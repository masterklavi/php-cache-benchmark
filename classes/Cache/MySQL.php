<?php

namespace Cache;

class MySQL implements Cache {
    
    const HOST = 'localhost';
    const USER = 'root';
    const PASSWORD = 'password';
    const DATABASE = 'mysql_cache_benchmark';
    const TABLE_DROP_SQL = 'DROP TABLE cache';
    const TABLE_CREATE_SQL =  'CREATE TABLE cache '
                            . '(`key` VARCHAR(255) NOT NULL, '
                            . '`value` VARCHAR(6000) DEFAULT NULL, '
                            . '`expires` INT DEFAULT NULL, '
                            . 'PRIMARY KEY(`key`)) ENGINE=MEMORY';
    
    private $_db;
    
    public static function init() {
        if (!class_exists('mysqli')) {
            throw new \Exception('mysqli is not installed');
        }
        $db = new \mysqli(self::HOST, self::USER, self::PASSWORD);
        if ($db->connect_error) {
            throw new \Exception('An error on connect');
        }
        $db->set_charset('utf-8');
        $db->query('CREATE DATABASE '.self::DATABASE);
        $db->query(self::TABLE_DROP_SQL);
        $db->query(self::TABLE_CREATE_SQL);
        $db->close();
    }
    
    public function __construct() {
        $this->_db = new \mysqli(self::HOST, self::USER, self::PASSWORD);
        if ($this->_db->connect_error) {
            throw new \Exception('An error on connect');
        }
        $this->_db->set_charset('utf-8');
        $this->_db->select_db(self::DATABASE);
    }
    
    public function __destruct() {
        $this->_db->close();
    }
    
    
    public function remove($key) {
        return $this->_db->query(sprintf(
                                    'DELETE FROM `cache` WHERE `key` = "%s"',
                                    $this->_db->real_escape_string($key)
        ));
    }
    
    public function getString($key) {
        $result = $this->_db->query(sprintf(
                    'SELECT `value`, `expires` FROM `cache` WHERE `key` = "%s"',
                    $this->_db->real_escape_string($key)
        ));
        if ($result === false) {
            throw new \Exception('A bad result');
        }
        if ($result->num_rows === 0) {
            $result->close();
            return null;
        }
        $row = $result->fetch_row();
        $result->close();
        if ($row[1] && $row[1] < time()) {
            $this->_db->query(sprintf(
                                    'DELETE FROM `cache` WHERE `key` = "%s"',
                                    $this->_db->real_escape_string($key)
            ));
            return false;
        }
        return $row[0];
    }

    public function setString($key, $string, $expiration = 0) {
        return $this->_db->query(sprintf(
                                'REPLACE INTO `cache` VALUES ("%s", "%s", %s)',
                                $this->_db->real_escape_string($key),
                                $this->_db->real_escape_string($string),
                                $expiration > 0 ? time() + $expiration : 'NULL'
        ));
    }

}
