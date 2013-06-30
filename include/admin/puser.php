<?php
/**
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL)
 * @copyright (C) 2000-2010 ilch.de
 * @version $Id$
 */
defined('main') or die('no direct access');
defined('admin') or die('only admin access');

$design = new design('Ilch Admin-Control-Panel :: Registrierungen', '', 2);
$design->header();

$tpl = new tpl('puser', 1);
// add pending user
if ($menu->get(1) == "confirm" AND isset($_GET[ 'check' ])) {
    $erg = db_query("SELECT * FROM `prefix_usercheck` WHERE `check` = '" . escape($_GET[ 'check' ], 'string') . "'");
    if (db_num_rows($erg) == 1) {
        $row = db_fetch_assoc($erg);
        $row[ 'name_clean' ] = escape(get_lower($row[ 'name' ]), 'string');
        switch ($row[ 'ak' ]) {
            // confirm regist
            case 1:
                if (0 == db_count_query("SELECT COUNT(*) FROM `prefix_user` WHERE `name_clean` = BINARY '" . $row[ 'name_clean' ] . "'")) {
                    db_query("INSERT INTO `prefix_user` (`name`,`name_clean`,`pass`,`recht`,`regist`,`llogin`,`email`,`status`,`opt_mail`,`opt_pm`)
  			 VALUES('" . $row[ 'name' ] . "','" . $row[ 'name_clean' ] . "','" . $row[ 'pass' ] . "',-1,'" . time() . "','" . time() . "','" . $row[ 'email' ] . "',1,1,1)");
                    db_query("DELETE FROM `prefix_usercheck` WHERE `check` = '" . escape($_GET[ 'check' ], 'string') . "'");
                } else {
                    $tpl->set_out('error', 'Username existiert bereits', 3);
                }
                break;
            // confirm new pass
            case 2:
                db_query("UPDATE `prefix_user` SET `pass` = '" . $row[ 'pass' ] . "' WHERE `name_clean` = BINARY '" . $row[ 'name_clean' ] . "'");
                db_query("DELETE FROM `prefix_usercheck` WHERE `check` = '" . escape($_GET[ 'check' ], 'string') . "'");
                break;
            // confirm new email
            case 3:
                list($id, $check) = explode('||', $row[ 'check' ]);
                db_query("UPDATE `prefix_user` SET `email` = '" . $row[ 'email' ] . "' WHERE `id` = " . escape($id, 'integer'));
                db_query("DELETE FROM `prefix_usercheck` WHERE `check` = '" . escape($_GET[ 'check' ], 'string') . "'");
                break;
            // join us
            case 4:
                echo '<br />Joinus kann über diese Liste nicht akzeptiert werden, mache diese über <a style="color:red;" href="http://ilch11.dev/admin.php?groups-joinus">Joinus Anfragen bearbeiten</a><br /><br />';
                break;
            // ak 5 remove account
            case 5:
                list ($id, $muell) = explode('-remove-', $row['check']);
                if ($id == $_SESSION['authid']) {
                    echo 'Der eigene Account ist auf diese Weise nicht l&ouml;schbar.';
                    break;
                }
                user_remove($id);
                db_query("DELETE FROM prefix_usercheck WHERE `check` = '" . escape($_GET['check'], 'string') . "'");
                break;
        }
    } else {
        $tpl->set_out('error', 'User nicht auffindbar', 3);
    }
}
// remove pending user
if ($menu->get(1) == "del" AND isset($_GET[ 'check' ])) {
    db_query("DELETE FROM `prefix_usercheck` WHERE `check` = '" . escape($_GET[ 'check' ], 'string') . "'");
}

$tpl->out(0);
$ak = array(
    '',
    'neuer User',
    'neues Passwort',
    'neue Emailadresse',
    'Join us'
    );
$c = 0;
$erg = db_query("SELECT `check`, `name`, `email`, `ak`, date_format(`datime`,'%k:%i Uhr %e.%c.%Y') as `time` FROM `prefix_usercheck` ORDER by `datime` DESC");
while ($row = db_fetch_assoc($erg)) {
    if ($class == 'Cmite') {
        $class = 'Cnorm';
    } else {
        $class = 'Cmite';
    }
    $c++;
    $row[ 'c' ] = $c;
    $row[ 'class' ] = $class;
    if ($row[ 'ak' ] == 3) {
        list($id, $check) = explode('||', $row[ 'check' ]);
        $row[ 'name' ] = @db_result(db_query("SELECT `name` FROM `prefix_user` WHERE `id` = " . $id), 0);
    }
    $row[ 'aktion' ] = $ak[ $row[ 'ak' ] ];
    $tpl->set_ar_out($row, 1);
}

$tpl->out(2);

$design->footer();

?>