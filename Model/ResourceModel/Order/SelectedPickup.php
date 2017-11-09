<?php

namespace Nord\Shipfunk\Model\ResourceModel\Order;

class SelectedPickup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table and initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order_selected_pickup', 'selected_pickup_id');
    }
}
