<?php

namespace Cache;

class Redis implements Cache {
    
    const HOST = '/tmp/redis.sock';
    const PORT = 0;
    
    private $_object;

    public static function init() {
        if (!class_exists('Redis')) {
            throw new \Exception('Redis is not installed');
        }
        $object = new \Redis();
        if (!$object->connect(self::HOST, self::PORT)) {
            throw new \Exception('An error on connect');
        }
        $object->close();
    }
    
    public function __construct() {
        $this->_object = new \Redis();
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
        if ($expiration > 0) {
            return $this->_object->setEx($key, $expiration, $string);
        } else {
            return $this->_object->set($key, $string);
        }
    }

}
