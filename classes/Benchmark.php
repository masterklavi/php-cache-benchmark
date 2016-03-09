<?php

/**
 * Benchmarking class
 * 
 * It runs every methods in all caches and measures an average rps.
 * 
 */
class Benchmark {
    
    const VERSION = 2;
    
    const ITERATIONS = 1000;
    const CONCURRENCY = [1, 5, 10, 20, 50, 100, 150];
    const TIME = 10;
    
    const METHODS = [
        'string',
        'stringExpires',
        'longString',
        'longStringExpires',
    ];
    
    public static function run(array $classes) {
        foreach ($classes as $class) {
            $class::init();
        }
        $result = [];
        foreach ($classes as $class) {
            $cache_result = [];
            foreach (self::CONCURRENCY as $concurrency) {
                print $class.', concurrency='.$concurrency.PHP_EOL;
                $cache_result[$concurrency] = 
                                    self::_benchmarkCache($class, $concurrency);
            }
            $result[$class] = $cache_result;
        }
        return $result;
    }

    private static function _benchmarkCache($class, $concurrency) {
        $benchmark = new Benchmark($class, $concurrency);
        $result = [];
        foreach (self::METHODS as $method) {
            $result[$method] = $benchmark->$method();
        }
        return $result;
    }
    
    public static function storeResult($name, $result) {
        file_put_contents($name, json_encode(
                [
                    'VERSION' => self::VERSION, 
                    'CONCURRENCY' => self::CONCURRENCY,
                    'ITERATIONS' => self::ITERATIONS,
                    'TIME' => self::TIME,
                    'RESULT' => $result, 
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ));
        print 'A result saved as '.$name.PHP_EOL;
    }


    private $_class;
    private $_concurrency;
    
    public function __construct($class, $concurrency) {
        $this->_class = $class;
        $this->_concurrency = $concurrency;
    }
    
    public function string($string = null) {
        
        $iterations = 0;
        $results = [
            'set' => 0,
            'get' => 0,
            'remove' => 0,
        ];
        if (is_null($string)) {
            $string = str_repeat('0', 100);
        }
        $timer = microtime(true);
        
        do {
            for ($i = 0; $i < $this->_concurrency; $i++) {
                $pid = pcntl_fork();
                if ($pid == -1) {
                    throw new Exception('fork error');
                } elseif (!$pid) {
                    $key = $i*self::ITERATIONS;
                    $cache = new $this->_class;
                    for ($j = 0; $j < self::ITERATIONS; $j++) {
                        $cache->setString($i, $string);
                        $key++;
                    }
                    unset($cache);
                    exit;
                }
            }
            $mark = microtime(true);
            while (pcntl_waitpid(0, $status) != -1) { 
                $status = pcntl_wexitstatus($status);
            }
            $results['set'] += microtime(true) - $mark;
            
            
            for ($i = 0; $i < $this->_concurrency; $i++) {
                $pid = pcntl_fork();
                if ($pid == -1) {
                    throw new Exception('fork error');
                } elseif (!$pid) {
                    $key = $i*self::ITERATIONS;
                    $cache = new $this->_class;
                    for ($j = 0; $j < self::ITERATIONS; $j++) {
                        $cache->getString($i, $string);
                        $key++;
                    }
                    unset($cache);
                    exit;
                }
            }
            $mark = microtime(true);
            while (pcntl_waitpid(0, $status) != -1) { 
                $status = pcntl_wexitstatus($status);
            }
            $results['get'] += microtime(true) - $mark;
            
            
            for ($i = 0; $i < $this->_concurrency; $i++) {
                $pid = pcntl_fork();
                if ($pid == -1) {
                    throw new Exception('fork error');
                } elseif (!$pid) {
                    $key = $i*self::ITERATIONS;
                    $cache = new $this->_class;
                    for ($j = 0; $j < self::ITERATIONS; $j++) {
                        $cache->remove($i, $string);
                        $key++;
                    }
                    unset($cache);
                    exit;
                }
            }
            $mark = microtime(true);
            while (pcntl_waitpid(0, $status) != -1) { 
                $status = pcntl_wexitstatus($status);
            }
            $results['remove'] += microtime(true) - $mark;
            
            $iterations += self::ITERATIONS*$this->_concurrency;
            
        } while(microtime(true) - $timer < self::TIME);
        
        foreach ($results as &$result) {
            $result = $iterations/$result;
        }
        
        return $results;
    }
    
    public function stringExpires($string = null) {
        
        $iterations = 0;
        $results = [
            'set' => 0,
            'get_notexpired' => 0,
            'get_expired' => 0,
        ];
        if (is_null($string)) {
            $string = str_repeat('0', 100);
        }
        $timer = microtime(true);
        
        do {
            for ($i = 0; $i < $this->_concurrency; $i++) {
                $pid = pcntl_fork();
                if ($pid == -1) {
                    throw new Exception('fork error');
                } elseif (!$pid) {
                    $key = $i*self::ITERATIONS;
                    $cache = new $this->_class;
                    for ($j = 0; $j < self::ITERATIONS; $j++) {
                        $cache->setString($i, $string, 1);
                        $key++;
                    }
                    unset($cache);
                    exit;
                }
            }
            $mark = microtime(true);
            while (pcntl_waitpid(0, $status) != -1) { 
                $status = pcntl_wexitstatus($status);
            }
            $results['set'] += microtime(true) - $mark;
            
            
            for ($i = 0; $i < $this->_concurrency; $i++) {
                $pid = pcntl_fork();
                if ($pid == -1) {
                    throw new Exception('fork error');
                } elseif (!$pid) {
                    $key = $i*self::ITERATIONS;
                    $cache = new $this->_class;
                    for ($j = 0; $j < self::ITERATIONS; $j++) {
                        $cache->getString($i, $string);
                        $key++;
                    }
                    unset($cache);
                    exit;
                }
            }
            $mark = microtime(true);
            while (pcntl_waitpid(0, $status) != -1) { 
                $status = pcntl_wexitstatus($status);
            }
            $results['get_notexpired'] += microtime(true) - $mark;
            
            sleep(1);
            
            for ($i = 0; $i < $this->_concurrency; $i++) {
                $pid = pcntl_fork();
                if ($pid == -1) {
                    throw new Exception('fork error');
                } elseif (!$pid) {
                    $key = $i*self::ITERATIONS;
                    $cache = new $this->_class;
                    for ($j = 0; $j < self::ITERATIONS; $j++) {
                        $cache->getString($i, $string);
                        $key++;
                    }
                    unset($cache);
                    exit;
                }
            }
            $mark = microtime(true);
            while (pcntl_waitpid(0, $status) != -1) { 
                $status = pcntl_wexitstatus($status);
            }
            $results['get_expired'] += microtime(true) - $mark;
            
            $iterations += self::ITERATIONS*$this->_concurrency;
            
        } while(microtime(true) - $timer < self::TIME);
        
        $cache = new $this->_class;
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $cache->remove($i);
        }
        unset($cache);
        
        foreach ($results as &$result) {
            $result = $iterations/$result;
        }
        
        return $results;
    }
    
    public function longString() {
        return $this->string(str_repeat('0', 6000));
    }
    
    public function longStringExpires() {
        return $this->stringExpires(str_repeat('0', 6000));
    }
}