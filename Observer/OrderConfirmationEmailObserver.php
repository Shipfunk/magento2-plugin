<?php

namespace Nord\Shipfunk\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Nord\Shipfunk\Model\Order\SelectedPickupFactory as OrderPickupFactory;
use Nord\Shipfunk\Model\Quote\SelectedPickupFactory as QuotePickupFactory;

/**
 *
 * @package Nord\Shipfunk\Observer
 */
class OrderConfirmationEmailObserver implements ObserverInterface
{
    /**
     * @var OrderPickupFactory
     */
    protected $orderSelectedPickupFactory;
  
    /**
     * @var QuotePickupFactory
     */
    protected $quoteSelectedPickupFactory;
    
    /**
     * @param OrderPickupFactory $orderSelectedPickupFactory
     * @param QuotePickupFactory $quoteSelectedPickupFactory
     */
    public function __construct(
        OrderPickupFactory $orderSelectedPickupFactory,
        QuotePickupFactory $quoteSelectedPickupFactory
    ) {
        $this->orderSelectedPickupFactory = $orderSelectedPickupFactory;
        $this->quoteSelectedPickupFactory = $quoteSelectedPickupFactory;
    }
  
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $transport = $observer->getEvent()->getTransport();
        $order = $transport->getOrder();
        $pickupInfo = '';
        // @todo should be rewriten not to use load() but either a resource-load or collection
        $quoteSelectedPickup = $this->quoteSelectedPickupFactory->create()->load($order->getQuoteId(), 'quote_id');
        if ($quoteSelectedPickup) {
            $pickupInfo = implode("<br>", [
              $quoteSelectedPickup->getPickupName(),
              $quoteSelectedPickup->getPickupAddress(),
              $quoteSelectedPickup->getPickupPostcode(),
              $quoteSelectedPickup->getPickupCity()
            ]) ;
        }
        if (!$pickupInfo) {
        // @todo should be rewriten not to use load() but either a resource-load or collection
        $orderSelectedPickup = $this->orderSelectedPickupFactory->create()->load($order->getId(), 'order_id');
          if ($orderSelectedPickup) {
              $pickupInfo = implode("<br>", [
                $orderSelectedPickup->getPickupName(),
                $orderSelectedPickup->getPickupAddress(),
                $orderSelectedPickup->getPickupPostcode(),
                $orderSelectedPickup->getPickupCity()
              ]) ;
          }
        }
        $transport->setData('formattedPickupAddress', $pickupInfo);
    }
}