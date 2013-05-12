<?php
/**
 *
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL)
 * @copyright (C) 2000-2010 ilch.de
 * @version $Id$
 */
defined('main') or die('no direct access');

//TODO headeradd + bodyend bei ajax mit hinzufügen

if (!isset($ILCH_HEADER_ADDITIONS)) {
    $ILCH_HEADER_ADDITIONS = '';
}
if (!isset($ILCH_BODYEND_ADDITIONS)) {
    $ILCH_BODYEND_ADDITIONS = '';
}
class design extends tpl {
    protected $html;
    protected $design;
    protected $vars;
    protected $was;
    protected $file;
    protected $ajax;
    protected $headerAdds;
    protected $bodyendAdds;
    protected $json;

    public function __construct($title, $hmenu, $was = 1, $file = null) {
        global $allgAr, $menu;

        header('Content-Type: text/html;charset=UTF-8');
        if (AJAXCALL) {
            $this->ajax = true;
            $this->json = array('title' => $title,
                                'hmenu' => $hmenu);
        } else {
            $this->ajax = false;

            if (!is_null($file)) {
                echo '<div style="display: block; background-color: #FFFFFF; border: 2px solid #ff0000;">!!Man konnte in einer PHP Datei eine spezielle Index angeben. Damit das Design fuer diese Datei anders aussieht. Diese Funktion wurde ersetzt. Weitere Informationen im Forum auf ilch.de ... Thema: <a href="http://www.ilch.de/forum-showposts-13758-p1.html#108812">http://www.ilch.de/forum-showposts-13758-p1.html#108812</a></div>';
            }

            $this->vars = array();
            $this->file = $file; // setzte das file standard 0 weil durch was definiert
            $this->was = $was; // 0 = smalindex, 1 = normal index , 2 = admin
            $this->design = tpl::get_design();
            $link = $this->htmlfile();
            $this->headerAdds = '';
            $this->bodyendAdds = '';


            $tpl = new tpl($link, 2);
            if ($tpl->list_exists('boxleft')) {
                $tpl->set('boxleft', $this->get_boxes('l', $tpl));
            }
            if ($tpl->list_exists('boxright')) {
                $tpl->set('boxright', $this->get_boxes('r', $tpl));
            }
            // ab 0.6 =  ... menu listen moeglich
            for ($i = 1; $i <= $allgAr[ 'menu_anz' ]; $i++) {
                if ($tpl->list_exists('menunr' . $i)) {
                    $tpl->set('menunr' . $i, $this->get_boxes($i, $tpl));
                }
            }

            $ar = array(
                'TITLE' => $this->escape_explode($title),
                'HMENU' => $this->buildBreadcrumb($hmenu, $tpl),
                'SITENAME' => $this->escape_explode($allgAr[ 'title' ]),
                'hmenuende' => '',
                'vmenuende' => '',
                'hmenubegi' => '',
                'vmenubegi' => '',
                'hmenupoint' => '',
                'vmenupoint' => '',
                'DESIGN' => $this->design
                );
            $tpl->set_ar($ar);
            $this->html = $tpl->get(0);
            $this->html .= '{EXPLODE}';
            $this->html .= $tpl->get(1);
            unset($tpl);

            $zsave0 = array();
            preg_match_all("/\{_boxes_([^\{\}]+)\}/", $this->html, $zsave0);

            $this->replace_boxes($zsave0[1]);
            unset($zsave0);
            $this->vars_replace();
            unset($this->vars);

            $this->html = explode('{EXPLODE}', $this->html);
        }
    }

    public function addheader($text) {
        $this->headerAdds .= $text;
    }

    public function header($addons = '') {
        global $ILCH_HEADER_ADDITIONS, $allgAr;
        $this->addheader($ILCH_HEADER_ADDITIONS);
        if (isset($this->html[0]) and !$this->ajax) {
            $this->html[0] = str_replace('</head>', $this->load_addons($addons) . $this->headerAdds . "\n
			<link rel=\"stylesheet\" type=\"text/css\" href=\"include/includes/css/jquery/templates/".$allgAr['jqueryui']."/jquery-ui.css\" />\n
			</head>", $this->html[ 0 ]);
            echo $this->html[0] . '<div id="icContent">';
            unset($this->html[0]);
        } else {
            ob_start();
        }
    }

    protected function getJqueryThingy($a, $b){
        $pattern = '%jquery-\d\.\d+(\.\d+)?(\.min)\.js%';
        if (preg_match($pattern, $a) == 1) {
            return -1;
        } elseif (preg_match($pattern, $b) == 1) {
            return 1;
        }
        return 0;
    }

    // Fuegt Dynamische und Statische *.js und *.css Dateien in den Header ein
    // Kann jedoch nur uerber die header-Funktion aufgerufen werden
    protected function load_addons($addons = '') {
        $buffer = '';
        if (!is_array($addons)) {
            $addons = Array($addons
                );
        }
        // Ordner nach dynamischen Dateien durchsuchen
        $js = read_ext('include/includes/js/global', 'js');
        $css = read_ext('include/includes/css/global', 'css');
        // Dynamisches CSS laden (css vor js laden!)
        foreach ($css as $file) {
            $buffer .= "\n" . '<link rel="stylesheet" type="text/css" href="include/includes/css/global/' . $file . '" />';
        }
        // Dynamisches Javascript laden
        // sort jquery Top -- should be removed later by Olox
        usort($js, array($this, "getJqueryThingy"));

        foreach ($js as $file) {
            $buffer .= "\n" . '<script type="text/javascript" src="include/includes/js/global/' . $file . '"></script>';
        }
        // Alle statischen Inhalte pruefen
        foreach ($addons as $addon) {
            $dir = explode('.', $addon);
            $dir = end($dir);
            if (file_exists('include/includes/' . $dir . '/' . $addon)) {
                if ($dir == 'js') {
                    $buffer .= "\n" . '<script type="text/javascript" src="include/includes/' . $dir . '/' . $addon . '"></script>';
                } else if ($dir == 'css') {
                    $buffer .= "\n" . '<link rel="stylesheet" type="text/css" href="include/includes/' . $dir . '/' . $addon . '" />';
                }
            } else {
                $buffer = "\n" . '<script language="javascript">' . "\nalert('Couldn\'t find the file \"include/includes/" . $dir . "/" . $addon . "\"!');" . "\n</script>";
            }
        }
        return $buffer;
    }

    public function addtobodyend($text) {
        $this->bodyendAdds .= $text;
    }

    public function footer($exit = 0) {
        global $allgAr;
        if ($this->ajax) {
            $this->json['headerAdds'] = $this->headerAdds;
            $this->json['bodyendAdds'] = $this->bodyendAdds;
            $cntnt = ob_get_clean();
			$this->json['content'] = str_replace('_jqueryuitheme_', '/'.$allgAr['jqueryui'].'/', $cntnt);
            if (isset($allgAr['modrewrite']) and $allgAr['modrewrite'] == 1) {
                $this->json['content'] = self::rewriteLinks( $this->json['content'] );
            }
            echo json_encode($this->json);
            exit;
        }
        $this->html[1] = str_replace('</body>', $this->bodyendAdds . "\n</body>", $this->html[1]);
        echo '</div>' . $this->html[1];
        unset($this->html[1]);
        if (isset($allgAr['modrewrite']) and $allgAr['modrewrite'] == 1) {
			$cntnt = ob_get_clean();
			$cntnt = str_replace('_jqueryuitheme_', '/'.$allgAr['jqueryui'].'/', $cntnt);
            echo self::rewriteLinks( $cntnt) ;
        }
        if ($exit == 1) {
            if (DEBUG) {
                debug_out();
            }
            exit();
        }
    }

    public static function ajax_boxload() {
        global $allgAr, $menu;
        if (AJAXCALL and isset($_GET['boxreload']) and $_GET['boxreload'] == 'true') {
            ob_start();
            $file = $menu->get_url('box');
            if ($file !== false) {
                require $file;
            }
            $obcontent = ob_get_clean();
            if (isset($allgAr['modrewrite']) and $allgAr['modrewrite'] == 1) {
                $obcontent = self::rewriteLinks($obcontent);
            }
            /* Debug kann später gelöscht werden
               $obcontent = ob_get_clean();
               $enc = mb_detect_encoding($obcontent);
               //$obcontent = utf8_encode($obcontent);
               $array = array('content' => $obcontent, 'stringenc' => $enc);
               $json = json_encode($array);
               if (strlen($json)) {
               echo $json;
               } else {
               echo 'mb_check_encoding: ' . $enc ."\n";
               echo "\nJSON ERROR CODE: " . json_last_error();
               echo "\npossible Errors\nJSON_ERROR_DEPTH: ", JSON_ERROR_DEPTH, " - Maximale Stacktiefe überschritten",
               "\nJSON_ERROR_CTRL_CHAR: ", JSON_ERROR_CTRL_CHAR, ' - Unerwartetes Steuerzeichen gefunden',
               "\nJSON_ERROR_SYNTAX: ", JSON_ERROR_SYNTAX, ' - Syntaxfehler, ungültiges JSON',
               "\nJSON_ERROR_NONE: ", JSON_ERROR_NONE, ' - Keine Fehler', "\n Array vor json_encode:\n";
               print_r($array);
               }*/
            echo json_encode( array('content'=> $obcontent ) );
            exit;
        }
    }

    public static function rewriteLinks($html) {
        $html = preg_replace ('%href=\"\?([^\"]+)\"%Uis', "href=\"index.php?\\1\"", $html);
        $html = preg_replace ('%href=\"index.php\?([-0-9A-Z_]+)#([a-zA-Z0-9]+)\">%Uis', "href=\"\\1.html#\\2\">", $html);
        $html = preg_replace ('%href=\"index.php\?([-0-9A-Z_]+)\">%Uis', "href=\"\\1.html\">", $html);
        $html = preg_replace ('%action=\"\?([^\"]+)\"%Uis', "action=\"index.php?\\1\"", $html);
        $html = preg_replace ('%URL=\?([^\"]+)\"%Uis', "URL=index.php?\\1\"", $html);
        return $html;
    }

    protected function escape_explode($s) {
        $s = str_replace('{EXPLODE}', '&#123;EXPLODE&#125;', $s);
        return ($s);
    }

    protected function htmlfile_ini() {
        global $menu;
        $ma = $menu->get_string_ar();
        $ia = array();
        if (!file_exists('include/designs/' . $this->design . '/design.ini')) {
            return (false);
        }
        $ia = parse_ini_file('include/designs/' . $this->design . '/design.ini');
        arsort($ma);
        krsort($ia);
        foreach ($ia as $k => $v) {
            $k = preg_replace("/[^a-zA-Z0-9-*]/", "", $k);
            $k = str_replace('*', '[^-]+', $k);
            foreach ($ma as $k1 => $v1) {
                if (preg_match("/" . $k . "/", $k1) AND file_exists('include/designs/' . $this->design . '/' . $v)) {
                    return ($v);
                }
            }
        }
        return (false);
    }

    protected function htmlfile() {
        $ini = $this->htmlfile_ini();
        /*
        if ( !is_null ($this->file) AND file_exists ('include/designs/'.$this->design.'/templates/'.$this->file)) {
        $f = 'designs/'.$this->design.'/templates/'.$this->file;
        } elseif ( !is_null ($this->file) AND file_exists ('include/templates/'.$this->file)) {
        $f = 'templates/'.$this->file;
        */
        if ($this->was == 1 AND $ini !== false) {
            $f = 'designs/' . $this->design . '/' . $ini;
        } elseif ($this->was == 0 AND file_exists('include/templates/' . $this->design . '/templates/small_index.htm')) {
            $f = 'templates/' . $this->design . '/templates/small_index.htm';
        } elseif ($this->was == 0) {
            $f = 'templates/small_index.htm';
        } elseif ($this->was == 1) {
            $f = 'designs/' . $this->design . '/index.htm';
        } elseif ($this->was == 2) {
            $f = 'admin/design/index.htm';
        }
        return ($f);
    }

    protected function replace_boxes($zsave0) {
        foreach ($zsave0 as $v) {
            $dat = strtolower($v);
            $buffer = $this->get_boxcontent($dat);
            if ($buffer !== false) {
                $this->vars[ '_boxes_' . $v ] = $buffer;
            }
        }
        if (!is_array($this->vars)) {
            $this->vars = array();
        }
    }

    protected function vars_replace() {
        foreach ($this->vars as $k => $v) {
            $this->html = str_replace('{' . $k . '}', $v, $this->html);
        }
    }
    // ####
    protected function get_boxes($wo, tpl $tpl) {
        global $lang, $allgAr, $menu;
        if (is_numeric($wo)) {
            $datei = 'menunr' . $wo;
        } elseif ($wo == 'l') {
            $datei = 'boxleft';
            $wo = 1;
        } elseif ($wo == 'r') {
            $datei = 'boxright';
            $wo = 2;
        }

        $retur = '';
        $ex_ebene = 0;
        $ex_was = 1;
        $firstmep = false;
        $hovmenup = '';
        $abf = "SELECT * FROM `prefix_menu` WHERE wo = " . $wo . " ORDER by pos";
        $erg = db_query($abf);
        $menuar = $menupaths = array();
        while ($r = db_fetch_assoc($erg)) {
            //Nur Menüpunkte für die Rechte bestehen anzeigen
            if (($r['recht_type'] == 0 or $r['recht_type'] == 3) and ! has_right($r['recht'],'', true)) {
                continue;
            } elseif ($r['recht_type'] == 1 and $r['recht'] != $_SESSION['authright']) {
                continue;
            } elseif ($r['recht_type'] == 2 and $r['recht'] > $_SESSION['authright']) {
                continue;
            }
            $menuar[$r['pos']] = $r;
            $menupaths[$r['path']] = $r['pos'];
        }
        // Aktiven Punkt herausfinden
        foreach(array_reverse($menu->get_string_ar()) as $path) {
            $path = str_replace('self-', '', $path);
            if (isset($menupaths[$path])) {
                $act_pos = $menupaths[$path];
                break;
            }
        }
        // //Punkte löschen, die nicht angezeigt werden sollen
        // //so dass Untermenüpunkte nur vom aktiven Menüpunkt angezeigt werden
        // $todel = array();
        // //Punkte davor
        // for($i = $act_pos; $i > -1; $i--){
        // if (isset($menuar[$i]) and $menuar[$i]['ebene'] == 0) {
        // $todel_before = $i;
        // break;
        // }
        // }
        // $todel_after = count($menuar);
        // for($i = $act_pos+1; $i < $todel_after; $i++){
        // if (isset($menuar[$i]) and $menuar[$i]['ebene'] == 0) {
        // $todel_after = $i;
        // break;
        // }
        // }
        foreach ($menuar as $pos => $row) {
            // if ($row['ebene'] >  0 and ($pos < $todel_before  or $pos > $todel_after)) {
            // continue;
            // }
            $subhauptx = $row[ 'was' ];
            $whileMenP = ($subhauptx >= 7 ? true : false);
            if (($row[ 'was' ] >= 7 AND $ex_was == 1) OR ($ex_ebene < ($row[ 'ebene' ] - 1)) OR ($ex_was <= 4 AND $row[ 'ebene' ] != 0) OR ($row[ 'was' ] >= 7 AND !$tpl->list_exists($hovmenup))) {
                continue;
            }
            // nur wenn ein menu in die variable $menuzw geschrieben wurde
            // wird in diese if abfrage gesprungen
            if (($whileMenP === false) AND !empty($menuzw)) {
                $menuzw .= $this->get_boxes_get_menu_close($ex_ebene, 0, $menuzw, $wmpE, $wmpTE, $wmpTEE);
                $retur .= $tpl->list_get($datei, array(
                        htmlspecialchars($boxname),
                        $menuzw . $menuzwE
                        ));
                $menuzw = '';
            }
            if ($row[ 'was' ] == 1) {
                // die box wird direkt in die to return variable geschrieben
                $buffer = $this->get_boxcontent($row[ 'path' ]);
                $retur .= $tpl->list_get($datei, array($row[ 'name' ],
                        $buffer
                        ));
            } elseif ($row[ 'was' ] >= 2 AND $row[ 'was' ] <= 4) {
                // der name des menues wird gesetzt
                // und die variable wird gesetzt.
                $boxname = $row[ 'name' ];
                $menuzw = '';
                $menuzwE = '';
                $ex_ebene = 0; // ex ebene
                $hovmenu = '';
                if ($row[ 'was' ] == 2 AND $tpl->list_exists('hmenupoint')) {
                    $hovmenu = 'hmenu';
                } elseif ($row[ 'was' ] == 3 AND $tpl->list_exists('vmenupoint')) {
                    $hovmenu = 'vmenu';
                }
                $firstmep = true;
                if (!empty($hovmenu)) {
                    $menuzw .= $tpl->list_get($hovmenu . 'begi', array());
                    $menuzwE .= $tpl->list_get($hovmenu . 'ende', array());
                }
                $hovmenup = $hovmenu . 'point';
            } elseif ($whileMenP) {
                // menupunkt wird generiert
                $ebene = $row[ 'ebene' ];
                $menuTarget = ($subhauptx == 8 ? '_blank' : '_self');
                $act_pos = null;
                list($wmpA, $wmpE, $wmpTE, $wmpTEE) = explode('|', $tpl->list_get($hovmenup, array($menuTarget,
                            ($subhauptx == 8 ? '' : 'index.php?') . $row[ 'path' ],
                            $row[ 'name' ], ($row['pos'] == $act_pos ? 'active' : 'inactive')
                            )));
                if (!empty($menuzw) AND $firstmep === false) {
                    $menuzw .= $this->get_boxes_get_menu_close($ex_ebene, $ebene, $menuzw, $wmpE, $wmpTE, $wmpTEE);
                }
                $menuzw .= $wmpA;
                $firstmep = false;
            }

            $ex_was = $row[ 'was' ];
            $ex_ebene = $row[ 'ebene' ];
        }
        if (!empty($menuzw)) {
            $menuzw .= $this->get_boxes_get_menu_close($ex_ebene, 0, $menuzw, $wmpE, $wmpTE, $wmpTEE);
            $retur .= $tpl->list_get($datei, array(
                    htmlspecialchars($boxname),
                    $menuzw . $menuzwE
                    ));
        }
        return ($retur);
    }

    protected function get_boxes_get_menu_close($ex_ebene, $ebene, $menuzw, $wmpE, $wmpTE, $wmpTEE) {
        $menu1 = '';
        if ($ex_ebene == $ebene AND !empty($menuzw)) {
            $menu1 .= $wmpE . "\n";
        } elseif ($ex_ebene > $ebene) {
            $menu1 .= $wmpE . "\n";
            for ($i = 0; $i < ($ex_ebene - $ebene); $i++) {
                $menu1 .= $wmpTEE . "\n";
            }
        } elseif ($ex_ebene < $ebene) {
            $menu1 .= $wmpTE . "\n";
        }
        return ($menu1);
    }

    protected function get_boxcontent($box) {
        global $lang, $allgAr, $menuAr, $menu, $ILCH_HEADER_ADDITIONS, $ILCH_BODYEND_ADDITIONS;
        if (file_exists('include/boxes/' . $box)) {
            load_box_lang($box);
            $pfad = 'include/boxes/' . $box;
        } elseif (file_exists('include/contents/selfbp/selfb/' . str_replace('self_', '', $box))) {
            $pfad = 'include/contents/selfbp/selfb/' . str_replace('self_', '', $box);
        } elseif (file_exists('include/boxes/' . $box . '.php')) {
            $pfad = 'include/boxes/' . $box . '.php';
        } elseif (file_exists('include/boxes/' . $box . '.htm')) {
            $pfad = 'include/boxes/' . $box . '.htm';
        } elseif (file_exists('include/contents/selfbp/selfb/' . str_replace('self_', '', $box) . '.php')) {
            $pfad = 'include/contents/selfbp/selfb/' . str_replace('self_', '', $box) . '.php';
        } elseif (file_exists('include/contents/selfbp/selfb/' . str_replace('self_', '', $box) . '.htm')) {
            $pfad = 'include/contents/selfbp/selfb/' . str_replace('self_', '', $box) . '.htm';
        } else {
            return (false);
        }
        ob_start();
        include $pfad;
        $buffer = $this->escape_explode(ob_get_contents());
        ob_end_clean();
        return ($buffer);
    }

	/**
	* Baut das die Brotkruemelnavigation zusammen
	*
	* @param array $data Ein Arry, welches die zu verarbeitenden Daten enthält. Aus Gründen der Abwärtskompatibilität zu Versionen, wo die Brotkruemelnavigation als String übergeben wird, kein Type Hinting. Wenn alle Templates umgestellt sind kann das noch eingeführt werden
	* @param tpl $tpl Die Tpl-Klasse für die index.htm. Wird zum Abfragen der Gerstaltung benötigt
	* @return string Brotkrumennavigation
	* @autor finke <surf-finke@gmx.de>
	*/
	protected function buildBreadcrumb($data, tpl $tpl){
		if(is_array($data)){
			$breadcrumb = '';

			if($tpl->list_exists('hseparator')) $separator = $tpl->list_get('hseparator', array());
			else $separator = '&raquo;';
			if($tpl->list_exists('hlink')) $link = $tpl->list_get('hlink', array());
			else $link = '<a href="index.php?%3" %2>%1</a>';
			if($tpl->list_exists('hlast')) $last = $tpl->list_get('hlast', array());
			else $last = '<span %2>%1</span>';

			foreach($data as $v){
				if(empty($v)) continue;
				
				if(!empty($breadcrumb)){
					$breadcrumb .= $separator;
				}
				
				if(is_string($v)) $v = array('type'=>'last', 'text'=>$v);

				switch(strtolower($v['type'])){
				case 'link':
					$v1 = (empty($v['text'])?'':htmlentities($v['text']));
					$v2 = (empty($v['attr'])?'':$v['attr']);
					$v3 = (empty($v['href'])?'':rawurlencode($v['href']));

					$breadcrumb .= str_replace(array('%1', '%2', '%3'), array($v1, $v2, $v3), $link);
					break;
				case 'last':
					$v1 = (empty($v['text'])?'':htmlentities($v['text']));
					$v2 = (empty($v['attr'])?'':$v['attr']);

					$breadcrumb .= str_replace(array('%1','%2'),array($v1, $v2),$last);
					break;
				default:
					continue 2;
				}				
			}
			return $this->escape_explode($breadcrumb);
		}
		//Abwaerzkompatibilitaet
		return '<span id="icHmenu">' . $this->escape_explode($data) . '</span>';
	}
	
	/**
	* Baut ein Array zusammen, welches für buildBreadcrumb verwendet werden kann.
	*
	* Diese Funktion dient als Wrapper, um auch einfache Arrays für die Brotkruemelnavigation zu verwenden
	* Input Shema für zwei Einträge mit Link und ein EIntrag ohne Link: 
	* array([url1]=>[text1], [url2]=>[text2], [text3])
	* Output: 
	* array(array('type'=>'link', 'text'=>[text1], 'href'=>[url1]), array('type'=>'link', 'text'=>[text2], 'href'=>[url2]),
array('type'=>'last', 'text'=>[text3]))
	*
	* @param array $data Ein Arry, welches die zu verarbeitenden Daten enthält.
	* @return array für die Verwendung in buildBreadcrumb()
	* @autor finke <surf-finke@gmx.de>
	*/
	public static function breadcrumbFromSimple(array $data){
		$result = array();
		foreach($data as $k=>$v){
			if(!empty($k) && is_string($k)){
				$result[] = array('type'=>'link', 'text'=>$v, 'href'=>$k);
			}else{
				$result[] = array('type'=>'last', 'text'=>$v);
			}
		}
		return $result;
	}
}
