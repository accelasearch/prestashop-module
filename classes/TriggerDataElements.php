<?php

/**
 * Contiene le definizioni ed i dati dei trigger da creare che verranno
 * passati alla classe Trigger ed ai suoi metodi di generazione
 * // NOTE: I trigger devono essere generati in questo modo per poter essere flessibili e soggetti a condizioni imprescindibili
 * come i controlli sulla versione e la relativa esistenza dei campi
 */

namespace AccelaSearch;

class TriggerDataElements
{
  public $elements;
  public function __construct()
  {

    // update trigger data and definition

    $this->elements = [
      "u_product" => [
        "when" => "AFTER",
        "type" => "UPDATE",
        "table" => "product",
        "fields" => [
          [
            "name" => "id_manufacturer",
            "select_fields" => [
              "p.`id_product`",
              "'product'",
              "0",
              "0",
              "'manufacturer'",
              "NEW.`id_manufacturer`",
              "'{{PREFIX}}product'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product",
              "from_as" => "p"
            ],
            "joins" => [],
            "wheres" => [
              " AND p.`id_product`=NEW.`id_product`"
            ],
          ],
          [
            "name" => "ean13",
            "select_fields" => [
              "p.`id_product`",
              "'product'",
              "0",
              "0",
              "'ean13'",
              "NEW.`ean13`",
              "'{{PREFIX}}product'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product",
              "from_as" => "p"
            ],
            "joins" => [],
            "wheres" => [
              " AND p.`id_product`=NEW.`id_product`"
            ],
          ],
          [
            "name" => "isbn",
            "select_fields" => [
              "p.`id_product`",
              "'product'",
              "0",
              "0",
              "'isbn'",
              "NEW.`isbn`",
              "'{{PREFIX}}product'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product",
              "from_as" => "p"
            ],
            "joins" => [],
            "wheres" => [
              " AND p.`id_product`=NEW.`id_product`"
            ],
          ],
          [
            "name" => "upc",
            "select_fields" => [
              "p.`id_product`",
              "'product'",
              "0",
              "0",
              "'upc'",
              "NEW.`upc`",
              "'{{PREFIX}}product'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product",
              "from_as" => "p"
            ],
            "joins" => [],
            "wheres" => [
              " AND p.`id_product`=NEW.`id_product`"
            ],
          ],
          [
            "name" => "reference",
            "select_fields" => [
              "p.`id_product`",
              "'product'",
              "0",
              "0",
              "'reference'",
              "NEW.`reference`",
              "'{{PREFIX}}product'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product",
              "from_as" => "p"
            ],
            "joins" => [],
            "wheres" => [
              " AND p.`id_product`=NEW.`id_product`"
            ],
          ]
        ],
      ],
      "u_product_lang" => [
        "when" => "AFTER",
        "type" => "UPDATE",
        "table" => "product_lang",
        "fields" => [
          [
            "name" => "name",
            "select_fields" => [
              "ps.`id_product`",
              "'product'",
              "ps.`id_shop`",
              "pl.`id_lang`",
              "'name'",
              "NEW.`name`",
              "'{{PREFIX}}product_lang'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product_shop",
              "from_as" => "ps"
            ],
            "joins" => [
              " JOIN `{{PREFIX}}product_lang` AS pl ON ps.`id_product`=pl.`id_product`"
            ],
            "wheres" => [
              " AND pl.`id_product`=NEW.`id_product`",
              " AND pl.`id_lang`=NEW.`id_lang`",
              " AND pl.`id_shop`=NEW.`id_shop`",
              " AND ps.`id_shop`=NEW.`id_shop`"
            ],
          ],
          [
            "name" => "description",
            "select_fields" => [
              "ps.`id_product`",
              "'product'",
              "ps.`id_shop`",
              "pl.`id_lang`",
              "'description'",
              "NEW.`description`",
              "'{{PREFIX}}product_lang'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product_shop",
              "from_as" => "ps"
            ],
            "joins" => [
              " JOIN `{{PREFIX}}product_lang` AS pl ON ps.`id_product`=pl.`id_product`"
            ],
            "wheres" => [
              " AND pl.`id_product`=NEW.`id_product`",
              " AND pl.`id_lang`=NEW.`id_lang`",
              " AND pl.`id_shop`=NEW.`id_shop`",
              " AND ps.`id_shop`=NEW.`id_shop`"
            ],
          ],
          [
            "name" => "description_short",
            "select_fields" => [
              "ps.`id_product`",
              "'product'",
              "ps.`id_shop`",
              "pl.`id_lang`",
              "'description_short'",
              "NEW.`description_short`",
              "'{{PREFIX}}product_lang'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product_shop",
              "from_as" => "ps"
            ],
            "joins" => [
              " JOIN `{{PREFIX}}product_lang` AS pl ON ps.`id_product`=pl.`id_product`"
            ],
            "wheres" => [
              " AND pl.`id_product`=NEW.`id_product`",
              " AND pl.`id_lang`=NEW.`id_lang`",
              " AND pl.`id_shop`=NEW.`id_shop`",
              " AND ps.`id_shop`=NEW.`id_shop`"
            ],
          ],
          [
            "name" => "link_rewrite",
            "select_fields" => [
              "ps.`id_product`",
              "'product'",
              "ps.`id_shop`",
              "pl.`id_lang`",
              "'link_rewrite'",
              "NEW.`link_rewrite`",
              "'{{PREFIX}}product_lang'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product_shop",
              "from_as" => "ps"
            ],
            "joins" => [
              " JOIN `{{PREFIX}}product_lang` AS pl ON ps.`id_product`=pl.`id_product`"
            ],
            "wheres" => [
              " AND pl.`id_product`=NEW.`id_product`",
              " AND pl.`id_lang`=NEW.`id_lang`",
              " AND pl.`id_shop`=NEW.`id_shop`",
              " AND ps.`id_shop`=NEW.`id_shop`"
            ],
          ]
        ],
      ],
      "u_product_shop" => [
        "when" => "AFTER",
        "type" => "UPDATE",
        "table" => "product_shop",
        "fields" => [
          [
            "name" => "price",
            "select_fields" => [
              "ps.`id_product`",
              "'product'",
              "ps.`id_shop`",
              "0",
              "'price'",
              "NEW.`price`",
              "'{{PREFIX}}product_shop'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product_shop",
              "from_as" => "ps"
            ],
            "joins" => [],
            "wheres" => [
              " AND ps.`id_product`=NEW.`id_product`",
              " AND ps.`id_shop`=NEW.`id_shop`"
            ],
          ],
          [
            "name" => "active",
            "select_fields" => [
              "ps.`id_product`",
              "'product'",
              "ps.`id_shop`",
              "0",
              "'active'",
              "NEW.`active`",
              "'{{PREFIX}}product_shop'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product_shop",
              "from_as" => "ps"
            ],
            "joins" => [],
            "wheres" => [
              " AND ps.`id_product`=NEW.`id_product`",
              " AND ps.`id_shop`=NEW.`id_shop`"
            ],
          ]
        ],
      ],
      "u_product_attribute_shop" => [
        "when" => "AFTER",
        "type" => "UPDATE",
        "table" => "product_attribute_shop",
        "fields" => [
          [
            "name" => "price",
            "select_fields" => [
              "pas.`id_product`",
              "pas.`id_product_attribute`",
              "'product'",
              "pas.`id_shop`",
              "0",
              "'price'",
              "NEW.`price`",
              "'{{PREFIX}}product_attribute_shop'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product_attribute_shop",
              "from_as" => "pas"
            ],
            "joins" => [],
            "wheres" => [
              " AND pas.`id_product_attribute`=NEW.`id_product_attribute`",
              " AND pas.`id_shop`=NEW.`id_shop`"
            ],
          ]
        ],
      ],
      "u_product_attribute" => [
        "when" => "AFTER",
        "type" => "UPDATE",
        "table" => "product_attribute",
        "fields" => [
          [
            "name" => "reference",
            "select_fields" => [
              "pa.`id_product`",
              "pa.`id_product_attribute`",
              "'product'",
              "0",
              "0",
              "'reference'",
              "NEW.`reference`",
              "'{{PREFIX}}product_attribute'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product_attribute",
              "from_as" => "pa"
            ],
            "joins" => [],
            "wheres" => [
              " AND pa.`id_product_attribute`=NEW.`id_product_attribute`"
            ],
          ],
          [
            "name" => "ean13",
            "select_fields" => [
              "pa.`id_product`",
              "pa.`id_product_attribute`",
              "'product'",
              "0",
              "0",
              "'ean13'",
              "NEW.`ean13`",
              "'{{PREFIX}}product_attribute'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product_attribute",
              "from_as" => "pa"
            ],
            "joins" => [],
            "wheres" => [
              " AND pa.`id_product_attribute`=NEW.`id_product_attribute`"
            ],
          ],
          [
            "name" => "isbn",
            "select_fields" => [
              "pa.`id_product`",
              "pa.`id_product_attribute`",
              "'product'",
              "0",
              "0",
              "'isbn'",
              "NEW.`isbn`",
              "'{{PREFIX}}product_attribute'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product_attribute",
              "from_as" => "pa"
            ],
            "joins" => [],
            "wheres" => [
              " AND pa.`id_product_attribute`=NEW.`id_product_attribute`"
            ],
          ],
          [
            "name" => "upc",
            "select_fields" => [
              "pa.`id_product`",
              "pa.`id_product_attribute`",
              "'product'",
              "0",
              "0",
              "'upc'",
              "NEW.`upc`",
              "'{{PREFIX}}product_attribute'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product_attribute",
              "from_as" => "pa"
            ],
            "joins" => [],
            "wheres" => [
              " AND pa.`id_product_attribute`=NEW.`id_product_attribute`"
            ],
          ]
        ],
      ],
      "u_image_shop" => [
        "when" => "AFTER",
        "type" => "UPDATE",
        "table" => "image_shop",
        "fields" => [
          [
            "name" => "id_image",
            "select_fields" => [
              "pis.`id_product`",
              "'image'",
              "pis.`id_shop`",
              "0",
              "'id_image'",
              "NEW.`id_image`",
              "'{{PREFIX}}image_shop'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "image_shop",
              "from_as" => "pis"
            ],
            "joins" => [],
            "wheres" => [
              " AND pis.`id_product`=NEW.`id_product`",
              " AND pis.`id_shop`=NEW.`id_shop`"
            ],
          ]
        ],
      ],
      "u_product_attribute_image" => [
        "when" => "AFTER",
        "type" => "UPDATE",
        "table" => "product_attribute_image",
        "fields" => [
          [
            "name" => "id_image",
            "select_fields" => [
              "pa.`id_product`",
              "pai.`id_product_attribute`",
              "'image'",
              "0",
              "0",
              "'id_image'",
              "NEW.`id_image`",
              "'{{PREFIX}}product_attribute_image'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "product_attribute_image",
              "from_as" => "pai"
            ],
            "joins" => [
              " JOIN {{PREFIX}}product_attribute AS pa ON pa.`id_product_attribute` = NEW.`id_product_attribute`"
            ],
            "wheres" => [
              " AND pai.`id_product_attribute`=NEW.`id_product_attribute`"
            ],
          ]
        ],
      ],
      "u_stock_available" => [
        "when" => "AFTER",
        "type" => "UPDATE",
        "table" => "stock_available",
        "fields" => [
          [
            "name" => "quantity",
            "select_fields" => [
              "sa.`id_product`",
              "sa.`id_product_attribute`",
              "'stock'",
              "sa.`id_shop`",
              "0",
              "'quantity'",
              "NEW.`quantity`",
              "'{{PREFIX}}stock_available'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "stock_available",
              "from_as" => "sa"
            ],
            "joins" => [],
            "wheres" => [
              " AND sa.`id_product`=NEW.`id_product`",
              " AND sa.`id_product_attribute`=NEW.`id_product_attribute`",
              " AND sa.`id_shop`=NEW.`id_shop`"
            ],
          ]
        ],
      ],
      "u_category_lang" => [
        "when" => "AFTER",
        "type" => "UPDATE",
        "table" => "category_lang",
        "fields" => [
          [
            "name" => "name",
            "select_fields" => [
              "cl.`id_category`",
              "'category'",
              "cl.`id_shop`",
              "cl.`id_lang`",
              "'name'",
              "NEW.`name`",
              "'{{PREFIX}}category_lang'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "category_lang",
              "from_as" => "cl"
            ],
            "joins" => [],
            "wheres" => [
              " AND cl.`id_category`=NEW.`id_category`",
              " AND cl.`id_shop`=NEW.`id_shop`",
              " AND cl.`id_lang`=NEW.`id_lang`",
            ],
          ],
          [
            "name" => "link_rewrite",
            "select_fields" => [
              "cl.`id_category`",
              "'category'",
              "cl.`id_shop`",
              "cl.`id_lang`",
              "'link_rewrite'",
              "NEW.`link_rewrite`",
              "'{{PREFIX}}category_lang'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "category_lang",
              "from_as" => "cl"
            ],
            "joins" => [],
            "wheres" => [
              " AND cl.`id_category`=NEW.`id_category`",
              " AND cl.`id_shop`=NEW.`id_shop`",
              " AND cl.`id_lang`=NEW.`id_lang`",
            ],
          ]
        ],
      ],
      "u_category" => [ // cambio genitore categoria
        "when" => "AFTER",
        "type" => "UPDATE",
        "table" => "category",
        "fields" => [
          [
            "name" => "id_parent",
            "select_fields" => [
              "c.`id_category`",
              "'category'",
              "0",
              "0",
              "'id_parent'",
              "NEW.`id_parent`",
              "'{{PREFIX}}category'",
              "'u'"
            ],
            "from_clause" => [
              "from_name" => "category",
              "from_as" => "c"
            ],
            "joins" => [],
            "wheres" => [
              " AND c.`id_category`=NEW.`id_category`"
            ],
          ]
        ],
      ]
    ];

    // add mpn field if ps_version match requirement

    if(!version_compare(_PS_VERSION_, "1.7.7", "<")){
      $this->elements["u_product"]["fields"][] = [
        "name" => "mpn",
        "select_fields" => [
          "p.`id_product`",
          "'product'",
          "0",
          "0",
          "'mpn'",
          "NEW.`mpn`",
          "'{{PREFIX}}product'",
          "'u'"
        ],
        "from_clause" => [
          "from_name" => "product",
          "from_as" => "p"
        ],
        "joins" => [],
        "wheres" => [
          " AND p.`id_product`=NEW.`id_product`"
        ],
      ];
      $this->elements["u_product_attribute"]["fields"][] = [
        "name" => "mpn",
        "select_fields" => [
          "p.`id_product`",
          "'product'",
          "0",
          "0",
          "'mpn'",
          "NEW.`mpn`",
          "'{{PREFIX}}product'",
          "'u'"
        ],
        "from_clause" => [
          "from_name" => "product",
          "from_as" => "p"
        ],
        "joins" => [],
        "wheres" => [
          " AND p.`id_product`=NEW.`id_product`"
        ],
      ];
    }

    $this->elements["u_specific_price"] = [
      "when" => "AFTER",
      "type" => "UPDATE",
      "table" => "specific_price",
      "fields" => [
        [
          "name" => "reduction",
          "select_fields" => [
            "sp.`id_product`",
            "sp.`id_product_attribute`",
            "'price'",
            "sp.`id_shop`",
            "0",
            "'reduction'",
            "NEW.`reduction`",
            "'{{PREFIX}}specific_price'",
            "'u'"
          ],
          "from_clause" => [
            "from_name" => "specific_price",
            "from_as" => "sp"
          ],
          "joins" => [],
          "wheres" => [
            " AND sp.`id_product`=NEW.`id_product`",
            " AND sp.`id_product_attribute`=NEW.`id_product_attribute`",
            " AND sp.`id_shop`=NEW.`id_shop`",
          ],
        ]
      ],
    ];

    // insert trigger data and definition

    // nuovo prodotto inserito

    $this->elements["i_product_shop"] = [
      "when" => "AFTER",
      "type" => "INSERT",
      "table" => "product_shop",
      "fields" => [
        [
          "name" => "id_product",
          "select_fields" => [
            "ps.`id_product`",
            "'product'",
            "ps.`id_shop`",
            "0",
            "'id_product'",
            "NEW.`id_product`",
            "'{{PREFIX}}product_shop'",
            "'i'"
          ],
          "from_clause" => [
            "from_name" => "product_shop",
            "from_as" => "ps"
          ],
          "joins" => [],
          "wheres" => [
            " AND ps.`id_product`=NEW.`id_product`",
            " AND ps.`id_shop`=NEW.`id_shop`",
          ],
        ]
      ],
    ];

    // nuova variante inserita

    $this->elements["i_product_attribute_shop"] = [
      "when" => "AFTER",
      "type" => "INSERT",
      "table" => "product_attribute_shop",
      "fields" => [
        [
          "name" => "id_product",
          "select_fields" => [
            "pas.`id_product`",
            "pas.`id_product_attribute`",
            "'variant'",
            "pas.`id_shop`",
            "0",
            "'id_product_attribute'",
            "NEW.`id_product_attribute`",
            "'{{PREFIX}}product_attribute_shop'",
            "'i'"
          ],
          "from_clause" => [
            "from_name" => "product_attribute_shop",
            "from_as" => "pas"
          ],
          "joins" => [],
          "wheres" => [
            " AND pas.`id_product_attribute`=NEW.`id_product_attribute`",
            " AND pas.`id_shop`=NEW.`id_shop`",
          ],
        ]
      ],
    ];

    // nuova categoria inserita

    $this->elements["i_category_shop"] = [
      "when" => "AFTER",
      "type" => "INSERT",
      "table" => "category_shop",
      "fields" => [
        [
          "name" => "id_category",
          "select_fields" => [
            "NEW.`id_category`",
            "'category'",
            "cs.`id_shop`",
            "0",
            "'id_category'",
            "NEW.`id_category`",
            "'{{PREFIX}}category_shop'",
            "'i'"
          ],
          "from_clause" => [
            "from_name" => "category_shop",
            "from_as" => "cs"
          ],
          "joins" => [],
          "wheres" => [
            " AND cs.`id_category`=NEW.`id_category`",
            " AND cs.`id_shop`=NEW.`id_shop`",
          ],
        ]
      ],
    ];

    // nuova immagine inserita

    $this->elements["i_image_shop"] = [
      "when" => "AFTER",
      "type" => "INSERT",
      "table" => "image_shop",
      "fields" => [
        [
          "name" => "id_image",
          "select_fields" => [
            "pis.`id_product`",
            "'image'",
            "pis.`id_shop`",
            "0",
            "'id_image'",
            "NEW.`id_image`",
            "'{{PREFIX}}image_shop'",
            "'i'"
          ],
          "from_clause" => [
            "from_name" => "image_shop",
            "from_as" => "pis"
          ],
          "joins" => [],
          "wheres" => [
            " AND pis.`id_product`=NEW.`id_product`",
            " AND pis.`id_shop`=NEW.`id_shop`"
          ],
        ]
      ],
    ];

    // nuovo prezzo speciale

    $this->elements["i_specific_price"] = [
      "when" => "AFTER",
      "type" => "INSERT",
      "table" => "specific_price",
      "fields" => [
        [
          "name" => "id_product",
          "select_fields" => [
            "sp.`id_product`",
            "sp.`id_product_attribute`",
            "'price'",
            "sp.`id_shop`",
            "0",
            "'id_product'",
            "NEW.`id_product`",
            "'{{PREFIX}}specific_price'",
            "'i'"
          ],
          "from_clause" => [
            "from_name" => "specific_price",
            "from_as" => "sp"
          ],
          "joins" => [],
          "wheres" => [
            " AND sp.`id_product`=NEW.`id_product`",
            " AND sp.`id_product_attribute`=NEW.`id_product_attribute`",
            " AND sp.`id_shop`=NEW.`id_shop`",
          ],
        ]
      ],
    ];

    // nuova associazione immagine variante

    $this->elements["i_product_attribute_image"] = [
      "when" => "AFTER",
      "type" => "INSERT",
      "table" => "product_attribute_image",
      "fields" => [
        [
          "name" => "id_image",
          "select_fields" => [
            "pa.`id_product`",
            "pai.`id_product_attribute`",
            "'attribute_image'",
            "0",
            "0",
            "'id_image'",
            "NEW.`id_image`",
            "'{{PREFIX}}product_attribute_image'",
            "'i'"
          ],
          "from_clause" => [
            "from_name" => "product_attribute_image",
            "from_as" => "pai"
          ],
          "joins" => [
            " JOIN {{PREFIX}}product_attribute AS pa ON pa.`id_product_attribute` = NEW.`id_product_attribute`"
          ],
          "wheres" => [
            " AND pai.`id_product_attribute`=NEW.`id_product_attribute`"
          ],
        ]
      ],
    ];

    // nuova associazione categoria

    $this->elements["i_category_product"] = [
      "when" => "AFTER",
      "type" => "INSERT",
      "table" => "category_product",
      "fields" => [
        [
          "name" => "id_category",
          "select_fields" => [
            "cp.`id_product`",
            "'category_product'",
            "0",
            "0",
            "'id_category'",
            "NEW.`id_category`",
            "'{{PREFIX}}category_product'",
            "'i'"
          ],
          "from_clause" => [
            "from_name" => "category_product",
            "from_as" => "cp"
          ],
          "joins" => [],
          "wheres" => [
            " AND cp.`id_category`=NEW.`id_category`",
            " AND cp.`id_product`=NEW.`id_product`",
          ],
        ]
      ],
    ];

    // delete trigger data and definition

    $this->elements["d_category_product"] = [
      "when" => "AFTER",
      "type" => "DELETE",
      "table" => "category_product",
      "fields" => [
        [
          "name" => "id_category",
          "select_fields" => [
            "OLD.`id_product`",
            "'category_product'",
            "0",
            "0",
            "'id_category'",
            "OLD.`id_category`",
            "'{{PREFIX}}category_product'",
            "'d'"
          ],
          "from_clause" => [
            "from_name" => "",
            "from_as" => ""
          ],
          "joins" => [],
          "wheres" => [],
        ]
      ],
    ];

    $this->elements["d_product_shop"] = [
      "when" => "AFTER",
      "type" => "DELETE",
      "table" => "product_shop",
      "fields" => [
        [
          "name" => "id_product",
          "select_fields" => [
            "OLD.`id_product`",
            "'product'",
            "OLD.`id_shop`",
            "0",
            "'id_product'",
            "OLD.`id_product`",
            "'{{PREFIX}}product_shop'",
            "'d'"
          ],
          "from_clause" => [
            "from_name" => "",
            "from_as" => ""
          ],
          "joins" => [],
          "wheres" => [],
        ]
      ],
    ];

    $this->elements["d_product_attribute_shop"] = [
      "when" => "AFTER",
      "type" => "DELETE",
      "table" => "product_attribute_shop",
      "fields" => [
        [
          "name" => "id_product",
          "select_fields" => [
            "OLD.`id_product`",
            "OLD.`id_product_attribute`",
            "'variant'",
            "OLD.`id_shop`",
            "0",
            "'id_product_attribute'",
            "OLD.`id_product_attribute`",
            "'{{PREFIX}}product_attribute_shop'",
            "'d'"
          ],
          "from_clause" => [
            "from_name" => "",
            "from_as" => ""
          ],
          "joins" => [],
          "wheres" => [],
        ]
      ],
    ];

    $this->elements["d_category_shop"] = [
      "when" => "AFTER",
      "type" => "DELETE",
      "table" => "category_shop",
      "fields" => [
        [
          "name" => "id_category",
          "select_fields" => [
            "OLD.`id_category`",
            "'category'",
            "OLD.`id_shop`",
            "0",
            "'id_category'",
            "OLD.`id_category`",
            "'{{PREFIX}}category_shop'",
            "'d'"
          ],
          "from_clause" => [
            "from_name" => "",
            "from_as" => ""
          ],
          "joins" => [],
          "wheres" => [],
        ]
      ],
    ];

    $this->elements["d_image_shop"] = [
      "when" => "AFTER",
      "type" => "DELETE",
      "table" => "image_shop",
      "fields" => [
        [
          "name" => "id_image",
          "select_fields" => [
            "OLD.`id_product`",
            "'image'",
            "OLD.`id_shop`",
            "0",
            "'id_image'",
            "OLD.`id_image`",
            "'{{PREFIX}}image_shop'",
            "'d'"
          ],
          "from_clause" => [
            "from_name" => "",
            "from_as" => ""
          ],
          "joins" => [],
          "wheres" => [],
        ]
      ],
    ];

    $this->elements["d_specific_price"] = [
      "when" => "AFTER",
      "type" => "DELETE",
      "table" => "specific_price",
      "fields" => [
        [
          "name" => "id_product",
          "select_fields" => [
            "OLD.`id_product`",
            "OLD.`id_product_attribute`",
            "'price'",
            "OLD.`id_shop`",
            "0",
            "'id_specific_price'",
            "OLD.`id_specific_price`",
            "'{{PREFIX}}specific_price'",
            "'d'"
          ],
          "from_clause" => [
            "from_name" => "",
            "from_as" => ""
          ],
          "joins" => [],
          "wheres" => [],
        ]
      ],
    ];

    $this->elements["d_product_attribute_image"] = [
      "when" => "AFTER",
      "type" => "DELETE",
      "table" => "product_attribute_image",
      "fields" => [
        [
          "name" => "id_image",
          "select_fields" => [
            "pa.`id_product`",
            "OLD.`id_product_attribute`",
            "'attribute_image'",
            "0",
            "0",
            "'id_image'",
            "OLD.`id_image`",
            "'{{PREFIX}}product_attribute_image'",
            "'d'"
          ],
          "from_clause" => [
            "from_name" => "product_attribute_image",
            "from_as" => "pai"
          ],
          "joins" => [
            " JOIN {{PREFIX}}product_attribute AS pa ON pa.`id_product_attribute` = OLD.`id_product_attribute`"
          ],
          "wheres" => [],
        ]
      ],
    ];

  }
}

 ?>
