<?php
// Copyright by: Manuel
// Support: www.ilch.de
// if(file_exists('install.php') || file_exists('install.sql')) die('Installationsdateien noch vorhanden! Bitte erst l&ouml;schen!');
ob_start();
define('main', true);
define('DEBUG', true);
define('SCRIPT_START_TIME', microtime(true));
define('PATH', dirname(__FILE__).'/');
define('AJAXCALL', isset($_GET['ajax']) and $_GET['ajax'] == 'true');
// Konfiguration zur Anzeige von Fehlern
// Auf http://www.php.net/manual/de/function.error-reporting.php sind die verfügbaren Modi aufgelistet
// Seit php-5.3 ist eine Angabe der TimeZone Pflicht
if (version_compare(phpversion(), '5.3') != - 1) {
    if (E_ALL > E_DEPRECATED) {
        @error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
    } else {
        @error_reporting(E_ALL ^ E_NOTICE);
    }
    date_default_timezone_set('Europe/Berlin');
} else {
    @error_reporting(E_ALL ^ E_NOTICE);
}
// header('Content-Type: text/html;charset=UTF-8');
@ini_set('display_errors', 'On');
// Session starten
session_name('sid');
session_start();
// Datenbankverbindung aufbauen und Funktionen und Klassen laden
require_once('include/includes/config.php');
require_once('include/includes/loader.php');
// Allgemeiner Konfig-Array
$allgAr = getAllgAr();

/* ENTWICKLUNGSVERSION SQL UPDATES */
require_once('update/update.php');
// Menu, Nutzerverwaltung und Seitenstatistik laden
$menu = new menu();
$m = $menu->get_complete();
user_identification($m);
// Sprachdateien oeffnen
load_global_lang();
load_modul_lang();
// Ajaxreload für Boxen
design::ajax_boxload();

site_statistic();
// Wartungsmodus
if ($allgAr['wartung'] == 1) {
    if (is_admin()) {
        @define('DEBUG', true);
        debug ('Wartungsmodus aktiv !');
    } else {
        die ($allgAr['wartungstext']);
    }
}
// Modul oeffnen
require_once('include/contents/' . $menu->get_url());
// Datenbank schließen
db_close();
debug_out();