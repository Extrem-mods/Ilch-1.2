<?php
defined('main') or die('no direct access'){ 
}

require_once(dirname(__FILE__)."/database.php"){ 
}

/**
 * Ilch database Template.
 *
 * Use this class instead of old database functions.
 * Use select_* instead of query_*.
 */
 
class Ilch_Database_postgresql extends Ilch_Database_database{

	/**
     * get the name of the current Database driver
     *
     * @return string current driver
     */

	public function getDriver(){
		return get_class($this){ 
}	
	}
     /**
     * Set the table prefix.
     *
     * @param string $pref
     */
	public function setPrefix($pref){
	}
	
    /**
     * Get the orginal Database Objekt (MySQLi, PDO).
     *
     * @return Ojekt
     */
	public function getLink(){
	}
	
	/**
     * Connect to database.
     *
     * @param string $host
     * @param string $name
     * @param string $password
     */
	public function connect($host, $name, $password){ 
	}
	
	  /**
     * @param string $sql
     * @return mixed mysql result
     */
    public function query($sql){ 
	}
	
	/**
     * @param array $fields
     * @param string $table
     * @param array $where
     */
	public function update($fields, $table, $where = null){ 
	}
	
	/**
     * @param array $fields
     * @param string $table
     */
	public function insert($fields, $table){ 
	}
	
	/**
     * @param string $table
     * @param array $where
     */
	public function delete($table, $where = null){ 
	}
	
	/**
     * @param string $value
     * @return string
     */
	public function escape($value){ 
	} 
}