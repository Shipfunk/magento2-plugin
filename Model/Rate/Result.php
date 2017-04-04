<?php

namespace Nord\Shipfunk\Model\Rate;

use Magento\Shipping\Model\Rate\Result as OriginalResult;

class Result extends OriginalResult
{
    /**
     * Sort rates by price from min to max
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function sortRatesByPrice()
    {
        return $this;
    }
}