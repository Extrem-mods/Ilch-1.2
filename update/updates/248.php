<?php
/**
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL)
 * @copyright (C) 2000-2012 ilch.de
 * @version $Id
*/
 
 db_query("CREATE TABLE IF NOT EXISTS `prefix_tpc_connectors` (
    `id` smallint(6) NOT NULL AUTO_INCREMENT,
    `name` varchar(64) NOT NULL,
    `class_name` varvhar(64) NOT NULL,
    `description` text NOT NULL DEFAULT '',
    `tested` int(2) NOT NULL DEFAULT 0,
    `active` BOOL NOT NULL DEFAULT 0,
    `version` varchar(8) NOT NULL DEFAULT 0,
    `events` int() NOT NULL DEFAULT 0
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='powered by ilch.de'");
db_query("INSERT INTO `prefix_config` (`schl`, `typ`, `kat`, `frage`, `wert`, `pos`, `hide`, `helptext`) VALUES ('tcp',  'r2',  'Allgemein', 'Soll der Third-Party-Connector verwendet werden?',  '0', '0', '0', NULL)");
$rev = '248';
$update_messages[$rev][] = 'TPC Eingebaut';
