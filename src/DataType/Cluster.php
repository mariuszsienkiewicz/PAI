<?php

namespace Mariuszsienkiewicz\KMeans\DataType;

/**
 * Summary of Cluster
 */
class Cluster
{
    /**
     * Array containing all the points in the cluster
     * @var array<Point>
     */
    private array $points = [];

    /**
     * Point where the centroid lies
     * @var Point
     */
    public Point $centroid;

    /**
     * Computes centroid of the cluster
     * @return void
     */
    public function computeCentroid(): void
    {
        $count = $this->count();
        if ($count == 0) {
            return;
        }

        $centroidCoordinations = [];

        foreach ($this->points as $point) {
            foreach ($point->coordinates as $axis => $coordinate) {
                if (false == array_key_exists($axis, $centroidCoordinations)) {
                    $centroidCoordinations[$axis] = 0;
                }

                $centroidCoordinations[$axis] += $coordinate;
            }
        }

        foreach ($centroidCoordinations as $axis => $coordinate) {
            $centroidCoordinations[$axis] = $coordinate / $count;
        }

        $this->centroid = new Point($centroidCoordinations);
    }


    public function assign(Point $point): void
    {
        $this->points[] = $point;
    }

    public function detach(Point $point): void
    {
        if (($key = array_search($point, $this->points)) !== false) {
            unset($this->points[$key]);
        }
    }

    /**
     * @return array<Point>
     */
    public function getAll(): array
    {
        return $this->points;
    }

    public function count(): int
    {
        return count($this->points);
    }
}