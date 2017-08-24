<?php

namespace Nord\Shipfunk\Model\ResourceModel\Pickups;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Nord\Shipfunk\Model\Pickups', 'Nord\Shipfunk\Model\ResourceModel\Pickups');
    }
}
