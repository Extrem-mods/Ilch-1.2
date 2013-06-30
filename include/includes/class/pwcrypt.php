<?php
/**
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL)
 * @copyright (C) 2000-2012 ilch.de
 * @version $Id$
 */
defined('main') or die('no direct access');

/**
 * PwCrypt
 *
 * Achtung: beim Übertragen von mit 2a erzeugten Passwörtern auf einen anderen PC/Server,
 * dort kann es u.U. Passieren, dass eine Authentifikation nicht mehr möglich ist,
 * da 2a auf einigen System fehlerhafte Ergebnisse liefert.
 * Versuche dann bitte 2x bzw. 2y.
 *
 * @author finke <Surf-finke@gmx.de>
 * @copyright Copyright (c) 2012
 */
class PwCrypt
{
    const LETTERS = 1;    //0001
    const NUMBERS = 2;    //0010
    const ALPHA_NUM = 3;    //0011
    const URL_CHARACTERS = 4;   //0100
    const FOR_ULR = 7;    //0111
    const SPECIAL_CHARACTERS = 8; //1000
    //Konstanten für die Verschlüsselung
    const MD5 = '1';
    const BLOWFISH_OLD = '2a';
    const BLOWFISH = '2y';
    const BLOWFISH_FALSE = '2x';
    const SHA256 = '5';
    const SHA512 = '6';

    private $hashAlgorithm = self::SHA256;

    /**
     * @param string $lvl Gibt den zu verwendenden Hashalgorithmus an (Klassenkonstante)
     */
    public function __construct($lvl = '')
    {
        if (!empty($lvl)) {
            $this->hashAlgorithm = $lvl;
        }

        /* Wenn 2a gewählt aber 2y verfügbar: nutze trotzdem 2y, da dies sicherer ist; wenn 2x oder 2y gewählt
         * aber nicht verfügbar, nutze 2a */
        if (version_compare(PHP_VERSION, '5.3.5', '<')
            && ($this->hashAlgorithm === self::BLOWFISH || $this->hashAlgorithm === self::BLOWFISH_FALSE)
        ) {
            $this->hashAlgorithm == self::BLOWFISH_OLD;
        } elseif (version_compare(PHP_VERSION, '5.3.5', '>=') && $this->hashAlgorithm == self::BLOWFISH_OLD) {
            $this->hashAlgorithm = self::BLOWFISH;
        }

        // Prüfen welche Hash Funktionen Verfügbar sind. Ab 5.3 werden alle Mitgeliefert
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            if ($this->hashAlgorithm === self::SHA512 && (!defined('CRYPT_SHA512') || CRYPT_SHA512 !== 1)) {
                $this->hashAlgoriathm = self::SHA256; // Wenn SHA512 nicht verfügbar, versuche SHA256
            }
            if ($this->hashAlgorithm === self::SHA256 && (!defined('CRYPT_SHA256') || CRYPT_SHA256 !== 1)) {
                $this->hashAlgorithm = self::BLOWFISH_OLD; // Wenn SHA256 nicht verfügbar, versuche BLOWFISH
            }
            if ($this->hashAlgorithm === self::BLOWFISH_OLD && (!defined('CRYPT_BLOWFISH') || CRYPT_BLOWFISH !== 1)) {
                $this->hashAlgorithm = self::MD5; // Wenn BLOWFISH nicht verfügbar, nutze MD5
            }
        }
    }

    /**
    * Liefert eine Zufallszahl zwichen gewählten Grenzen
    * 
    *Liefert Eine Zufallszahl zwichen $min und $max. Der Parameter $quality bestimmt die Qualität der verwendeten Zufallszahlen.
    * 1: Die Zufallszahlen werden durch einen Pseudozufallsgenerator erzeugt
    * 2: Die Zahlen werden duch einen Pseudozufallszahlen Generator erzeugt, welcher regelmäßig mit echten Zufallszahlen neu initiiert wird
    * 3: Echte Zufallszahlen. Diese sind allerdings nur begrenztz verfügbar und blockieren, wenn die verfügbare entropie erschöpft ist.
    * Es wird empfohlen die my_crypt erweiterung zu installieren, ohen diese stehet unter Windows nur Qualitätsstufe 1 zur Verfügung.
    * 
    * @author finke <Surf-finke@gmx.de>
    * @param $min Der niedrigste zurückzugebende Wert (Vorgabe: 0) 
    * @param $max Der nhöchste zurückzugebende Wert (Vorgabe: 255, max ) 
    * @param $quality Angabe über die Qualität der Zufallszahlen. (1-3, Vorgabe 2)
    * @return int Zufahlszahl
    */
    public static function getRndNumber($min = 0, $max = 255, &$quality = 2){
        $quality = intval($quality);
        if($min > $max){
            return false;
        }
        $bytes = (int)ceil(log($max, 2)/8);
        if($bytes > 4){
            return false;
        }        
        $n = $max - $min + 1;
        $m = (float)pow(2,8*$bytes);

        if($n > $m){
            return false;
        }

        $k = $n%$m;

        do{
            $number = '';
            if(function_exists('mcrypt_create_iv')){
                if ((version_compare(PHP_VERSION, '5.3.0', '<')
                && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
                || $quality <=1) {
                    $number = mcrypt_create_iv($bytes,  MCRYPT_RAND);
                    $quality =1;
                }elseif($quality >=3){
                    $number = mcrypt_create_iv($bytes,  MCRYPT_DEV_RANDOM );
                    $quality = 3;
                }elseif($quality == 2){
                    $number = mcrypt_create_iv($bytes,   MCRYPT_DEV_URANDOM);
                }
            }else{
                if($quality >=3){
                    $quality = 3;
                    $fp = @fopen('/dev/random','rb');
                    if ($fp !== FALSE) {
                        $number = fread($fp, $bytes);
                    }
                    if(strlen($number) != $bytes){
                        $quality = 2;
                    }                    
                }
                if($quality == 2){
                    $fp = @fopen('/dev/urandom','rb');
                    if ($fp !== FALSE) {
                        $number = fread($fp, $bytes);
                    }
                    if(strlen($number) != $bytes){
                        $quality = 1;
                    }
                }
                if($quality <=1){
                    $number = '';
                    for($i = 0; $i < $bytes;$i++){
                        $number .= pack('C', mt_rand(0,255));
                    }
                    $quality = 1;
                }
            }
            
            $number = str_pad($number, 4, chr(0), STR_PAD_LEFT);
            $number = unpack('N', $number);
            $number = $number[1];
        }while($number >= ($m - $k));
        return $min + $number % $k;
    }


    /**
     * Generiert einen String, welcher die Grundlage für das erzeugen von Zufallszahlen bildet
     *
     * @param integer $chars Angabe welche Zeichen verwendet werden
     * @return string
     */
    private static function generatePool($chars = self::ALPHA_NUM){
        $pool = '';
        if ($chars & self::LETTERS) {
            $pool .= 'abcdefghijklmnopqrstuvwxyz';
            $pool .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        if ($chars & self::NUMBERS) {
            $pool .='0123456789';
        }

        //in einer URL nicht reservierte Zeichen
        if ($chars & (self::URL_CHARACTERS | self::SPECIAL_CHARACTERS)) {
            $pool .= '-_.~';
        }

        //restiliche Sonderzeichen
        if ($chars & self::SPECIAL_CHARACTERS) {
            $pool .= '!#$%&()*+,/:;=?@[]';
        }
        return $pool;
    }

    /**
     * Erstellt eine zufällige Zeichenkette
     *
     * @param integer $size Länge der Zeichenkette
     * @param integer $chars Angabe welche Zeichen für die Zeichenkette verwendet werden
     * @return string
     */
    public static function getRndString($size = 20, $chars = self::ALPHA_NUM, &$quality = 2)
    {
        $pool = str_shuffle(self::generatePool($chars));
        $pool_size = strlen($pool);
        $string = '';
        for ($i = 0; $i < $size; $i++) {
            //TODO: Zufallszahlen aus /dev/random bzw /dev/urandom wenn verfügbar
            $string .= $pool[self::getRndNumber(0, $pool_size - 1, $quality)];
        }
        return $string;
    }

    /**
     * Erstellt eine zufällige Zeichenkette die garantiert eineindeutig ist
     * Der erste Teil, je nach Verwendeten Zeichen 8-10 Stellen, werden dabei in abhänigkeit der Systemzeit berechet, der rest wird zufällig aufgefüllt.
     *
     * @param integer $size Länge der Zeichenkette
     * @param integer $chars Angabe welche Zeichen für die Zeichenkette verwendet werden
     * @return string
     */
    public static function getUuid($size = 15, $chars = self::ALPHA_NUM){

        $pool = self::generatePool($chars);
        $pool_size = strlen($pool);
        $string_bin = base_convert(str_replace(array(' ', '.'), '', microtime()), 10,2);

        if(strlen($string_bin)%7 != 0){
            $string_bin = str_pad($string_bin, (strlen($string_bin) + (7-strlen($string_bin)%7)),'0', STR_PAD_LEFT);
        }

        $array = str_split($string_bin, 7);
        $string = '';

        foreach($array as $i){
            $string .=   $pool[(base_convert($i, 2, 10))%$pool_size];
        }

        if($size < strlen($string)){
            return substr($string, 0, $size);
        }else{
            return $string . self::getRndString($size - strlen($string), $chars);
        }
    }

    /**
     * Prüft, ob der übergebene Hash, im crpyt Format ist
     *
     * @param mixed $hash
     * @return boolean
     */
    public static function isCryptHash($hash)
    {
        return (preg_match('/^\$([156]|2[axy])\$/', $hash) === 1);
    }

    /**
     * Gibt den Code der gewählten/genutzen Hashmethode zurück (Crpyt Konstante)
     *
     * @return string
     */
    public function getHashAlgorithm()
    {
        return $this->hashAlgorithm;
    }

    /**
     * Erstellt ein Hash für das übergebene Passwort
     *
     * @param string $passwd Klartextpasswort
     * @param string $salt Salt für den Hashalgorithus
     * @param integer $rounds Anzahl der Runden für den verwendeten Hashalgorithmus
     * @return string Hash des Passwortes (Ausgabe von crypt())
     */
    public function cryptPasswd($passwd, $salt = '', $rounds = 0)
    {
        $salt_string = '';
        switch ($this->hashAlgorithm) {
            case self::SHA512:
            case self::SHA256:
                $salt = (empty($salt) ? self::getRndString(16, self::ALPHA_NUM) : $salt);
                if ($rounds < 1000 || $rounds > 999999999) {
                    $rounds = mt_rand(2000, 10000);
                }
                $salt_string = '$' . $this->hashAlgorithm . '$rounds=' . $rounds . '$' . $salt . '$';
                break;
            case self::BLOWFISH:
            case self::BLOWFISH_OLD:
            case self::BLOWFISH_FALSE:
                $salt = (empty($salt) ? self::getRndString(22, self::ALPHA_NUM) : $salt);
                if ($rounds < 4 || $rounds > 31) {
                    $rounds = mt_rand(6, 10);
                }
                //Verwendet 2x, wenn verfügbar, auch wenn 2a angegeben wurde
                $salt_string = '$' . $this->hashAlgorithm . '$' . $rounds . '$' . $salt . '$';
                break;
            case self::MD5:
                $salt = (empty($salt) ? self::getRndString(12, self::ALPHA_NUM) : $salt);
                $salt_string = '$' . $this->hashAlgorithm . '$' . $salt . '$';
                break;
            default:
                return false;
        }
        $crypted_pw = crypt($passwd, $salt_string);
        if (strlen($crypted_pw) < 13) {
            return false;
        }
        return $crypted_pw;
    }

    /**
     * Prüft, ob das Klartextpasswort dem Hash "entspricht"
     *
     * @param mixed $passwd Klartextpasswort
     * @param mixed $crypted_passwd Hash des Passwortes (aus der Datenbank)
     * @param boolean $backup wenn Check fehlschlägt und das alte passwort mit BLOWFISH_OLD verschlüsselt wurde,
     *      werden beide Varianten noch einmal explizit geprüft, wenn verfügbar. Nur nach Transfer der Datenbank verwenden,
     *      da dies ein Sicherheitsrisiko darstellen kann
     * @return boolean
     */
    public function checkPasswd($passwd, $crypted_passwd, $backup = false)
    {
        if (empty($crypted_passwd)) {
            return false;
        }
        if (self::isCryptHash($crypted_passwd)) {
            $new_chrypt_pw = crypt($passwd, $crypted_passwd);
            if (strlen($new_chrypt_pw) < 13) {
                return false;
            }
        } else {
            $new_chrypt_pw = md5($passwd);
        }
        if ($new_chrypt_pw == $crypted_passwd) {
            return true;
        } else {
            if ($backup == true
                && version_compare(PHP_VERSION, '5.3.5', '>=')
                && substr($crypted_passwd, 0, 4) == '$2a$'
            ) {
                $password_x = '$2x$' . substr($crypted_passwd, 4);
                $password_y = '$2y$' . substr($crypted_passwd, 4);
                $password_neu_x = crypt($passwd, $password_x);
                $password_neu_y = crypt($passwd, $password_y);
                if ($password_neu_x === $password_x || $password_neu_y === $password_y) {
                    return true;
                }
            }
        }
        return false;
    }
}
