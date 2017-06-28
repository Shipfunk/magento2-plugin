<?php

namespace Nord\Shipfunk\Model\ResourceModel;

class SelectedPickup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
      $this->_init('nord_shipfunk_selected_pickup', 'id');
    }

}
