<?php

namespace Nord\Shipfunk\Model\Api;

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
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Nord\Shipfunk\Model\Api\ShipfunkResponseFactory $shipfunkResponseFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Nord\Shipfunk\Model\Api\ShipfunkResponseFactory $shipfunkResponseFactory
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->shipfunkResponseFactory = $shipfunkResponseFactory;
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
        /** @var \Nord\Shipfunk\Api\Data\ShipfunkResponseInterface $shipfunkResponse */
        $shipfunkResponse = $this->shipfunkResponseFactory->create();
        $shipfunkResponse->setResponse('{"test": 23}');
        return $shipfunkResponse;
    }
}
