<?php

namespace Mariuszsienkiewicz\PAI\KMeans\Subscriber;

/**
 * Point of this subscriber is to create the image snapshot of each step of the
 * clustering process.
 *
 * It's a really basic implementation just to show the points in the data contrainer.
 *
 * GD library is required.
 */
class ImageSubscriber implements SubscriberInterface
{
    /**
     * Path to the folder where the images will be saved.
     */
    private string $imgsPath;

    /**
     * Image width in pixels.
     */
    private int $width;

    /**
     * Image height in pixels.
     */
    private int $height;

    /**
     * Padding percentage that will be used to move elements apart.
     */
    private float $paddingPercentage = .05;

    /**
     * Data container min height in pixels, used to map the values on container.
     */
    private int $dataContainerMinHeight;

    /**
     * Data container max height in pixels, used to map the values on container.
     */
    private int $dataContainerMaxHeight;

    /**
     * Data container min width in pixels, used to map the values on container.
     */
    private int $dataContainerMinWidth;

    /**
     * Data container max width in pixels, used to map the values on container.
     */
    private int $dataContainerMaxWidth;

    /**
     * Text offset used to properly place text in the padding space.
     */
    private float $textOffset = 1.25;

    /**
     * Size of the point that represents data.
     */
    private int $pointSize = 2;

    /**
     * Size of point that represents centroid.
     */
    private int $centroidSize = 8;

    /**
     * Minimal data value, used to map pixels.
     */
    private float $minVal;

    /**
     * Maximal data value, used to map pixels.
     */
    private float $maxVal;

    /**
     * Array of rgb values that will be used to colour points in clusters.
     */
    private array $colours = [
        1 => [244, 67, 54],
        2 => [232, 30, 99],
        3 => [156, 39, 176],
        4 => [103, 58, 183],
        5 => [63, 81, 181],
        6 => [33, 150, 243],
        7 => [3, 169, 244],
        8 => [0, 188, 212],
        9 => [0, 150, 136],
        10 => [76, 175, 80],
        11 => [139, 195, 74],
        12 => [205, 220, 57],
        13 => [255, 235, 59],
        14 => [255, 193, 7],
        15 => [255, 152, 0],
        16 => [255, 87, 34],
        17 => [121, 85, 72],
        18 => [158, 158, 158],
        19 => [96, 125, 139],
    ];

    /**
     * @param string       $imgsPath - path to the image folder
     * @param int          $width    - image width in pixels
     * @param int          $height   - image height in pixels
     * @param float        $minVal   - min value of data (used for value mapping)
     * @param float        $maxVal   - max value of data (used for value mapping)
     * @param array<array> $colours  - array where key is the index of the cluster and value is rgb array of that cluster
     */
    public function __construct(string $imgsPath, int $width, int $height, float $minVal, float $maxVal, array $colours = [])
    {
        $this->imgsPath = realpath($imgsPath);
        $this->width = $width;
        $this->height = $height;
        $this->minVal = $minVal;
        $this->maxVal = $maxVal;
        $this->dataContainerMinHeight = $this->height * $this->paddingPercentage;
        $this->dataContainerMaxHeight = $this->height - ($this->height * $this->paddingPercentage);
        $this->dataContainerMinWidth = $this->width * $this->paddingPercentage;
        $this->dataContainerMaxWidth = $this->width - ($this->width * $this->paddingPercentage);
        $this->colours = $colours + $this->colours;
    }

    /**
     * {@inheritdoc}
     */
    public function sampleDataEvent(array $points): void
    {
        $im = $this->getImage();
        $pointsCount = 0;

        $pointColour = imagecolorallocate($im, 255, 255, 255);

        foreach ($points as $point) {
            $this->addEllipse($im, $point->coordinates[0], $point->coordinates[1], $this->pointSize, $pointColour);
            ++$pointsCount;
        }

        $this->addText($im, 'Sample data.');
        $this->saveImage($im, 'sample_data');
    }

    /**
     * {@inheritdoc}
     */
    public function randomPointsAssignmentEvent(array $clusters): void
    {
        $im = $this->getImage();

        $this->addDataToImage($im, $clusters);
        $this->addText($im, 'Random cluster assignment.');
        $this->saveImage($im, 'random_assignment');
    }

    /**
     * {@inheritdoc}
     */
    public function centroidsCreationEvent(array $clusters, int $index): void
    {
        $im = $this->getImage();

        $this->addDataToImage($im, $clusters, $clusters);
        $this->addText($im, "Iteration: $index\A");
        $this->saveImage($im, $index.'A');
    }

    /**
     * {@inheritdoc}
     */
    public function newClustersEvent(array $clusters, int $index): void
    {
        $im = $this->getImage();

        $this->addDataToImage($im, $clusters);
        $this->addText($im, "Iteration: $index\B");
        $this->saveImage($im, $index.'B');
    }

    /**
     * Sets the single data point, in pixels.
     */
    public function setPointSize(int $size): void
    {
        $this->pointSize = $size;
    }

    /**
     * Sets the centroid data point, in pixels, should be bigger
     * than the point size.
     */
    public function setCentroidSize(int $size): void
    {
        $this->centroidSize = $size;
    }

    /**
     * Adds data to the image, it's possible to pass centroids
     * to add them in the same moment as clusters data.
     *
     * @param array $centroids - optional
     *
     * @return void
     */
    private function addDataToImage(\GdImage $im, array $clusters, array $centroids = [])
    {
        foreach ($clusters as $key => $cluster) {
            foreach ($cluster->getAll() as $point) {
                $this->addEllipse($im, $point->coordinates[0], $point->coordinates[1], $this->pointSize, $this->getColour($im, $key));
            }
        }

        if (!empty($centroids)) {
            foreach ($centroids as $key => $cluster) {
                if (!empty($cluster->getAll())) {
                    $this->addEllipse($im, $cluster->centroid->coordinates[0], $cluster->centroid->coordinates[1], $this->centroidSize, $this->getColour($im, $key));
                }
            }
        }
    }

    /**
     * Adds the point to the data container.
     * This function uses the values ​​that have been padded.
     */
    private function addEllipse(\GdImage $im, int $x, int $y, int $size, int $colour): void
    {
        $x = $this->map($x, $this->minVal, $this->maxVal, $this->dataContainerMinWidth, $this->dataContainerMaxWidth);
        $y = $this->map($y, $this->minVal, $this->maxVal, $this->dataContainerMinHeight, $this->dataContainerMaxHeight);
        $reversedY = ($this->dataContainerMaxHeight - $y) + $this->height * $this->paddingPercentage;
        imagefilledellipse($im, $x, $reversedY, $size, $size, $colour);
    }

    /**
     * Adds the text below the data container.
     */
    private function addText(\GdImage $im, string $text): void
    {
        imagestring(
            $im,
            5,
            $this->width * $this->paddingPercentage,
            $this->height - ($this->height * $this->paddingPercentage / $this->textOffset),
            $text,
            imagecolorallocate($im, 255, 255, 255)
        );
    }

    /**
     * Saves the image to the requested path.
     *
     * @return void
     */
    private function saveImage(\GdImage $im, string $filename)
    {
        imagejpeg($im, $this->imgsPath.DIRECTORY_SEPARATOR."$filename.jpg");
    }

    private function getImage(): \GdImage
    {
        $im = imagecreatetruecolor($this->width, $this->height);
        imageantialias($im, true);

        // background
        imagefilledrectangle($im, 0, 0, imagesx($im) - 1, imagesy($im) - 1, imagecolorallocate($im, 35, 35, 35));

        // foreground
        imagefilledrectangle(
            $im,
            $this->width * $this->paddingPercentage,
            $this->height * $this->paddingPercentage,
            $this->width - ($this->width * $this->paddingPercentage),
            $this->height - ($this->height * $this->paddingPercentage),
            imagecolorallocate($im, 25, 25, 25)
        );

        return $im;
    }

    private function map(float $value, float $fromMin, float $fromMax, int $toMin, int $toMax)
    {
        return ($value - $fromMin) * ($toMax - $toMin) / ($fromMax - $fromMin) + $toMin;
    }

    private function getColour($im, $index)
    {
        [$r, $g, $b] = $this->colours[$index];

        return imagecolorallocate($im, $r, $g, $b);
    }
}
