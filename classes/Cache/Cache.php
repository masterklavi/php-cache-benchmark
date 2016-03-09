<?php

namespace Cache;

interface Cache {
    
    public static function init();
    
    public function __construct();
    public function __destruct();
    
    public function remove($key);
    
    public function getString($key);
    public function setString($key, $string, $expiration = 0);
    
}
