<?php

defined('main') or die('no direct access');

/**
 * Ilch database Template.
 *
 * Use this class instead of old database functions.
 * Use select_* instead of query_*.
 */
 
abstract class Ilch_Database_Database{

	/**
     * get the name of the current Database driver
     *
     * @return string current driver
     */

	public function getDriver(){
		return get_class($this);	
	}
     /**
     * Set the table prefix.
     *
     * @param string $pref
     */
	abstract public function setPrefix($pref);
	
    /**
     * Get the orginal Database Objekt (MySQLi, PDO).
     *
     * @return Ojekt
     */
	abstract public function getLink();
	
	/**
     * Connect to database.
     *
     * @param string $host
     * @param string $name
     * @param string $password
     */
	abstract public function connect($host, $name, $password);
	
	  /**
     * @param string $sql
     * @return mixed mysql result
     */
    abstract public function query($sql);
	
	/**
     * @param array $fields
     * @param string $table
     * @param array $where
     */
	abstract public function update($fields, $table, $where = null);
	
	/**
     * @param array $fields
     * @param string $table
     */
	abstract public function insert($fields, $table);
	
	/**
     * @param string $table
     * @param array $where
     */
	abstract public function delete($table, $where = null);
	
	/**
     * @param string $value
     * @return string
     */
	abstract public function escape($value); 
 }