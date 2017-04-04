<?php

namespace Nord\Shipfunk\Model\BoxPacker;

use DVDoug\BoxPacker\ItemList;
use DVDoug\BoxPacker\VolumePacker;
use Psr\Log\LogLevel;

/**
 * Class ShipfunkVolumePacker
 *
 * @package Nord\Shipfunk\Model\BoxPacker
 */
class ShipfunkVolumePacker extends VolumePacker
{
    /**
     * ShipfunkVolumePacker constructor.
     *
     * @param Box      $box
     * @param ItemList $items
     */
    public function __construct(Box $box, ItemList $items)
    {
        parent::__construct($box, $items);
    }

    /**
     * Pack as many items as possible into specific given box
     *
     * @return ShipfunkPackedBox packed box
     */
    public function pack()
    {
        $this->logger->log(LogLevel::DEBUG, "[EVALUATING BOX] {$this->box->getReference()}");

        $packedItems     = new ItemList;
        $remainingDepth  = $this->box->getInnerDepth();
        $remainingWeight = $this->box->getMaxWeight() - $this->box->getEmptyWeight();
        $remainingWidth  = $this->box->getInnerWidth();
        $remainingLength = $this->box->getInnerLength();

        $layerWidth = $layerLength = $layerDepth = 0;
        while (!$this->items->isEmpty()) {

            $itemToPack = $this->items->top();

            //skip items that are simply too large
            if ($this->isItemTooLargeForBox($itemToPack, $remainingDepth, $remainingWeight)) {
                $this->items->extract();
                continue;
            }

            $this->logger->log(LogLevel::DEBUG, "evaluating item {$itemToPack->getDescription()}");
            $this->logger->log(LogLevel::DEBUG,
                "remaining width: {$remainingWidth}, length: {$remainingLength}, depth: {$remainingDepth}");
            $this->logger->log(LogLevel::DEBUG,
                "layerWidth: {$layerWidth}, layerLength: {$layerLength}, layerDepth: {$layerDepth}");

            $itemWidth  = $itemToPack->getWidth();
            $itemLength = $itemToPack->getLength();

            if ($this->fitsGap($itemToPack, $remainingWidth, $remainingLength)) {

                $packedItems->insert($this->items->extract());
                $remainingWeight -= $itemToPack->getWeight();

                $nextItem = !$this->items->isEmpty() ? $this->items->top() : null;
                if ($this->fitsBetterUnrotated($itemToPack, $nextItem, $remainingWidth, $remainingLength)) {
                    $this->logger->log(LogLevel::DEBUG, "fits (better) unrotated");
                    $remainingLength -= $itemLength;
                    $layerLength += $itemLength;
                    $layerWidth = max($itemWidth, $layerWidth);
                } else {
                    $this->logger->log(LogLevel::DEBUG, "fits (better) rotated");
                    $remainingLength -= $itemWidth;
                    $layerLength += $itemWidth;
                    $layerWidth = max($itemLength, $layerWidth);
                }
                $layerDepth = max($layerDepth,
                    $itemToPack->getDepth()); //greater than 0, items will always be less deep

                //allow items to be stacked in place within the same footprint up to current layerdepth
                $maxStackDepth = $layerDepth - $itemToPack->getDepth();
                while (!$this->items->isEmpty() && $this->canStackItemInLayer($itemToPack, $this->items->top(),
                        $maxStackDepth, $remainingWeight)) {
                    $remainingWeight -= $this->items->top()->getWeight();
                    $maxStackDepth -= $this->items->top()->getDepth();
                    $packedItems->insert($this->items->extract());
                }
            } else {
                if ($remainingWidth >= min($itemWidth, $itemLength) && $this->isLayerStarted($layerWidth, $layerLength,
                        $layerDepth)
                ) {
                    $this->logger->log(LogLevel::DEBUG, "No more fit in lengthwise, resetting for new row");
                    $remainingLength += $layerLength;
                    $remainingWidth -= $layerWidth;
                    $layerWidth = $layerLength = 0;
                    continue;
                } elseif ($remainingLength < min($itemWidth, $itemLength) || $layerDepth == 0) {
                    $this->logger->log(LogLevel::DEBUG, "doesn't fit on layer even when empty");
                    $this->items->extract();
                    continue;
                }

                $remainingWidth  = $layerWidth ? min(floor($layerWidth * 1.1),
                    $this->box->getInnerWidth()) : $this->box->getInnerWidth();
                $remainingLength = $layerLength ? min(floor($layerLength * 1.1),
                    $this->box->getInnerLength()) : $this->box->getInnerLength();
                $remainingDepth -= $layerDepth;

                $layerWidth = $layerLength = $layerDepth = 0;
                $this->logger->log(LogLevel::DEBUG, "doesn't fit, so starting next vertical layer");
            }
        }
        $this->logger->log(LogLevel::DEBUG, "done with this box");

        return new ShipfunkPackedBox($this->box, $packedItems, $remainingWidth, $remainingLength, $remainingDepth,
            $remainingWeight);
    }

}