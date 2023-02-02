<?php

namespace Mariuszsienkiewicz\KMeans\Subscriber;
use Mariuszsienkiewicz\KMeans\DataType\Point;

interface SubscriberInterface
{
    /**
     * Fired when sample data is available
     * @param array<Point> $points
     * @return void
     */
    public function sampleDataEvent(array $points): void;

    /**
     * Fired after random points assigment.
     * @param array<\Mariuszsienkiewicz\KMeans\DataType\Cluster> $clusters
     * @return void
     */
    public function randomPointsAssignmentEvent(array $clusters): void;

    /**
     * Fired after creation of new centroids
     * @param array<\Mariuszsienkiewicz\KMeans\DataType\Cluster> $clusters
     * @param int $iteration
     * @return void
     */
    public function centroidsCreationEvent(array $clusters, int $iteration): void;

    /**
     * Fired after reassigning points to new clusters
     * @param array<\Mariuszsienkiewicz\KMeans\DataType\Cluster> $clusters
     * @param int $iteration
     * @return void
     */
    public function newClustersEvent(array $clusters, int $iteration): void;
}