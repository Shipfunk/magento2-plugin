<?php

namespace Nord\Shipfunk\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Nord\Shipfunk\Model\Api\Shipfunk\SetOrderStatus;
use Nord\Shipfunk\Model\Order\SelectedPickupFactory;

/**
 * Class CheckoutSubmitAllAfterObserver
 *
 * @package Nord\Shipfunk\Observer
 * @todo Should we send final customer details to Shipfunk at this point ?
 * @todo Should there be an button on order view, to manually send the SetOrderStatus API, in case it fails here.
 */
class CheckoutSubmitAllAfterObserver implements ObserverInterface
{
    /**
     * @var SetOrderStatus
     */
    protected $SetOrderStatus;
  
    /**
     * @var \Nord\Shipfunk\Model\Order\SelectedPickupFactory
     */
    protected $orderSelectedPickupFactory;
  
    /**
     * constructor.
     *
     * @param SelectedPickupFactory $orderSelectedPickupFactory
     * @param SetOrderStatus       $SetOrderStatus
     */
    public function __construct(
        SelectedPickupFactory $orderSelectedPickupFactory,
        SetOrderStatus $SetOrderStatus
    ) {
        $this->orderSelectedPickupFactory = $orderSelectedPickupFactory;
        $this->SetOrderStatus = $SetOrderStatus;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        // If set, copy the quote selected pickup info to order
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension) {
            $quoteSelectedPickup = $cartExtension->getSelectedPickup();
            if ($quoteSelectedPickup && $quoteSelectedPickup->getPickupName()) {
                $orderSelectedPickup = $this->orderSelectedPickupFactory->create();
                $orderSelectedPickup->setPickupName($quoteSelectedPickup->getPickupName())
                                ->setPickupAddress($quoteSelectedPickup->getPickupAddress())
                                ->setPickupPostcode($quoteSelectedPickup->getPickupPostcode())
                                ->setPickupCity($quoteSelectedPickup->getPickupCity())
                                ->setPickupCountry($quoteSelectedPickup->getPickupCountry())
                                ->setPickupId($quoteSelectedPickup->getPickupId())
                                ->setPickupOpeningHours($quoteSelectedPickup->getPickupOpeningHours())
                                ->setPickupOpeningHoursException($quoteSelectedPickup->getPickupOpeningHoursException())
                                ->setOrder($order)
                                ->save();
            }
        }
        // Send placed order status to Shipfunk 
        $this->SetOrderStatus
            ->setOrderId($order->getQuoteId())
            ->setFinalOrderId($order->getRealOrderId())
            ->setOrderStatus(SetOrderStatus::STATUS_PLACE)
            ->execute();
    }
}