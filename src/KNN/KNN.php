<?php

namespace Mariuszsienkiewicz\PAI\KNN;

use Mariuszsienkiewicz\PAI\Math\Distance;
use Mariuszsienkiewicz\PAI\Math\Normalizer;

class KNN
{
    /**
     * Number of nearest neighbours to consider
     * @var int 
     */
    private int $k;

    /**
     * Samples used in the process
     * @var array<array>
     */
    private array $samples;

    /**
     * Labels for each sample
     * @var array<array>
     */
    private array $labels;

    /**
     * Array containing counted labels
     * @var array<string, integer>
     */
    private array $labelsCount;

    public function __construct(int $k, array $samples, array $labels)
    {
        $this->k = $k;
        $this->samples = $samples;
        $this->labels = $labels;
        $this->labelsCount = array_count_values($this->labels);
    }

    /**
     * Predict the the label of given sample
     * @param array $sample
     * @return null|string
     */
    public function predict(array $sample)
    {
        $distances = $this->getDistancesToNeighbours($sample);
        $nearestNeighbours = array_slice($distances, 0, $this->k, true);
        return $this->getNearestNeighbourLabel($nearestNeighbours);
    }

    /**
     * Calculates the distance between sample to predict and all other samples 
     * @param array $sample
     * @return array containing all neighbor distances sorted in ascending order
     */
    private function getDistancesToNeighbours(array $sample)
    {
        $normalizer = new Normalizer($this->samples);
        $normalizedSamples = $normalizer->minMax();

        $distances = [];
        foreach ($normalizedSamples as $key => $neighbor) {
            $distances[$key] = Distance::euclidianDistance($sample, $neighbor);
        }

        asort($distances);

        return $distances;
    }

    /**
     * Get nearest neighbour group label
     * @param array $neighbours
     * @return null|string
     */
    private function getNearestNeighbourLabel($neighbours)
    {
        $largestNeighboursGroup = null;
        $largestNeighboursGroupCount = 0;
        foreach ($neighbours as $key => $value) {
            /** @var string $label */
            $label = $this->labels[$key];
            $labelCount = $this->labelsCount[$label];
            if ($this->labelsCount[$label] > $largestNeighboursGroupCount) {
                $largestNeighboursGroup = $label;
                $largestNeighboursGroupCount = $labelCount;
            }
        }

        return $largestNeighboursGroup;
    }
}