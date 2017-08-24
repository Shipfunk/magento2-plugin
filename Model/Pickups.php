<?php

namespace Nord\Shipfunk\Model;

class Pickups extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct() {
        $this->_init('Nord\Shipfunk\Model\ResourceModel\Pickups');
    }
}
