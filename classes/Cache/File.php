<?php

namespace Cache;

class File implements Cache {
    
    const PATH = '/tmp/file_cache_benchmark/';

    public static function init() {
        if (!is_dir(self::PATH)) {
            mkdir(self::PATH);
        }
    }
    
    public function __construct() {}
    public function __destruct() {}
    
    public function remove($key) {
        $filename = self::PATH.$key;
        if (!file_exists($filename)) {
            return false;
        }
        return unlink($filename);
    }
    
    public function getString($key) {
        $filename = self::PATH.$key;
        if (!file_exists($filename)) {
            return null;
        }
        $content = file_get_contents($filename);
        $expires = (int)substr($content, 0, 15);
        if ($expires > 0 && $expires < time()) {
            unlink($filename);
            return null;
        }
        return substr($content, 15);
    }

    public function setString($key, $string, $expiration = 0) {
        file_put_contents(
                self::PATH.$key, 
                sprintf(
                        '%-15d%s', 
                        $expiration > 0 ? time() + $expiration : $expiration, 
                        $string
                )
        );
    }

}
