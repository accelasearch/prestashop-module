CREATE TABLE IF NOT EXISTS __DB_PREFIX__accelasearch_logs (
    `id` INT NOT NULL AUTO_INCREMENT,
    `message` TEXT NULL DEFAULT NULL,
    `gravity` TINYINT NOT NULL,
    `context` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;