<?php

namespace Mariuszsienkiewicz\PAI\KMeans\Subscriber;

use Mariuszsienkiewicz\PAI\KMeans\DataType\Point;

interface SubscriberInterface
{
    /**
     * Fired when sample data is available.
     *
     * @param array<Point> $points
     */
    public function sampleDataEvent(array $points): void;

    /**
     * Fired after random points assigment.
     *
     * @param array<\Mariuszsienkiewicz\PAI\KMeans\DataType\Cluster> $clusters
     */
    public function randomPointsAssignmentEvent(array $clusters): void;

    /**
     * Fired after creation of new centroids.
     *
     * @param array<\Mariuszsienkiewicz\PAI\KMeans\DataType\Cluster> $clusters
     */
    public function centroidsCreationEvent(array $clusters, int $iteration): void;

    /**
     * Fired after reassigning points to new clusters.
     *
     * @param array<\Mariuszsienkiewicz\PAI\KMeans\DataType\Cluster> $clusters
     */
    public function newClustersEvent(array $clusters, int $iteration): void;
}
