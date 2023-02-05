<?php

require __DIR__ . '/../../vendor/autoload.php';

use Mariuszsienkiewicz\PAI\KNN\KNN;

$samples = [[10, 12], [12, 11], [43, 21], [31, 12], [95, 85], [96, 75], [106, 65], [108, 98]];
$labels = ['a', 'a', 'b', 'b', 'c', 'c', 'c', 'c'];

$knn = new KNN(3, $samples, $labels);

echo $knn->predict([103, 65]);
