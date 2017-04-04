<?php

namespace Nord\Shipfunk\Model\BoxPacker;

use DVDoug\BoxPacker\BoxList;
use DVDoug\BoxPacker\ItemList;
use DVDoug\BoxPacker\PackedBoxList;
use DVDoug\BoxPacker\WeightRedistributor;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Psr\Log\LoggerAwareInterface;

/**
 * Class ShipfunkPacker
 *
 * @package Nord\Shipfunk\Model\BoxPacker
 */
class ShipfunkPacker implements LoggerAwareInterface
{

    use LoggerAwareTrait;

    /**
     * List of items to be packed
     *
     * @var ItemList
     */
    protected $items;

    /**
     * List of box sizes available to pack items into
     *
     * @var BoxList
     */
    protected $boxes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->items = new ItemList();
        $this->boxes = new BoxList();

        $this->logger = new NullLogger();

    }

    /**
     * Add item to be packed
     *
     * @param Item $item
     * @param int  $qty
     */
    public function addItem(Item $item, $qty = 1)
    {
        for ($i = 0; $i < $qty; $i++) {
            $this->items->insert($item);
        }
        $this->logger->log(LogLevel::INFO,
            "added {$qty} x {$item->getDescription()} with price of {$item->getPrice()}");
    }

    /**
     * Set a list of items all at once
     *
     * @param \Traversable|array $items
     */
    public function setItems($items)
    {
        if ($items instanceof ItemList) {
            $this->items = clone $items;
        } else {
            $this->items = new ItemList();
            foreach ($items as $item) {
                $this->items->insert($item);
            }
        }
    }

    /**
     * Add box size
     *
     * @param Box $box
     */
    public function addBox(Box $box)
    {
        $this->boxes->insert($box);
        $this->logger->log(LogLevel::INFO, "added box {$box->getReference()}");
    }

    /**
     * Add a pre-prepared set of boxes all at once
     *
     * @param BoxList $boxList
     */
    public function setBoxes(BoxList $boxList)
    {
        $this->boxes = clone $boxList;
    }

    /**
     * @return BoxList
     */
    public function getBoxes()
    {
        $boxes = clone $this->boxes;

        return $boxes;
    }

    /**
     * Pack items into boxes
     *
     * @throws \RuntimeException
     * @return PackedBoxList
     */
    public function pack()
    {
        $packedBoxes = $this->doVolumePacking();

        //If we have multiple boxes, try and optimise/even-out weight distribution
        if ($packedBoxes->count() > 1) {
            $redistributor = new WeightRedistributor($this->boxes);
            $redistributor->setLogger($this->logger);
            $packedBoxes = $redistributor->redistributeWeight($packedBoxes);
        }

        $this->logger->log(LogLevel::INFO, "packing completed, {$packedBoxes->count()} boxes");

        return $packedBoxes;
    }

    /**
     * Pack items into boxes using the principle of largest volume item first
     *
     * @throws \RuntimeException
     * @return PackedBoxList
     */
    public function doVolumePacking()
    {

        $packedBoxes = new PackedBoxList;

        //Keep going until everything packed
        while ($this->items->count()) {
            $boxesToEvaluate      = clone $this->boxes;
            $packedBoxesIteration = new PackedBoxList();

            //Loop through boxes starting with smallest, see what happens
            while (!$boxesToEvaluate->isEmpty()) {
                $box = $boxesToEvaluate->extract();

                $volumePacker = new ShipfunkVolumePacker($box, clone $this->items);
                $volumePacker->setLogger($this->logger);
                $packedBox = $volumePacker->pack();
                if ($packedBox->getItems()->count()) {
                    $packedBoxesIteration->insert($packedBox);

                    //Have we found a single box that contains everything?
                    if ($packedBox->getItems()->count() === $this->items->count()) {
                        break;
                    }
                }
            }

            //Check iteration was productive
            if ($packedBoxesIteration->isEmpty()) {
                throw new \RuntimeException('Item '.$this->items->top()->getDescription().' 
                is too large to fit into any box');
            }

            //Find best box of iteration, and remove packed items from unpacked list
            $bestBox       = $packedBoxesIteration->top();
            $unPackedItems = $this->items->asArray();
            foreach (clone $bestBox->getItems() as $packedItem) {
                foreach ($unPackedItems as $unpackedKey => $unpackedItem) {
                    if ($packedItem === $unpackedItem) {
                        unset($unPackedItems[$unpackedKey]);
                        break;
                    }
                }
            }
            $unpackedItemList = new ItemList();
            foreach ($unPackedItems as $unpackedItem) {
                $unpackedItemList->insert($unpackedItem);
            }
            $this->items = $unpackedItemList;
            $packedBoxes->insert($bestBox);

        }

        return $packedBoxes;
    }

}