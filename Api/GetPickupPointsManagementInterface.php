<?php

namespace Nord\Shipfunk\Api;

/**
 * Interface GetPickupPointsManagementInterface
 * @api
 */
interface GetPickupPointsManagementInterface
{
    /**
     * @param string $cartId
     * @param string $query
     * @return \Nord\Shipfunk\Api\Data\ShipfunkResponseInterface
     */
    public function getPickupPointsFromShipfunk(
        $cartId,
        string $query
    );
}
