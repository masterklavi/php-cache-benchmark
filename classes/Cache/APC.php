<?php

namespace Cache;

class APC implements Cache {

    public static function init() {
        if (!function_exists('apc_store')) {
            throw new \Exception('APC is not installed');
        }
    }

    public function __construct() {}
    public function __destruct() {}
    
    
    public function remove($key) {
        return apcu_delete((string)$key);
    }
    
    public function getString($key) {
        return apcu_fetch((string)$key);
    }

    public function setString($key, $string, $expiration = 0) {
        return apcu_store((string)$key, $string, $expiration);
    }
}
