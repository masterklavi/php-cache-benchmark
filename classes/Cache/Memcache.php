<?php

namespace Cache;

class Memcache implements Cache {
    
    const HOST = 'unix:///tmp/memcached.sock';
    const PORT = 0;
    
    private $_object;

    public static function init() {
        if (!class_exists('Memcache')) {
            throw new \Exception('Memcache is not installed');
        }
        $object = new \Memcache();
        if (!$object->connect(self::HOST, self::PORT)) {
            throw new \Exception('An error on connect');
        }
        $object->close();
    }
    
    public function __construct() {
        $this->_object = new \Memcache();
        if (!$this->_object->connect(self::HOST, self::PORT)) {
            throw new \Exception('An error on connect');
        }
    }
    
    public function __destruct() {
        $this->_object->close();
    }
    
    
    public function remove($key) {
        return $this->_object->delete($key);
    }
    
    public function getString($key) {
        return $this->_object->get($key);
    }

    public function setString($key, $string, $expiration = 0) {
        return $this->_object->set($key, $string, 0, $expiration);
    }

}
