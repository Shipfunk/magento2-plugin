<?php

namespace Nord\Shipfunk\Model\ResourceModel\Quote\SelectedPickup;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Nord\Shipfunk\Model\Quote\SelectedPickup::class,
            \Nord\Shipfunk\Model\ResourceModel\Quote\SelectedPickup::class
        );
    }
}
