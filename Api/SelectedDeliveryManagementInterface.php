<?php

namespace Nord\Shipfunk\Api;

/**
 * Interface SelectedDeliveryManagementInterface
 * @api
 */
interface SelectedDeliveryManagementInterface
{
    /**
     * @param int $cartId
     * @param string $query
     * @return \Nord\Shipfunk\Api\Data\ShipfunkResponseInterface
     */
    public function submitSelectedDeliveryToShipfunk(
        $cartId,
        string $query
    );
}
