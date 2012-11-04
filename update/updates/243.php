<?php
/**
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL)
 * @copyright (C) 2000-2012 ilch.de
 * @version $Id
*/

db_query("CREATE  TABLE IF NOT EXISTS `prefix_images` (
        `id` INT NOT NULL AUTO_INCREMENT ,
        `path` VARCHAR(255) NOT NULL ,
        `name` VARCHAR(45) NOT NULL ,
        `typ` VARCHAR(3) NOT NULL ,
        `width` INT NOT NULL ,
        `height` VARCHAR(45) NOT NULL ,
        `min_right` INT NOT NULL Default 0,
        PRIMARY KEY (`id`) )
    ENGINE = InnoDB;");
db_query("CREATE  TABLE IF NOT EXISTS `prefix_images_cache` (
        `id` INT NOT NULL ,
        `typ` VARCHAR(3) NOT NULL ,
        `width` INT NOT NULL ,
        `height` INT NOT NULL ,
        `last_edit` TIMESTAMP NOT NULL ,
        PRIMARY KEY (`id` ASC, `height` ASC, `width` ASC, `typ` ASC, `img_id` ASC))
    ENGINE = InnoDB;");

$rev='GD_1';
$update_messages[$rev][] = 'Datenbank für die Verwalung der Wrapperklasse angelegt';