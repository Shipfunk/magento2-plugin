<?php

namespace Nord\Shipfunk\Model\ResourceModel\SelectedPickup;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Nord\Shipfunk\Model\SelectedPickup', 'Nord\Shipfunk\Model\ResourceModel\SelectedPickup');
    }
  
    public function joinPickups() {
      $this->getSelect()->joinLeft(
         ['nsp' => $this->getTable('nord_shipfunk_pickups')],
         'main_table.pickup_id = nsp.pickup_id',
         ['pickup'=>'pickup']
      );
      return $this;
    }
  
    public function joinQuoteMask() {
      $this->getSelect()->joinLeft(
         ['qim' => $this->getTable('quote_id_mask')],
         'main_table.quote_id = qim.quote_id',
         ['masked_id'=>'masked_id']
      );
      return $this;
    }
  
}
