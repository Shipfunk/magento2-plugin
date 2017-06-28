<?php

namespace Nord\Shipfunk\Model\ResourceModel;

class Pickups extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
      $this->_init('nord_shipfunk_pickups', 'id');
    }

}
