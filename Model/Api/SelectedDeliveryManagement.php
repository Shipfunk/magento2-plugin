<?php

namespace Nord\Shipfunk\Model\Api;

use Nord\Shipfunk\Model\Api\Shipfunk\SelectedDelivery;

class SelectedDeliveryManagement implements \Nord\Shipfunk\Api\SelectedDeliveryManagementInterface
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
     * @var SelectedDelivery
     */
    protected $selectedDeliveryClient;

    /**
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Nord\Shipfunk\Model\Api\ShipfunkResponseFactory $shipfunkResponseFactory
     * @param \Nord\Shipfunk\Model\Api\Shipfunk\SelectedDelivery $selectedDelivery
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Nord\Shipfunk\Model\Api\ShipfunkResponseFactory $shipfunkResponseFactory,
        SelectedDelivery $selectedDelivery
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
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
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        
        $response = $this->selectedDeliveryClient
                         ->setOrderId($quoteIdMask->getQuoteId())
                         ->execute(["query" => json_decode($query, true)]);
        /** @var \Nord\Shipfunk\Api\Data\ShipfunkResponseInterface $shipfunkResponse */
        $shipfunkResponse = $this->shipfunkResponseFactory->create();
        $shipfunkResponse->setResponse($response->getBody());
        return $shipfunkResponse;
    }
}
