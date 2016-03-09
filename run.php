<?php

/**
 * PHP Cache Benchmark
 * 
 * Calculation and Comparison of RPS for some popular User Caches in PHP
 * 
 * @version 0.1
 * @author  Master Klavi <masterklavi@gmail.com>
 */

// includes
require 'classes/Cache/Cache.php';

require 'classes/Cache/File.php';
require 'classes/Cache/MySQL.php';
require 'classes/Cache/SQLite.php';
require 'classes/Cache/APC.php';
require 'classes/Cache/Memcache.php';
require 'classes/Cache/Memcached.php';
require 'classes/Cache/Redis.php';
require 'classes/Cache/SharedMemory.php';

require 'classes/Benchmark.php';

// benchmarking
$result = Benchmark::run([
    'Cache\File',
    'Cache\MySQL',
    'Cache\SQLite',
    'Cache\APC',
    'Cache\Memcache',
    'Cache\Memcached',
    'Cache\Redis',
    'Cache\SharedMemory',
]);

// store result as json
Benchmark::storeResult('result.json', $result);
