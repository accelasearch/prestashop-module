DROP TABLE IF EXISTS `{{PREFIX}}as_fullsync_queue`;

DROP TABLE IF EXISTS `{{PREFIX}}as_notifications`;

CREATE TABLE `{{PREFIX}}as_fullsync_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `query` longtext CHARACTER SET utf8 DEFAULT NULL,
  `is_processing` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `offset_limit` varchar(255) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `start_cycle` int(11) NOT NULL,
  `end_cycle` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `{{PREFIX}}as_notifications` (
  `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `id_product` INT NOT NULL,
  `id_product_attribute` INT DEFAULT 0,
  `type` VARCHAR(255) NOT NULL,
  `id_shop` INT NOT NULL DEFAULT 0,
  `id_lang` INT NOT NULL DEFAULT 0,
  `name` VARCHAR(255) DEFAULT NULL,
  `value` TEXT DEFAULT NULL,
  `tblname` VARCHAR(255) NOT NULL,
  `op` char(1) NOT NULL,
  `timex` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8;