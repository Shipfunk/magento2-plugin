<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

/**
 * Class DeleteParcels
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class DeleteParcels extends AbstractEndpoint
{
    public function execute($query = [])
    {
        if (!$query) {
          $query = [
             'query' => [
                'order' => [
                    'remove_all_parcels' => 0,
                    'return_parcels' => 1,
                    'parcels' => [
                      ['tracking_code' => $this->getTrackingCode()]
                    ]
                ]
             ]
          ];
        }
    
        $query = utf8_encode(json_encode($query));
        $result = $this->setEndpoint('delete_parcels')->post($query);
      
        return $result;
    }
}