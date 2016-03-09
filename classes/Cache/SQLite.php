<?php

namespace Cache;

class SQLite implements Cache {
    
    const DATABASE = '/tmp/sqlite_cache_benchmark.db';
    const TABLE_DROP_SQL = 'DROP TABLE cache';
    const TABLE_CREATE_SQL =  'CREATE TABLE cache '
                            . '(`key` VARCHAR(255) NOT NULL, '
                            . '`value` TEXT DEFAULT NULL, '
                            . '`expires` INT DEFAULT NULL, '
                            . 'PRIMARY KEY(`key`))';
    
    private $_db;
    
    public static function init() {
        if (!class_exists('SQLite3')) {
            throw new \Exception('SQLite3 is not installed');
        }
        $db = new \SQLite3(self::DATABASE);
        if (!$db) {
            throw new \Exception('An error on connect');
        }
        $db->exec(self::TABLE_DROP_SQL);
        $db->exec(self::TABLE_CREATE_SQL);
        $db->close();
    }
    
    public function __construct() {
        $this->_db = new \SQLite3(self::DATABASE);
        if (!$this->_db) {
            throw new \Exception('An error on connect');
        }
        $this->_db->busyTimeout(2000);
    }
    
    public function __destruct() {
        $this->_db->close();
    }
    
    
    public function remove($key) {
        return $this->_db->exec(sprintf(
                                    'DELETE FROM `cache` WHERE `key` = "%s"',
                                    $this->_db->escapeString($key)
        ));
    }
    
    public function getString($key) {
        $result = $this->_db->query(sprintf(
                    'SELECT `value`, `expires` FROM `cache` WHERE `key` = "%s"',
                    $this->_db->escapeString($key)
        ));
        if ($result === false) {
            throw new \Exception('A bad result');
        }
        $row = $result->fetchArray(SQLITE3_NUM);
        if ($row[1] && $row[1] < time()) {
            $this->_db->exec(sprintf(
                                    'DELETE FROM `cache` WHERE `key` = "%s"',
                                    $this->_db->escapeString($key)
            ));
            return false;
        }
        return $row[0];
    }

    public function setString($key, $string, $expiration = 0) {
        return $this->_db->exec(sprintf(
                                'REPLACE INTO `cache` VALUES ("%s", "%s", %s)',
                                $this->_db->escapeString($key),
                                $this->_db->escapeString($string),
                                $expiration > 0 ? time() + $expiration : 'NULL'
        ));
    }

}
