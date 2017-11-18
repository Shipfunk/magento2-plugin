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
                    'remove_all_parcels' => 1
                ]
             ]
          ];
        }
    
        $query = utf8_encode(json_encode($query));
        $result = $this->setEndpoint('delete_parcels')->get($query);
      
        return $result;
    }
}