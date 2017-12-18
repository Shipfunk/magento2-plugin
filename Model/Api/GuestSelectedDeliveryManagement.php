<?php

namespace Nord\Shipfunk\Model\Api;

/**
 * @todo Should this be merged with ShippingInformationManagementPlugin and dedicated API removed ?
 */
class GuestSelectedDeliveryManagement implements \Nord\Shipfunk\Api\GuestSelectedDeliveryManagementInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;
    
    /**
     * @var \Nord\Shipfunk\Model\Api\SelectedDeliveryManagement
     */
    protected $selectedDeliveryManagement;

    /**
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Nord\Shipfunk\Model\Api\SelectedDeliveryManagement $selectedDeliveryManagement
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Nord\Shipfunk\Model\Api\SelectedDeliveryManagement $selectedDeliveryManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->selectedDeliveryManagement = $selectedDeliveryManagement;
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
        return $this->selectedDeliveryManagement->submitSelectedDeliveryToShipfunk(
            $quoteIdMask->getQuoteId(),
            $query
        );
    }
}
