<?php

namespace Nord\Shipfunk\Model\BoxPacker;

use DVDoug\BoxPacker\Box as BoxInterface;

/**
 * Class Box
 *
 * @package Nord\Shipfunk\Model\BoxPacker
 */
class Box implements BoxInterface
{
    /**
     * @var  string
     */
    protected $reference;

    /**
     * @var  int
     */
    protected $outerWidth;

    /**
     * @var  int
     */
    protected $outerLength;

    /**
     * @var  int
     */
    protected $outerDepth;

    /**
     * @var  int
     */
    protected $emptyWeight;

    /**
     * @var  int
     */
    protected $innerWidth;

    /**
     * @var  int
     */
    protected $innerLength;

    /**
     * @var  int
     */
    protected $innerDepth;

    /**
     * @var  int
     */
    protected $maxWeight;

    /**
     * @var  int
     */
    protected $innerVolume;

    /**
     * Box constructor.
     *
     * @param string $reference
     * @param int    $outerWidth
     * @param int    $outerLength
     * @param int    $outerDepth
     * @param int    $emptyWeight
     * @param int    $innerWidth
     * @param int    $innerLength
     * @param int    $innerDepth
     * @param int    $maxWeight
     */
    public function __construct(
        $reference,
        $outerWidth,
        $outerLength,
        $outerDepth,
        $emptyWeight,
        $innerWidth,
        $innerLength,
        $innerDepth,
        $maxWeight
    ) {
        $this->reference   = $reference;
        $this->outerWidth  = $outerWidth;
        $this->outerLength = $outerLength;
        $this->outerDepth  = $outerDepth;
        $this->emptyWeight = $emptyWeight;
        $this->innerWidth  = $innerWidth;
        $this->innerLength = $innerLength;
        $this->innerDepth  = $innerDepth;
        $this->maxWeight   = $maxWeight;
        $this->innerVolume = $this->innerWidth * $this->innerLength * $this->innerDepth;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return int
     */
    public function getOuterWidth()
    {
        return $this->outerWidth;
    }

    /**
     * @return int
     */
    public function getOuterLength()
    {
        return $this->outerLength;
    }

    /**
     * @return int
     */
    public function getOuterDepth()
    {
        return $this->outerDepth;
    }

    /**
     * @return int
     */
    public function getEmptyWeight()
    {
        return $this->emptyWeight;
    }

    /**
     * @return int
     */
    public function getInnerWidth()
    {
        return $this->innerWidth;
    }

    /**
     * @return int
     */
    public function getInnerLength()
    {
        return $this->innerLength;
    }

    /**
     * @return int
     */
    public function getInnerDepth()
    {
        return $this->innerDepth;
    }

    /**
     * @return int
     */
    public function getInnerVolume()
    {
        return $this->innerVolume;
    }

    /**
     * @return int
     */
    public function getMaxWeight()
    {
        return $this->maxWeight;
    }
}