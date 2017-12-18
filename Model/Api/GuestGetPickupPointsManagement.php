<?php

namespace Nord\Shipfunk\Model\Api;

class GuestGetPickupPointsManagement implements \Nord\Shipfunk\Api\GuestGetPickupPointsManagementInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;
    
    /**
     * @var \Nord\Shipfunk\Api\GetPickupPointsManagementInterface
     */
    protected $getPickupPointsManagement;

    /**
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Nord\Shipfunk\Api\GetPickupPointsManagementInterface $getPickupPointsManagement
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Nord\Shipfunk\Api\GetPickupPointsManagementInterface $getPickupPointsManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->getPickupPointsManagement = $getPickupPointsManagement;
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
        return $this->getPickupPointsManagement->getPickupPointsFromShipfunk(
            $quoteIdMask->getQuoteId(),
            $query
        );
    }
}
