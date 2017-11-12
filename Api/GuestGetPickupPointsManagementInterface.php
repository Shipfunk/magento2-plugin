<?php

namespace Nord\Shipfunk\Api;

/**
 * Interface GuestGetPickupPointsManagementInterface
 * @api
 */
interface GuestGetPickupPointsManagementInterface
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
