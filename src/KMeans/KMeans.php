<?php

namespace Mariuszsienkiewicz\PAI\KMeans;

use Mariuszsienkiewicz\PAI\KMeans\DataType\Cluster;
use Mariuszsienkiewicz\PAI\KMeans\DataType\Point;
use Mariuszsienkiewicz\PAI\KMeans\Subscriber\SubscriberInterface;

class KMeans
{
    /**
     * 2D points that will be grouped.
     *
     * @var array<Point>
     */
    private array $points = [];

    /**
     * Number of clusters that will be created with points.
     */
    private readonly int $clusterCount;

    /**
     * The limit of operations that the algorithm can perform.
     */
    private int $iterationLimit;

    /**
     * Array containing Cluster objects.
     *
     * @var array<Cluster>
     */
    private $clusters = [];

    /**
     * Array with subscribers.
     *
     * @var array<SubscriberInterface>
     */
    private $subscribers = [];

    /**
     * @param int $iterationLimit - 0 means that there is no limit
     */
    public function __construct(int $clusterCount = 1, $iterationLimit = 0)
    {
        $this->clusterCount = $clusterCount;
        $this->iterationLimit = $iterationLimit;
    }

    /**
     * Groups the points into the clusters.
     */
    public function cluster(array $data): array
    {
        // assign points
        $this->initializeData($data);

        // assign random clusters to points
        $this->assignRandomClusters();

        $iteration = 1;
        while (!$this->limitExceeded($iteration) && $this->iterate($iteration)) {
            ++$iteration;
        }

        return $this->clusters;
    }

    /**
     * Attach subscriber that will be notified about events.
     */
    public function attachSubscriber(SubscriberInterface $subscriber): void
    {
        $this->subscribers[] = $subscriber;
    }

    /**
     * Initialize KMeans data.
     *
     * @return void
     */
    private function initializeData(array $data)
    {
        foreach ($data as $coordinates) {
            $this->addPoint($coordinates);
        }
        $this->notifySampleDataEvent($this->points);
    }

    /**
     * Assigns random cluster label for each point.
     */
    private function assignRandomClusters(): void
    {
        // add points to clusters
        foreach ($this->points as &$point) {
            $randomizedClusterIndex = rand(1, $this->clusterCount);
            if (false == array_key_exists($randomizedClusterIndex, $this->clusters)) {
                $this->clusters[$randomizedClusterIndex] = new Cluster();
            }

            $this->clusters[$randomizedClusterIndex]->assign($point);
        }

        $this->notifyRandomAssignmentEvent($this->clusters);
    }

    /**
     * @todo use \SplObjectStorage for better attach/detach functions
     * and faster operation of the alghoritm.
     *
     * @return bool
     */
    private function iterate(int $i)
    {
        $attach = [];
        $detach = [];

        // make sure clusters have up to date centroids data
        foreach ($this->clusters as $cluster) {
            $cluster->computeCentroid();
        }

        // calculate changes in clusters
        foreach ($this->clusters as $clusterIndex => $cluster) {
            foreach ($cluster->getAll() as $point) {
                $closestClusterIndex = $point->getClosestCluster($this->clusters);

                if ($closestClusterIndex !== $clusterIndex) {
                    $attach[$closestClusterIndex][] = $point;
                    $detach[$clusterIndex][] = $point;
                }
            }
        }

        // if there was no change between iterations then stop the process
        if (empty($attach) && empty($detach)) {
            return false;
        }
        // notify only when there is any change to cluster data
        $this->notifyCentroidsCreationEvent($this->clusters, $i);

        // attach the point to the nearest cluster
        foreach ($attach as $clusterIndex => $pointsToAttach) {
            foreach ($pointsToAttach as $point) {
                $this->clusters[$clusterIndex]->assign($point);
            }
        }

        // detach points from the outdated position
        foreach ($detach as $clusterIndex => $pointsToDetach) {
            foreach ($pointsToDetach as $point) {
                $this->clusters[$clusterIndex]->detach($point);
            }
        }
        $this->notifyNewClustersEvent($this->clusters, $i);

        return true;
    }

    /**
     * Creates the point object and adds it to the points array.
     */
    private function addPoint(array $coordinate): void
    {
        $this->points[] = new Point($coordinate);
    }

    private function limitExceeded(int $iteration): bool
    {
        return 0 != $this->iterationLimit && $iteration > $this->iterationLimit;
    }

    /**
     * @param array<Point> $points
     */
    private function notifySampleDataEvent(array $points): void
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->sampleDataEvent($points);
        }
    }

    /**
     * @param array<Cluster> $clusters
     */
    private function notifyRandomAssignmentEvent(array $clusters): void
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->randomPointsAssignmentEvent($clusters);
        }
    }

    /**
     * @param array<Cluster> $clusters
     * @param array<array>   $centroids
     */
    private function notifyCentroidsCreationEvent(array $clusters, int $index): void
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->centroidsCreationEvent($clusters, $index);
        }
    }

    /**
     * @param array<Cluster> $clusters
     */
    private function notifyNewClustersEvent(array $clusters, int $index): void
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->newClustersEvent($clusters, $index);
        }
    }
}
