<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

/**
 * Class SetOrderStatus
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class SetOrderStatus extends AbstractEndpoint
{
    const STATUS_PLACE = 'placed';
    const STATUS_CANCEL = 'cancelled';
  
    public function execute($query = [])
    {
        if (!$query) {
          $query = [
             'query' => [
                'order' => [
                    'status' => $this->getOrderStatus()
                ]
             ]
          ];
        }
      
        if ($this->getFinalOrderId()) {
          $query['query']['order']['final_orderid'] = $this->getFinalOrderId();
        }
      
        $query = utf8_encode(json_encode($query));
        $result = $this->setEndpoint('set_order_status')
                      ->post($query);
      
        return $result;
    }
}