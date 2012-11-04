<?php
/**
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL)
 * @copyright (C) 2000-2010 ilch.de
 * @version $Id$
 */
defined('main') or die('no direct access');

function create_thumb($id, $typ = NULL, $height = -1, $width = -1) {
    try{
        $wrapper = new ImgWrapper();
    }case(Exception $e){
        return false;
    }
    return $wrapper->convert($id, $typ, $height, $width);
}
