<?php

require __DIR__ . '/../../vendor/autoload.php';

use Mariuszsienkiewicz\PAI\KMeans\KMeans;
use Mariuszsienkiewicz\PAI\KMeans\Subscriber\ImageSubscriber;

$minVal = 1;
$maxVal = 300;

for ($i = 0; $i < 2000; $i++) {
    $points[] = [
        rand($minVal, $maxVal),
        rand($minVal, $maxVal)
    ];
}

$imageSubscriber = new ImageSubscriber('./out/', 1000, 1000, $minVal, $maxVal);
$imageSubscriber->setPointSize(8);
$imageSubscriber->setCentroidSize(24);

$kmeans = new KMeans(4);
$kmeans->attachSubscriber($imageSubscriber);

$clusters = $kmeans->cluster($points);
