<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

# START COMPOSER #
$autoload = dirname(__FILE__) . "/vendor/autoload.php";
if (file_exists($autoload))
    require_once $autoload;
# END COMPOSER #

use \Accelasearch\Accelasearch\Install\Installer;
use \Accelasearch\Accelasearch\Sql\Manager;

class Accelasearch extends Module
{

    public function __construct()
    {
        $this->name = 'accelasearch';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'AccelaSearch';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Accelasearch');
        $this->description = $this->l('Boost your search engine with AI');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }

    public function install()
    {
        if (!parent::install())
            return false;

        $installer = new Installer($this, new Manager());
        return $installer->install();
    }

    public function uninstall()
    {
        if (!parent::uninstall())
            return false;

        $installer = new Installer($this, new Manager());
        return $installer->uninstall();
    }

    public function hookActionAdminControllerSetMedia()
    {
        $actions_controller_link = $this->context->link->getAdminLink('AccelasearchAdmin');
        Media::addJsDef([
            'accelasearch_controller_url' => $actions_controller_link,
            'accelasearch_controller_token' => Tools::getAdminTokenLite('AccelasearchAdmin'),
            'accelasearch_public_url' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/react/public/',

        ]);
    }

    public function getContent()
    {

        $this->context->smarty->assign('module_dir', $this->_path);

        if (Tools::getValue('configure') == $this->name) {
            $manifest = file_get_contents(dirname(__FILE__) . "/react/dist/manifest.json");
            if (!$manifest)
                return false;

            $files_data = json_decode($manifest, true);
            if ($files_data === null)
                return false;

            $base_url = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__;

            $js_files = [
                $base_url . "modules/$this->name/react/dist/" . $files_data["index.html"]["file"]
            ];
            $css_files = [
                $base_url . "modules/$this->name/react/dist/" . $files_data["index.html"]["css"][0]
            ];

            $this->context->smarty->assign('js_file_paths', $js_files);
            $this->context->smarty->assign('css_file_paths', $css_files);
        }


        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        return $output;
    }
}
