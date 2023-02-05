<?php

namespace Mariuszsienkiewicz\PAI\Math;

class Distance
{
    public static function euclidianDistance(array $x, array $y)
    {
        $distances = [];
        foreach ($x as $key => $value) {
            $distances[$key] = pow($value - $y[$key], 2);
        }

        $sumDistances = array_sum($distances);

        return sqrt($sumDistances);
    }
}
