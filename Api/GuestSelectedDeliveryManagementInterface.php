<?php

namespace Nord\Shipfunk\Api;

/**
 * Interface GuestSelectedDeliveryManagementInterface
 * @api
 */
interface GuestSelectedDeliveryManagementInterface
{
    /**
     * @param string $cartId
     * @param string $query
     * @return \Nord\Shipfunk\Api\Data\ShipfunkResponseInterface
     */
    public function submitSelectedDeliveryToShipfunk(
        $cartId,
        string $query
    );
}
