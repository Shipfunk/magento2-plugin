<?php

namespace Nord\Shipfunk\Model\Api;

use Nord\Shipfunk\Model\Api\Shipfunk\GetPickups;

class GetPickupPointsManagement implements \Nord\Shipfunk\Api\GetPickupPointsManagementInterface
{
    /**
     * @var ShipfunkResponseFactory
     */
    protected $shipfunkResponseFactory;
  
    /**
     * @var GetPickups
     */
    protected $getPickupsClient;

    /**
     * @param \Nord\Shipfunk\Model\Api\ShipfunkResponseFactory $shipfunkResponseFactory
     * @param \Nord\Shipfunk\Model\Api\Shipfunk\GetPickups $getPickups
     * @codeCoverageIgnore
     */
    public function __construct(
        \Nord\Shipfunk\Model\Api\ShipfunkResponseFactory $shipfunkResponseFactory,
        GetPickups $getPickups
    ) {
        $this->shipfunkResponseFactory = $shipfunkResponseFactory;
        $this->getPickupsClient = $getPickups;
    }

    /**
     * {@inheritDoc}
     */
    public function getPickupPointsFromShipfunk(
        $cartId,
        string $query
    ) {        
        $response = $this->getPickupsClient
                         ->setOrderId($cartId)
                         ->execute(["query" => json_decode($query, true)]);
        /** @var \Nord\Shipfunk\Api\Data\ShipfunkResponseInterface $shipfunkResponse */
        $shipfunkResponse = $this->shipfunkResponseFactory->create();
        $shipfunkResponse->setResponse($response->getBody());
        return $shipfunkResponse;
    }
}
