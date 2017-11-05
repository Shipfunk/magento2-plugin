<?php

namespace Nord\Shipfunk\Model\Api;

use Nord\Shipfunk\Model\Api\Shipfunk\GetPickups;

class GetPickupPointsManagement implements \Nord\Shipfunk\Api\GetPickupPointsManagementInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;
    
    /**
     * @var ShipfunkResponseFactory
     */
    protected $shipfunkResponseFactory;
  
    /**
     * @var GetPickups
     */
    protected $getPickupsClient;

    /**
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Nord\Shipfunk\Model\Api\ShipfunkResponseFactory $shipfunkResponseFactory
     * @param \Nord\Shipfunk\Model\Api\Shipfunk\GetPickups $getPickups
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Nord\Shipfunk\Model\Api\ShipfunkResponseFactory $shipfunkResponseFactory,
        GetPickups $getPickups
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
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
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        
        $response = $this->getPickupsClient
                         ->setOrderId($quoteIdMask->getQuoteId())
                         ->execute(["query" => json_decode($query, true)]);
        /** @var \Nord\Shipfunk\Api\Data\ShipfunkResponseInterface $shipfunkResponse */
        $shipfunkResponse = $this->shipfunkResponseFactory->create();
        $shipfunkResponse->setResponse($response->getBody());
        return $shipfunkResponse;
    }
}
