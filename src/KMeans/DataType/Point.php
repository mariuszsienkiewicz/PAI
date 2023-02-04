<?php

namespace Mariuszsienkiewicz\PAI\KMeans\DataType;

class Point
{
    public array $coordinates;

    public function __construct($coordinates)
    {
        $this->coordinates = $coordinates;
    }

    /**
     * Gets the closest cluster
     * @param array<Cluster> $clusters
     * @return int
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
     * Calculates the euclidean distance
     * @param Point $point
     * @return int
     */
    public function calculateDistance(Point $point): int
    {
        $coordinates = [];
        foreach ($point->coordinates as $axis => $coordinate) {
            $coordinates[] = pow($this->coordinates[$axis] - $coordinate, 2);
        }

        return array_sum($coordinates);
    }
}