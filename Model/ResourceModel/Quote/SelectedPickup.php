<?php

namespace Nord\Shipfunk\Model\ResourceModel\Quote;

class SelectedPickup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table and initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('quote_selected_pickup', 'selected_pickup_id');
    }
}
