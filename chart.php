<?php

/**
 * Charts for PHP Cache Benchmark
 * 
 * It uses the pChart library <http://www.pchart.net/>
 * 
 * @version 0.1
 * @author  Master Klavi <masterklavi@gmail.com>
 */

// includes
require 'pChart/class/pData.class.php';
require 'pChart/class/pDraw.class.php';
require 'pChart/class/pImage.class.php';

// get a result
$result = json_decode(file_get_contents('result.json'), true)['RESULT'];
if (!$result) {
    throw new Exception('bad result');
}
// get object fields
$concurrency = array_keys(current($result));
$methods = array_keys(current(current($result)));

// font params
$font = [
    'FontName' => 'pChart/fonts/Forgotte.ttf',
    'FontSize' => 14
];

// create/clear directory of charts
if (!is_dir('charts')) {
    mkdir('charts');
} else {
    foreach (glob('charts/*.png') as $chart) {
        unlink($chart);
    }
}

// create a chart for every method
foreach ($methods as $method) {
    
    // get available params for this method
    reset($result);
    $params = array_keys(current(current($result))[$method]);
    
    // create a chart for every method+param
    foreach ($params as $param) {
        
        $data = new pData();
        $data->addPoints($concurrency, 'Concurrency');
        $data->setAbscissa('Concurrency');
        $data->setAbscissaName('Concurrency');
        $data->setAxisName(0, 'kRPS');

        // add points of rps of every class
        foreach ($result as $class => $class_result) {
            $points = [];
            foreach ($concurrency as $c) {
                $points[] = $class_result[$c][$method][$param]/1000;
            }
            $data->addPoints($points, $class);
        }

        // create and save a chart
        $myPicture = new pImage(1500, 800, $data);
        $myPicture->setGraphArea(80, 50, 1300, 750);
        $myPicture->setFontProperties($font);
        $myPicture->drawScale();
        $myPicture->drawSplineChart();
        $myPicture->drawLegend(1300, 100, ['Style' => LEGEND_NOBORDER, 'Alpha' => 100]);
        $myPicture->Render('charts/'.$method.'.'.$param.'.png');
    }
}
