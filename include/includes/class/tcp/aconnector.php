<?php

/**
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL)
 * @copyright (C) 2000-2012 ilch.de
 * @version $Id$
 */
defined('main') or die('no direct access');

namespace Tcp{

    /**
     * AConnector
     * 
     *
     * @author finke <Surf-finke@gmx.de>
     * @copyright Copyright (c) 2013
     */
    abstract class AConnector{
        private $_supportedEvents = 0x0;
        
        public __construct($events){   
            if(is_int($events)){
                $_supportedEvents = $events;
            }
        }
        
        public function test(&$log);
        
        public function getSupportetEvents(){
            return $this->_supportedEvents;
        };
        
        public function triggerEvent($event, array $options);
    }
}