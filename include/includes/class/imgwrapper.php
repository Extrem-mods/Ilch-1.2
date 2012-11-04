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

    //MIME Header für alle Unterstützten Typen
    CONST PNG_HEADER = 'image/png';
    CONST JPG_HEADER = 'image/jpeg';
    CONST GIF_HEADER = 'image/gif';
    CONST SVG_HEADER = 'image/svg+xml';

    private static $cache = PATH.'cache/images/';
    protected $supported = array();
    protected $lib = 0;


    //**
    * Prüft welche Vormate durch die Klasse Unterstützt werden
    */
    public function __construct(){
        if($this->imagickUsable()){
            $this->lib = $this->imagickUsable();
            $this->supported[PNG] = array('i' => true, 'c' = true);
            $this->supported[JPG] = array('i' => true, 'c' = true);
            $this->supported[GIV] = array('i' => true, 'c' = true);
            $this->supported[SVG] = array('i' => true, 'c' = false);
        }elseif(function_exists('gd_info')){
            $this->lib = 3;
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
    * Prüft ob eine Datei mit mit den gewünschten Maßen und typ gecacht und aktuell ist und gibt im erfolgsfall den entsprechenden Pfad zurück.
    */
    private function isCached($id, $typ, $height, $width){
        $size = $this->transformSize($id, $height, $width);
        $result = db_query("select `path`, `name` , `prefix_images`.`typ` as `typ`, UNIX_TIMESTAMP(`last_edit`) as `last_edit` FROM `prefix_images`
            LEFT JOIN `prefix_images_cache` ON (`prefix_images`.`id` = `prefix_images_cache`.`id`)
            where `prefix_images`.`id` = $id AND `prefix_images_cache`.`typ` = $typ AND `prefix_images_cache`.`height` = {$size['height']} AND `prefix_images_cache`.`width` = {$size['width']}");
        if($resulr = mysql_fetch_array($result)){
            $file = PATH . self:$cache . $id.'_'.$size['height'].'x'.$size['width'].'.'.$typ;
            $file_org = PATH.$result['path'].$result['name'].'.'.$result['typ'];
            if(file_exists($file) && file_exists($file_org)
              && $result['last_edit'] >= filemtime($file) && $result['last_edit'] >= filemtime($file_org)){
                return $file;
            }
        }
        return false;
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
            $height = round($height * $result['height']/100)
        }else{
            $height = intval($height);
        }

        if(preg_match('/[0-9]+[\.]?[0-9]*%/', $width)){
            $width = intval(substr($width, 0,-1));
            $width = round($width * $result['width']/100)
        }else{
            $width = intval($width);
        }

        $new = array();
        if($height == -1 && $width == -1){
            $new = $result;
        }elseif($height == -1){
            $height = round($result['height'] / $result['width'] * $width);
            $new[0] = $new['height'] = $height;
            $new[1] = $new['width'] = $width;
        }elseif($width == -1){
            $width = round($result['width'] / $result['height'] * $height);
            $new[0] = $new['height'] = $height;
            $new[1] = $new['width'] = $width;
        }else{
            $new[0] = $new['height'] = $height;
            $new[1] = $new['width'] = $width;
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
            $result = db_query("select `typ` FROM `prefix_images` WHERE `id` = $id ");
            if($result = mysql_fetch_row($result)){
                $header = self::getHeader($result[0]);
            }else{
                $header = 'HTTP/1.0 404 Not Found';
            }
        }elseif(!empty($typ) && strlen($typ) == 3){
            $header = self::getHeader($typ);
        }
        if(!headers_sent()) header($header);
        echo $this->fetch($id, $typ, $height, $width);
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
        if(@exec('/usr/bin/convert --version')) return 2;
        return FALSE;
    }

    /**
    *
    */
    public function getLibType(){
        return $this->lib;
    }

    /**
    * Git Ein Bild ohne Modifikation aus.
    */
    public static function get($id){
        $id = intval($id);
        $result = db_query("select `path`, `name` , `typ` FROM prefix_images where `id` = $id");
        if($result = mysql_fetch_assoc($result)){
            $file = PATH.$result['path'].$result['name'].'.'.$result['typ'];
            if(file_exists($file)){
                if(!headers_sent()){
                    header(self::getHeader($typ));
                }
                return @readfile($file);
            }
        }
    }

    /**
    * Gibt den Kompletten header für ein angegebenen Dateityp zurück
    */
    public static function getHeader($typ){
        $const = 'self::' +strtoupper($typ) + '_HEADER';
        if(defined($const)){
            $header = $constant($const);
        }else{
           $header = '';
        }
        return $header;
    }

    /**
    * Gibt das für das Betrachten eines Bildes nötigen mindest level zurück
    * @return Fals Wenn das Bild nicht registriert ist, ansonsten 0 - -9 Je nach Level. Achtung: KAnn sowohl FALS als aich 0 zurück geben. Wenn ihr auf nichtexistenz oder auf 0 Prüft immer === verwenden
    */
    public static needRight($id){
        $id = intval($id);
        $result = db_query("select `min_right` FROM `prefix_images` where `id` = $id");
        if($result = mysql_fetch_row($resulr)){
            return $result[0];
        }
        return FALSE;

    }
}
