<?php

/**
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL)
 * @copyright (C) 2000-2012 ilch.de
 * @version $Id$
 */
defined('main') or die('no direct access');


/**
 * Tpc (thrid party connector)
 *
 *
 * @author finke <Surf-finke@gmx.de>
 * @copyright Copyright (c) 2013
 */
class Tpc{
    private $_self = NULL;

    private $_connectors = array();

    /**
    * Eine Reihe von Konstanten, welche verschiedene Events beschreiben
    * Weitere follgen
    */
    const EVENT_CREATE_USER     = 0x01;
    const EVENT_RENAME_USER     = 0x02;
    const EVENT_CHANGE_PASSWORD = 0x04;
    const EVENT_RERANK_USER     = 0x08;
    const EVENT_DELETE_USER     = 0x10;


    static function get(){
        if($this->self === null || !($_self instanceof 'Tcp')) {
            $this->self = new Tcp();
        }
        return $_self
    }

    private __construct(){
        if($allgAr['tcp'] != 1) {
            return;
        }
        
        $result = db_query("SELECT `class_name` from `prefix_tpc_connectors` where `activ` = 1");
        $events = array();
        while($cName = mysql_fetch_row($result)){
            try{
                $tmp = new ReflectionClass('\\Tcp\\'.$cName)
                $tmp = $tmp->newInstance();
            }catch(InvalidArgumentException $e){
                continue;
            }

            if(!is_subclass_of($tmp, '\\Tcp\\AConnector')) {
                continue;
            }

            $events = $tmp->supportetEvents();
            if(is_int($events)) {
                $events = array($events);
            }
            if(!is_array($events)) {
                continue;
            }

            $this->$_connectors = array($tmp, $events);
        }
    }

    public function triggerEvent($event, array $options){    
        if($allgAr['tcp'] != 1 || !is_int($event)) {
            return;
        }

        foreach($_connectors as $connector){
            if($connector[1] & $event){
                $connector[0]->triggerEvent($event, $options);
            }
        }
    }
}
