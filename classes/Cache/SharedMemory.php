<?php

namespace Cache;

class SharedMemory implements Cache {

    const VARIABLES_COUNT = 11000;
    const VARIABLE_SIZE = 6000;
    
    private $_id;

    public static function init() {
        $id = shmop_open(
                        0xff7, 'c', 0644, 
                        self::VARIABLE_SIZE*self::VARIABLES_COUNT
        );
        if (!$id) {
            throw new Exception('An error on shmop_open()');
        }
        shmop_close($id);
    }
    
    public function __construct() {
        $this->_id = shmop_open(
                        0xff7, 'c', 0644, 
                        self::VARIABLE_SIZE*self::VARIABLES_COUNT
        );
        if (!$this->_id) {
            throw new Exception('An error on shmop_open()');
        }
    }
    
    public function __destruct() {
        shmop_close($this->_id);
    }    
    
    public function remove($key) {
        return shmop_write(
                        $this->_id, 
                        'shm$', 
                        intval($key)*self::VARIABLE_SIZE
        );
    }
    
    public function getString($key) {
        $content = shmop_read(
                            $this->_id, 
                            intval($key)*self::VARIABLE_SIZE,
                            self::VARIABLE_SIZE
        );
        if (substr($content, 0, 4) !== 'shm^') {
            return null;
        }
        $expires = (int)substr($content, 4, 15);
        if ($expires > 0 && $expires < time()) {
            return null;
        }
        $length = (int)substr($content, 19, 6);
        return substr($content, 25, $length);
    }

    public function setString($key, $string, $expiration = 0) {
        return shmop_write($this->_id, sprintf(
                'shm^%-15d%-6d%s', 
                $expiration > 0 ? time() + $expiration : $expiration, 
                strlen($string),
                $string
        ), intval($key)*self::VARIABLE_SIZE);
    }

}
