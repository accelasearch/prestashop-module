<?php
ignore_user_abort(true);
if (!defined('_PS_VERSION_')) exit;
class AdminAccelaSearchActionsController extends ModuleAdminController
{

	public function get_constant($name)
	{
		return constant($name);
	}

	public function __construct()
	{
		parent::__construct();
		$this->bootstrap = true;
	}

	public function renderList()
	{
		$list = parent::renderList();
		Tools::redirect(Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminModules') . '&configure=accelasearch'));
	}

	private function getShopDataByIdAndLang($id_shop, $id_lang)
	{
		$id_shop = (int)$id_shop;
		$id_lang = (int)$id_lang;
		$selected_shops_query = <<<SQL
			SELECT s.*, s.name AS shop_name, l.*, ls.*, su.domain_ssl url, su.physical_uri, su.virtual_uri
			FROM {$this->get_constant('_DB_PREFIX_')}shop s
			JOIN {$this->get_constant('_DB_PREFIX_')}lang_shop ls ON ls.id_shop = s.id_shop
			JOIN {$this->get_constant('_DB_PREFIX_')}shop_url su ON su.id_shop = s.id_shop
			JOIN {$this->get_constant('_DB_PREFIX_')}lang l ON l.id_lang = ls.id_lang
			WHERE s.id_shop = {$id_shop} AND l.id_lang = {$id_lang}
SQL;
		$shop_data = Db::getInstance()->getRow($selected_shops_query);
		return $shop_data;
	}

	private function getLangLink($idLang = null, Context $context = null, $idShop = null)
	{
		static $psRewritingSettings = null;
		if ($psRewritingSettings === null) {
			$psRewritingSettings = (int) Configuration::get('PS_REWRITING_SETTINGS', null, null, $idShop);
		}
		if (!$context) {
			$context = Context::getContext();
		}
		if (!Language::isMultiLanguageActivated($idShop) || !$psRewritingSettings) {
			return '';
		}
		if (!$idLang) {
			$idLang = $context->language->id;
		}
		return Language::getIsoById($idLang) . '/';
	}

	private function getHashByIdShopAndLangAndIso($id_shop, $id_lang, $iso)
	{
		$link = new Link();
		return md5($link->getBaseLink($id_shop) . $this->getLangLink($id_lang, null, $id_shop) . $iso);
	}

	private function addShopToAccelaSearch($shop_data)
	{
		$id_shop = (int)$shop_data["id_shop"];
		$iso_code = $shop_data["iso_code"];
		$link = new Link();
		$url = $link->getBaseLink($id_shop) . $this->getLangLink($shop_data["id_lang"], null, $id_shop);
		$data = [
			"url" => $url,
			"description" => $shop_data["shop_name"],
			"langiso" => $iso_code,
			"siteid" => $id_shop,
			"storeid" => 1,
			"viewid" => 1,
			"hash" => md5($url . $iso_code),
			"cmsid" => AccelaSearch::AS_CONFIG["CMS_ID"],
			"disabled" => 0,
			"lastupdate" => date("Y-m-d H:i:s")
		];
		$as_shop_insert = AS_Collector::getInstance()->insert("storeviews", $data);
		if (!(bool)$as_shop_insert) throw new \Exception("An error occured during shop creation on AccelaSearch");
		return true;
	}

	private function shopExistOnAccelaSearch($hash)
	{
		return (bool)AS_Collector::getInstance()->getValue("SELECT COUNT(*) FROM storeviews WHERE hash = '$hash'");
	}

	public function ajaxProcessDisconnectApikey()
	{
		\AccelaSearch\Sync::softDeleteAll();
		$as_shops = \AccelaSearch::getAsShops();
		foreach ($as_shops as $as_shop) {
			[
				"id_shop" => $id_shop,
				"id_lang" => $id_lang,
				"as_shop_id" => $as_shop_id,
				"as_shop_real_id" => $as_shop_real_id
			] = $as_shop;
			\AccelaSearch\Sync::reindex($as_shop_real_id);
		}
		foreach ($as_shops as $as_shop) {
			[
				"id_shop" => $id_shop,
				"id_lang" => $id_lang,
				"as_shop_id" => $as_shop_id,
				"as_shop_real_id" => $as_shop_real_id
			] = $as_shop;
			$isIndexing = true;
			$max_attempts = 5;
			$nb_execution = 0;
			while ($isIndexing) {
				if ($nb_execution == $max_attempts) break;
				sleep(5);
				$isIndexCall = \AccelaSearch\Sync::isIndexing($as_shop_real_id);
				$isIndexing = $isIndexCall->isIndexing;
				$nb_execution++;
			}
		}
		\AccelaSearch\Sync::deleteAll();
		foreach (AccelaSearch::DEFAULT_CONFIGURATION as $key => $value) {
			Configuration::updateGlobalValue($key, $value);
		}
		$token = AccelaSearch::generateToken(40);
		Configuration::updateGlobalValue("ACCELASEARCH_CRON_TOKEN", $token);
		$this->ajaxDie(Tools::jsonEncode(
			[
				'success' => true
			]
		));
	}

	public function ajaxProcessShopInitializations()
	{
		if (!AccelaSearch::AS_CONFIG["DEBUG_MODE"]) return;
		echo AccelaSearch::shopInitializations();
	}

	public function ajaxProcessResyncUsersGroups()
	{
		$as_shops = AccelaSearch::getAsShops();
		$queries = [];
		foreach ($as_shops as $as_shop) {
			[
				"id_shop" => $id_shop,
				"id_lang" => $id_lang,
				"as_shop_id" => $as_shop_id,
				"as_shop_real_id" => $as_shop_real_id
			] = $as_shop;
			$customer_groups = Group::getGroups($id_lang, $id_shop);
			foreach ($customer_groups as $customer_group) {
				$id_group = $customer_group["id_group"];
				$externalidstr = $id_shop . "_" . $id_lang . "_" . $id_group;
				$group_on_as = (bool)AS_Collector::getInstance()->getValue("SELECT COUNT(*) FROM users_groups WHERE externalidstr = '$externalidstr'");
				if (!$group_on_as) {
					$name = $customer_group["name"];
					$queries[] = AccelaSearch\Query::getByName("shopInitializationsCustomerGroup_query", [
						"name" => pSQL($name),
						"externalidstr" => $externalidstr,
						"storeview_id" => $as_shop_id
					]);
				}
			}
		}
		$queries = implode("", $queries);
		AS_Collector::getInstance()->query($queries);
		$this->ajaxDie(Tools::jsonEncode(
			[
				'success' => true
			]
		));
	}

	public function ajaxProcessResyncAllPrices()
	{
		$as_shops = \AccelaSearch::getAsShops();
		foreach ($as_shops as $as_shop) {
			[
				"id_shop" => $id_shop
			] = $as_shop;
			\AccelaSearch\Sync::createRepriceRule($id_shop);
		}
	}

	public function ajaxProcessResyncAll()
	{
		\AccelaSearch\Sync::softDeleteAll();
		$as_shops = \AccelaSearch::getAsShops();
		foreach ($as_shops as $as_shop) {
			[
				"id_shop" => $id_shop,
				"id_lang" => $id_lang,
				"as_shop_id" => $as_shop_id,
				"as_shop_real_id" => $as_shop_real_id
			] = $as_shop;
			\AccelaSearch\Sync::reindex($as_shop_real_id);
		}
		foreach ($as_shops as $as_shop) {
			[
				"id_shop" => $id_shop,
				"id_lang" => $id_lang,
				"as_shop_id" => $as_shop_id,
				"as_shop_real_id" => $as_shop_real_id
			] = $as_shop;
			$isIndexing = true;
			$max_attempts = 5;
			$nb_execution = 0;
			while ($isIndexing || ($nb_execution < $max_attempts)) {
				sleep(5);
				$isIndexCall = \AccelaSearch\Sync::isIndexing($as_shop_real_id);
				$isIndexing = $isIndexCall->isIndexing;
				$nb_execution++;
			}
		}
		\AccelaSearch\Sync::deleteAll();
		AccelaSearch::shopInitializations();
		Configuration::updateGlobalValue("ACCELASEARCH_FULLSYNC_CREATION_PROGRESS", 0);
	}

	public function ajaxProcessSoftDeleteAndCleanupProducts()
	{
		if (!AccelaSearch::AS_CONFIG["DEBUG_MODE"]) return;
		\AccelaSearch\Sync::softDeleteAll();
		$as_shops = \AccelaSearch::getAsShops();
		foreach ($as_shops as $as_shop) {
			[
				"id_shop" => $id_shop,
				"id_lang" => $id_lang,
				"as_shop_id" => $as_shop_id,
				"as_shop_real_id" => $as_shop_real_id
			] = $as_shop;
			\AccelaSearch\Sync::reindex($as_shop_real_id);
		}
		sleep(30);
		\AccelaSearch\Sync::deleteAll();
	}

	public function ajaxProcessGetFaqs()
	{
		$faqs = [
			$this->l("General") =>
			[
				$this->l("What is AccelaSearch module?") => $this->l("Thanks to AccelaSearch module you will boost your search engine"),
				$this->l("Is this module compatible with multishop?") => $this->l("Sure, make sure to choice right shops you want to synchronize during onboarding module configuration"),
			],
			$this->l("Installation") =>
			[
				$this->l("I don't see my products on searchbar after module installation, what can I do?") => $this->l("If you don't see any products in searchbar and are passed more than 5 minutes, run the products checker under 'Product Synchronization' tab")
			],
			$this->l("Configuration") =>
			[
				$this->l("How I can customize Search Layer?") => $this->l("Go to console.accelasearch.io and click on 'Customize design'")
			]
		];
		$this->ajaxDie(Tools::jsonEncode(
			[
				'faqs' => $faqs,
			]
		));
	}

	public function ajaxProcessStartRemoteChecker()
	{
		$as_shops = AccelaSearch::getAsShops();
		foreach ($as_shops as $as_shop) {
			[
				"id_shop" => $id_shop,
				"id_lang" => $id_lang,
				"as_shop_id" => $as_shop_id,
				"as_shop_real_id" => $as_shop_real_id
			] = $as_shop;
			AccelaSearch::createQueryDataInstanceByIdShopAndLang($id_shop, $id_lang, $as_shop_id, $as_shop_real_id);
			$missings = AccelaSearch::getMissingProductsOnAs($id_shop, $id_lang);
			$missings = array_slice($missings, 0, 50);
			$processed = [];
			$errors = [];
			foreach ($missings as $missing) {
				[$id_shop, $id_lang, $id_product, $id_product_attribute] = explode("_", $missing);
				if (in_array($id_product, $processed)) continue;
				try {
					$product_query = \AccelaSearch\Query::getProductCreationQuery($id_product, $id_shop, $id_lang, $as_shop_id, $as_shop_real_id, AccelaSearch::WITHOUT_IGNORE);
					AS_Collector::getInstance()->query($product_query);
				} catch (\Exception $e) {
					$errors[$missing] = $e->getMessage();
				}
				if (!(bool)$id_product_attribute) $processed[] = $id_product;
			}
		}
		$this->ajaxDie(Tools::jsonEncode(
			[
				'success' => true,
				'missings' => $missings,
				'errors' => $errors
			]
		));
	}

	private function setQueueAsProcessed($id)
	{
		return Db::getInstance()->update(
			"as_fullsync_queue",
			[
				"is_processing" => 2,
				"processed_at" => date("Y-m-d H:i:s")
			]
		);
	}

	public function ajaxProcessDeleteQueue()
	{
		if (!AccelaSearch::AS_CONFIG["DEBUG_MODE"]) return;
		Db::getInstance()->query("DELETE FROM " . _DB_PREFIX_ . "as_fullsync_queue");
	}

	public function ajaxProcessGetAsProductInformations()
	{
		if (!AccelaSearch::AS_CONFIG["DEBUG_MODE"]) return;
		$id_product = $_POST["pid"] ?? NULL;
		if ($id_product > 0) {
			$as_shops = AccelaSearch::getAsShops();
			$products = [];
			foreach ($as_shops as $as_shop) {

				[
					"id_shop" => $id_shop,
					"id_lang" => $id_lang,
					"as_shop_id" => $as_shop_id,
					"as_shop_real_id" => $as_shop_real_id
				] = $as_shop;

				$externalidstr = $id_shop . "_" . $id_lang . "_" . $id_product . "_";
				$_products = AS_Collector::getInstance()->executeS("SELECT * FROM products WHERE externalidstr LIKE '$externalidstr%' AND deleted = 0");

				foreach ($_products as $_product) {

					[
						"id" => $as_pid,
						"sku" => $sku,
						"siteid" => $siteid,
						"externalidstr" => $externalidstr,
						"url" => $url,
						"typeid" => $typeid
					] = $_product;

					$typeid_key = $typeid . "_" . $as_pid;

					if (!array_key_exists($typeid, $products)) $products[$typeid_key] = [];

					$products[$typeid_key]["basics"] = $_product;

					$prices = AS_Collector::getInstance()->executeS("SELECT * FROM prices WHERE productid = $as_pid AND deleted = 0");
					$images = AS_Collector::getInstance()->executeS("SELECT * FROM products_images WHERE productid = $as_pid AND deleted = 0");
					$categories = AS_Collector::getInstance()->executeS("SELECT * FROM products_categories WHERE productid = $as_pid AND deleted = 0");
					$stocks = AS_Collector::getInstance()->executeS("SELECT * FROM stocks WHERE productid = $as_pid AND deleted = 0");
					$attr_str = AS_Collector::getInstance()->executeS("SELECT pas.*, pal.label FROM products_attr_str pas JOIN products_attr_label pal ON pal.id = pas.labelid WHERE pas.productid = $as_pid");
					$attr_text = AS_Collector::getInstance()->executeS("SELECT pat.*, pal.label FROM products_attr_text pat JOIN products_attr_label pal ON pal.id = pat.labelid WHERE pat.productid = $as_pid");

					$products[$typeid_key]["prices"] = $prices;
					$products[$typeid_key]["images"] = $images;
					$products[$typeid_key]["categories"] = $categories;
					$products[$typeid_key]["stocks"] = $stocks;

					$products[$typeid_key]["attrs"] = [];

					foreach ($attr_str as $attr_str_single) {
						$name = $attr_str_single["label"];
						$products[$typeid_key]["attrs"][$name] = $attr_str_single["value"];
					}
					foreach ($attr_text as $attr_text_single) {
						$name = $attr_text_single["label"];
						$products[$typeid_key]["attrs"][$name] = $attr_text_single["value"];
					}
				}
			}
			$this->ajaxDie(Tools::jsonEncode(
				[
					'products' => $products
				]
			));
		}
	}

	public function ajaxProcessSendQueue()
	{
		if (!AccelaSearch::AS_CONFIG["DEBUG_MODE"]) return;
		$queues = AccelaSearch::getQueues();
		foreach ($queues as $queue) {
			AS_Collector::getInstance()->query($queue["query"]);
			$this->setQueueAsProcessed($queue["id"]);
		}
	}

	public function ajaxProcessCronManager()
	{
		echo "Hello from cron manager";
	}

	public function ajaxProcessAutomaticQueue()
	{

		if (!AccelaSearch::AS_CONFIG["DEBUG_MODE"]) return;
		$as_shops = AccelaSearch::getAsShops();
		$queries = "";
		set_time_limit(0);
		fastcgi_finish_request();
		foreach ($as_shops as $as_shop) {

			$divider = AccelaSearch::getOffsetDivider($as_shop["id_shop"], $as_shop["id_lang"]);
			$queue_start = 1;
			$products_nb = AccelaSearch::estimateNbProducts($as_shop["id_shop"], $as_shop["id_lang"]);
			$executions_nb = ceil($products_nb / $divider);

			for ($start = $queue_start; $start <= $executions_nb; $start++) {
				$limit = $divider * ($start - 1) . "," . $divider;
				$query = AccelaSearch::generateProductsQueryStatic(
					$as_shop["id_shop"],
					$as_shop["id_lang"],
					$as_shop["as_shop_id"],
					$as_shop["as_shop_real_id"],
					$limit
				);
				AccelaSearch::createQueue($query, $limit, $start, $executions_nb, $as_shop["id_shop"], $as_shop["id_lang"]);
			}
		}
	}

	public function ajaxProcessGenerateProductsQuery()
	{
		if (!AccelaSearch::AS_CONFIG["DEBUG_MODE"]) return;
		$as_shops = AccelaSearch::getAsShops();
		$queries = "";
		$limit = "25,30";
		foreach ($as_shops as $as_shop) {
			$queries .= AccelaSearch::generateProductsQueryStatic(
				$as_shop["id_shop"],
				$as_shop["id_lang"],
				$as_shop["as_shop_id"],
				$as_shop["as_shop_real_id"],
				$limit
			);
		}

		$queries = \AccelaSearch\Query::getProductCreationQuery(4356, 1, 1, $as_shops["1_1"]["as_shop_id"], $as_shops["1_1"]["as_shop_real_id"]);

		// AccelaSearch::createQueue($queries, $limit);

		// file_put_contents("/home/bimbimatti/public_html/modules/accelasearch/controllers/admin/query_payload.sql", $queries);

		echo $queries;
		// AS_Collector::getInstance()->query($queries);

		// Db::getInstance()->query($ps_queries);
		// Db::getInstance()->update(
		// 	"as_fullsync_queue",
		// 	[
		// 		"query" => $queries
		// 	],
		// 	"id = $queue_id"
		// );


	}

	public function ajaxProcessCleanupProducts()
	{
		if (!AccelaSearch::AS_CONFIG["DEBUG_MODE"]) return;
		AccelaSearch\Sync::DbCleanup();
	}

	public function ajaxProcessAddShops()
	{
		$success = false;
		$errors = [];
		$shops = $_POST["shops"] ?? [];
		$shop_synced = [];
		if (count($shops) == 0) {
			$this->ajaxDie(Tools::jsonEncode(
				[
					'success' => $success
				]
			));
		}
		foreach ($shops as $shop) {
			$shop_data = $this->getShopDataByIdAndLang($shop["id_shop"], $shop["id_lang"]);
			$iso = $shop_data["iso_code"];
			$hash = $this->getHashByIdShopAndLangAndIso($shop["id_shop"], $shop["id_lang"], $iso);
			$as_shop_exist = $this->shopExistOnAccelaSearch($hash);
			if (!$as_shop_exist) {
				try {
					$this->addShopToAccelaSearch($shop_data);
				} catch (\Exception $e) {
					$errors[] = $e->getMessage();
				}
			}
			$shop["iso_code"] = $iso;
			$shop_synced[$shop["id_shop"] . "_" . $shop["id_lang"]] = $shop;
		}
		$success = !(bool)count($errors);
		if ($success) {
			try {
				AccelaSearch::notifyShops();
				$as_shops = AS_Collector::getInstance()->executeS("SELECT * FROM storeviews");
				foreach ($as_shops as $as_shop) {
					[
						"id" => $as_shop_id,
						"siteid" => $as_shop_siteid,
						"langiso" => $as_shop_langiso,
						"hash" => $hash
					] = $as_shop;
					foreach ($shop_synced as $k => $shop_synced_entity) {
						if ($this->getHashByIdShopAndLangAndIso($shop_synced_entity["id_shop"],  $shop_synced_entity["id_lang"],  $shop_synced_entity["iso_code"]) == $hash) {
							$shop_synced[$k]["as_shop_id"] = $as_shop_id;
							$as_shop_real_id = AccelaSearch::convertShopIdFromCollectorVersionToReal($as_shop_id);
							$shop_synced[$k]["as_shop_real_id"] = $as_shop_real_id;
						}
					}
				}
				Configuration::updateGlobalValue("ACCELASEARCH_SHOPS_SYNCED", json_encode($shop_synced));
				AccelaSearch::shopInitializations();
				Configuration::updateGlobalValue("ACCELASEARCH_FULLSYNC_CREATION_PROGRESS", 0);
				$this->ajaxDie(Tools::jsonEncode(
					[
						'success' => $success,
						'errors' => $errors
					]
				));
			} catch (\Exception $e) {
				$success = false;
				$errors[] = $e->getMessage();
			}
		}
		$this->ajaxDie(Tools::jsonEncode(
			[
				'success' => $success,
				'errors' => $errors
			]
		));
	}

	public function ajaxProcessSubmitApikey()
	{
		$success = false;
		$apikey = $_POST["apikey"] ?? false;
		if (!$apikey) {
			$this->ajaxDie(Tools::jsonEncode(
				[
					'success' => $success
				]
			));
		}
		$credentials = AccelaSearch::asApi(
			"collector",
			"GET",
			[],
			false,
			[
				"X-Accelasearch-Apikey: " . $apikey
			]
		);
		if ($credentials === false) {
			$this->ajaxDie(Tools::jsonEncode(
				[
					'success' => false,
					"data" => []
				]
			));
		}
		$credentials = json_decode($credentials);
		$success = isset($credentials->status) ? false : true;
		if ($success) {
			Configuration::updateGlobalValue("ACCELASEARCH_APIKEY", $apikey);
			Configuration::updateGlobalValue("ACCELASEARCH_COLLECTOR", json_encode($credentials));
		}

		$this->ajaxDie(Tools::jsonEncode(
			[
				'success' => $success,
				"data" => $credentials
			]
		));
	}
}
