<?php

namespace Cache;

class Memcached implements Cache {
    
    const HOST = '/tmp/memcached.sock';
    const PORT = 0;
    
    private $_object;

    public static function init() {
        if (!class_exists('Memcached')) {
            throw new \Exception('Memcached is not installed');
        }
        $object = new \Memcached();
        if (!$object->addServer(self::HOST, self::PORT)) {
            throw new \Exception('An error on connect');
        }
        $object->quit();
    }
    
    public function __construct() {
        $this->_object = new \Memcached();
        if (!$this->_object->addServer(self::HOST, self::PORT)) {
            throw new \Exception('An error on connect');
        }
    }
    
    public function __destruct() {
        $this->_object->quit();
    }
    
    
    public function remove($key) {
        return $this->_object->delete($key);
    }
    
    public function getString($key) {
        return $this->_object->get($key);
    }

    public function setString($key, $string, $expiration = 0) {
        return $this->_object->set($key, $string, $expiration);
    }

}
