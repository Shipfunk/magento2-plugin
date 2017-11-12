<?php

namespace Nord\Shipfunk\Model\Api;

use Nord\Shipfunk\Model\Api\Shipfunk\SelectedDelivery;

/**
 * @todo Should this be merged with ShippingInformationManagementPlugin and dedicated API removed ?
 */
class SelectedDeliveryManagement implements \Nord\Shipfunk\Api\SelectedDeliveryManagementInterface
{
    /**
     * @var ShipfunkResponseFactory
     */
    protected $shipfunkResponseFactory;
  
    /**
     * @var SelectedDelivery
     */
    protected $selectedDeliveryClient;

    /**
     * @param \Nord\Shipfunk\Model\Api\ShipfunkResponseFactory $shipfunkResponseFactory
     * @param \Nord\Shipfunk\Model\Api\Shipfunk\SelectedDelivery $selectedDelivery
     * @codeCoverageIgnore
     */
    public function __construct(
        \Nord\Shipfunk\Model\Api\ShipfunkResponseFactory $shipfunkResponseFactory,
        SelectedDelivery $selectedDelivery
    ) {
        $this->shipfunkResponseFactory = $shipfunkResponseFactory;
        $this->selectedDeliveryClient = $selectedDelivery;
    }

    /**
     * {@inheritDoc}
     */
    public function submitSelectedDeliveryToShipfunk(
        $cartId,
        string $query
    ) {        
        $response = $this->selectedDeliveryClient
                         ->setOrderId($cartId)
                         ->execute(["query" => json_decode($query, true)]);
        /** @var \Nord\Shipfunk\Api\Data\ShipfunkResponseInterface $shipfunkResponse */
        $shipfunkResponse = $this->shipfunkResponseFactory->create();
        $shipfunkResponse->setResponse($response->getBody());
        return $shipfunkResponse;
    }
}
