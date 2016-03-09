# PHP Cache Benchmark
Calculation and Comparison of RPS for some popular User Caches in PHP.

## List of User Caches
* File
* MySQL
* SQLite
* Shared Memory
* APC
* Memcache
* Memcached
* Redis (phpredis)

## Run
To Benchmarking:

    php run.php

To Charting the results:

    php chart.php


## Results
The last result is stored in [result.json](https://github.com/masterklavi/php-cache-benchmark/blob/master/result.json)

## Charts
The Charts of the last result is stored in [charts/](https://github.com/masterklavi/php-cache-benchmark/tree/master/charts) folder. All the charts was created by the pChart library <http://www.pchart.net/>
