<?php

namespace Nord\Shipfunk\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 *
 * @package Nord\Shipfunk\Observer
 */
class OrderConfirmationEmailObserver implements ObserverInterface
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $pickupInfo = '';
        $transport = $observer->getEvent()->getTransport();
        $order = $transport->getOrder();
        $orderExtension = $order->getExtensionAttributes();
        if ($orderExtension && $orderExtension->getSelectedPickup() && $orderExtension->getSelectedPickup()->getPickupName()) {
            $pickupInfo = $orderExtension->getSelectedPickup()->getPickupName();
        }
      
        $transport->setData('formattedPickupAddress', $pickupInfo);
    }
}