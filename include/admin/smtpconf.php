<?php
/**
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL)
 * @copyright (C) 2000-2010 ilch.de
 * @version $Id$
 */
defined('main') or die('no direct access');
defined('admin') or die('only admin access');

$design = new design('Ilch Admin-Control-Panel :: SMTP-Konfiguration', '', 2);
$design->header();

if (!is_admin()) {
    echo 'Dieser Bereich ist nicht fuer dich...';
    $design->footer();
    exit();
}

$authMethods = array(
    'no' => 'keine',
    'auth' => 'einfache Authentifizierung',
    'tls' => 'TLS',
    'ssl' => 'SSL'
    );

$keys = array(
    'smtp_host',
    'smtp_port',
    'smtp_auth',
    'smtp_pop3beforesmtp',
    'smtp_pop3host',
    'smtp_pop3port',
    'smtp_login',
    'smtp_email',
    'smtp_login',
    'smtp_pass',
    'smtp_changesubject'
    );
// Daten aus Datenbank lesen
$qry = db_query('SELECT `t1` FROM `prefix_allg` WHERE `k` = "smtpconf"');
if (db_num_rows($qry) == 0 or ($smtpser = db_result($qry)) == '') {
    $smtp = array_fill_keys($keys, '');
    $smtp[ 'smtp_changesubject' ] = 1;
} else {
    $smtp = unserialize($smtpser);
}
// Formular verabeiten
if (isset($_POST[ 'subform' ]) and chk_antispam('adminuser_action', true)) {
    if (!empty($_POST[ 'smtp_pass' ])) {
        require_once('include/includes/libs/AzDGCrypt.class.inc.php');
        $cr64 = new AzDGCrypt(DBDATE . DBUSER . DBPREF);
        $smtp[ 'smtp_pass' ] = $cr64->crypt($_POST[ 'smtp_pass' ]);
    }
    unset($_POST[ 'smtp_pass' ]);

    foreach ($keys as $key) {
        if (isset($_POST[ $key ])) {
            $smtp[ $key ] = $_POST[ $key ];
        }
    }
    if (!isset($_POST[ 'smtp_pop3beforesmtp' ])) {
        $smtp[ 'smtp_pop3beforesmtp' ] = 0;
    }

    $smtpsql = escape(serialize($smtp), 'textarea');
    db_query('UPDATE `prefix_allg` SET `t1` = "' . $smtpsql . '" WHERE `k` = "smtpconf"');
    if (db_affected_rows() == 0) {
        echo '<h2>Es wurden keine &Auml;nderungen vorgenommen!</h2>';
    } else {
        echo '<h2>&Auml;nderungen gespeichert</h2>';
    }
}
// Formular ausgeben
$tpl = new tpl('smtpconf', 1);
$smtp[ 'smtp' ] = $allgAr[ 'mail_smtp' ] ? 1 : 0;
$smtp[ 'smtp_selauth' ] = arlistee($smtp[ 'smtp_auth' ], $authMethods);
$smtp[ 'smtp_pass' ] = (isset($smtp[ 'smtp_pass' ]) and !empty($smtp[ 'smtp_pass' ])) ? 1 : 0;
$smtp[ 'ANTISPAM' ] = get_antispam('adminuser_action', 0, true);
$tpl->set_ar_out($smtp, 0);
$design->footer();

?>