<?php

namespace Mariuszsienkiewicz\PAI\KMeans\DataType;

use Mariuszsienkiewicz\PAI\Math\Distance;

class Point
{
    public array $coordinates;

    public function __construct($coordinates)
    {
        $this->coordinates = $coordinates;
    }

    /**
     * Gets the closest cluster.
     *
     * @param array<Cluster> $clusters
     */
    public function getClosestCluster(array $clusters): int
    {
        $min = INF;
        $minCluster = null;

        foreach ($clusters as $clusterIndex => $cluster) {
            $distance = $this->calculateDistance($cluster->centroid);
            if ($distance < $min) {
                $min = $distance;
                $minCluster = $clusterIndex;
            }
        }

        return $minCluster;
    }

    /**
     * Calculates the euclidean distance.
     */
    public function calculateDistance(Point $point): int
    {
        return Distance::euclidianDistance($this->coordinates, $point->coordinates);
    }
}
