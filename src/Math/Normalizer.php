<?php

namespace Mariuszsienkiewicz\PAI\Math;

class Normalizer
{
    private $samples;

    public function __construct($samples)
    {
        $this->samples = $samples;
    }

    public function minMax()
    {
        [$min, $max] = $this->getMinMax();

        $normalizedSamples = [];
        foreach ($this->samples as $key => $sample) {
            $normalizedSample = [];
            foreach ($sample as $atttributeKey => $atttributeValue) {
                $normalizedSample[$atttributeKey] = ($atttributeValue - $min[$atttributeKey]) / ($max[$atttributeKey] - $min[$atttributeKey]);
            }
            $normalizedSamples[$key] = $normalizedSample;
        }

        return $normalizedSamples;
    }

    private function getMinMax()
    {
        $attributesCount = count($this->samples[array_key_first($this->samples)]);
        $min = [];
        $max = [];

        for ($i = 0; $i < $attributesCount; $i++) {
            $values = array_column($this->samples, $i);
            $min[$i] = min($values);
            $max[$i] = max($values);
        }

        return [$min, $max];
    }
}