<?php
/**
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL)
 * @copyright (C) 2000-2012 ilch.de
 * @version $Id$
 */
defined('main') or die('no direct access');

/**
 * ImgWrapper
 *
 * @author finke <Surf-finke@gmx.de>
 * @copyright Copyright (c) 2012
 */
class ImgWrapper{

    //Einige Konstanten
    //Typen
    CONST PNG = 'png';
    CONST JPG = 'jpg';
    CONST GIF = 'gif';
    CONST SVG = 'svg';

    //MIME Header für alle endungen
    CONST PNG_HEADER = 'image/png';
    CONST JPG_HEADER = 'image/jpeg';
    CONST GIF_HEADER = 'image/gif';
    CONST SVG_HEADER = 'image/svg+xml';

    private const CACHE = PATH.'cache/images/';
    protected $supported = array();
    $private $img = NULL;

    //**
    * Prüft welche Vormate durch die Klasse Unterstützt werden
    */
    public function __construct(){
        if($this->imagickUsable()){
            $this->supported[PNG] = array('i' => true, 'c' = true);
            $this->supported[JPG] = array('i' => true, 'c' = true);
            $this->supported[GIV] = array('i' => true, 'c' = true);
            $this->supported[SVG] = array('i' => true, 'c' = false);
        }elseif(function_exists('gd_info')){
            $gdinfo = gd_info();
            $this->supported[JPG] = array('i' => false, 'c' = false);
            if(isset($gdinfo['JPEG Support'])){
                $this->supported[JPG]['i'] = $gdinfo['JPEG Support'];
                $this->supported[JPG]['c'] = $gdinfo['JPEG Support'];
            }elseif(isset($gdinfo['JPG Support'])){
                $this->supported[JPG]['i'] = $gdinfo['JPG Support'];
                $this->supported[JPG]['c'] = $gdinfo['JPG Support'];
            }

            $this->supported[PNG] = array('i' => false, 'c' = false);
            $this->supported[PNG]['i'] = $gdinfo['PNG Support'];
            $this->supported[PNG]['c'] = $gdinfo['PNG Support'];

            $this->supported[GIF] = array('i' => false, 'c' = false);
            $this->supported[GIF]['i'] = $gdinfo['GIF Read Support'];
            $this->supported[GIF]['c'] = $gdinfo['GIF Create Support'];

            $this->supported[SVG] = array('i' => false, 'c' = false);
        }else{
            throw new Exception('No image manipulation library available on this syste.');
        }

    }

    /**
    *
    */
    public function __destruct(){
    }
    
    /**
    *
    */
    private function isCached($id, $typ, $height, $width){
        $size = $this->transformSize
        
        $result = db_query("SELECT `last_edit` FROM `prefix_images_cache` WHERE `id` = $id AND `typ` = $typ ");
        
    }
    
    /**
    * Wandelt Größenangaben in absolute Angaben um
    *
    */
    private function transformSize($id, $height = -1, $width = -1){
        $id = intval($id);
        $result = db_query("SELECT `height`, `width` FROM `prefix_images` WHERE `id` = $id"));
        if(!($result = mysql_fetch_array($result))) return NULL;
        //Umwandeln von Prozentangaben
        if(preg_match('/[0-9]*[\.]?[0-9]*%/', $height)){
            $height = intval(substr($height, 0,-1));
            $height = round($height * $result['height'])
        }else{
            $height = intval($height);
        }

        if(preg_match('/[0-9]*[\.]?[0-9]*%/', $width)){
            $width = intval(substr($width, 0,-1));
            $width = round($width * $result['width'])
        }else{
            $width = intval($width);
        }

        $new = array();
        if($height == -1 && $width == -1){
            $new = $result;
        }elseif($height == -1){
            $height = round($result['height'] / $result['width'] * $width);
            $new[0] = $new['height'] = $height;
            $new[1] = $new['width'] = (int)$width;
        }elseif($width == -1){
            $width = round($result['width'] / $result['height'] * $height);
            $new[0] = $new['height'] = (int)$height;
            $new[1] = $new['width'] = $width;
        }else{
            $new[0] = $new['height'] = (int)$height;
            $new[1] = $new['width'] = (int)$width;
        }
        return $new;
    }

    /**
    * Gibt ein Bild im gewünsten Vormat und auflösung zurück
    * @param integer $id ID des Bildes in der Datenbank
    * @param mixed $typ Entweder eine Klassenkonstanden oder NULL für den Orginaltyp
    * @param int $height Höhe, psitive Werte geben die Anzahl an Pixel an, -1 bedeutet orginalgröße, wenn Höhe und Breite mit -1 angegeben sind ; wenn nur ein es von beiden -1 ist, wird der Wert anhand des Verhältnisses zum angegebenen Wert Berechnet
    * @param int $width Breite; positive Werte geben die Anzahl an Pixel an, -1 bedeutet orginalgröße, wenn Höhe und Breite mit -1 angegeben sind ; wenn nur ein es von beiden -1 ist, wird der Wert anhand des Verhältnisses zum angegebenen Wert Berechnet
    *
    */
    public function fetch($id, $typ = NULL, $height = -1, $width = -1){

    }
    /**
    * Setzt den Header für ein Bestimtes Bild und gibt dieses mit den angegebenen Einstellungen aus
    * @param integer $id ID des Bildes in der Datenbank
    * @param mixed $typ Entweder eine Klassenkonstanden oder NULL für den Orginaltyp
    * @param int $height Höhe, psitive Werte geben die Anzahl an Pixel an, -1 bedeutet orginalgröße, wenn Höhe und Breite mit -1 angegeben sind ; wenn nur ein es von beiden -1 ist, wird der Wert anhand des Verhältnisses zum angegebenen Wert Berechnet
    * @param int $width Breite; positive Werte geben die Anzahl an Pixel an, -1 bedeutet orginalgröße, wenn Höhe und Breite mit -1 angegeben sind ; wenn nur ein es von beiden -1 ist, wird der Wert anhand des Verhältnisses zum angegebenen Wert Berechnet
    */
    public function print($id, $typ = NULL, $height = -1, $width = -1){
        if($typ === NULL){
        }elseif(!empty($typ) && strlen($typ) == 3){
            $const = 'self::' +strtoupper($typ) + '_HEADER';
            if(!defined($const)){
                return FALSE;
            }
            $header = constant($const);
        }else{
            return FALSE;
        }
    }

    /**
    *
    */
    public function getSupportedTyps(){
        return $this->supported;
    }
    
    /**
    *
    */
    public function convert($id, $to = self::PNG, $height = -1, $width = -1){


    }

    /**
    * Prüft ob die Imagick Bibliotek verfügbar ist.
    * return Gibt FALSE zurück, wenn nicht. 1 wenn die PHP Erweiterung eingebunden ist und 2 Wenn es über das System aufgerufen werden kann
    */
    public function imagickUsable(){
        if(class_exists('Imagick')) return 1;
        if(system('/usr/bin/convert --version')) return 2;
        return FALSE;
    }
}
