<?php

namespace Nord\Shipfunk\Model\BoxPacker;

use DVDoug\BoxPacker\Item as ItemInterface;

/**
 * Class Item
 *
 * @package Nord\Shipfunk\Model\BoxPacker
 */
class Item implements ItemInterface
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $length;

    /**
     * @var int
     */
    protected $depth;

    /**
     * @var int
     */
    protected $weight;

    /**
     * @var int
     */
    protected $keepFlat;

    /**
     * @var int
     */
    protected $volume;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var int
     */
    protected $cartItemId;

    /**
     * @var int
     */
    protected $productId;

    /**
     * @var int
     */
    protected $quantity;

    /**
     * Item constructor.
     *
     * @param $description
     * @param $width
     * @param $length
     * @param $depth
     * @param $weight
     * @param $keepFlat
     * @param $price
     * @param $cartItemId
     * @param $productId
     * @param $quantity
     */
    public function __construct(
        $description,
        $width,
        $length,
        $depth,
        $weight,
        $keepFlat,
        $price,
        $cartItemId,
        $productId,
        $quantity
    ) {
        $this->description = $description;
        $this->width       = $width;
        $this->length      = $length;
        $this->depth       = $depth;
        $this->weight      = $weight;
        $this->keepFlat    = $keepFlat;
        $this->volume      = $this->width * $this->length * $this->depth;
        $this->price       = $price;
        $this->cartItemId  = $cartItemId;
        $this->productId   = $productId;
        $this->quantity    = $quantity;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return int
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * @return int
     */
    public function getKeepFlat()
    {
        return $this->keepFlat;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getCartItemId()
    {
        return $this->cartItemId;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}