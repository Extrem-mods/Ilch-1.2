<?php
/**
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL)
 * @copyright (C) 2000-2010 ilch.de
 * @version $Id$
 */
defined('main') or die('no direct access');
defined('admin') or die('only admin access');

// Funktionen 
function installConnector($path){

}

function removeConnector($id){

}

//Aufruf von Sieten Ohne Anzeige des designs
if($menu->get(1) === 'action'){
    $msg = '';
    switch ($menu->get(2)) {
        case 'del':
            if(removeConnector($menu->get(3))){
                $msg = "Connector erfolgreich gelöscht.";
            }else{
                $msg = "Beim Löschen des Connectors ist ein fehler aufgetreten.";
            }
        break;    
    }
    wd('admin.php?tcp', $msg);
    exit;
}

// Anzeige
$design = new design('Ilch Admin-Control-Panel :: TCP', '', 2);
$design->header();
switch ($menu->get(1)) {
    case 'new':
    break;
    case 'edit':
    break;
    case 'show':
    default:    
}

$design->footer();
