<?php

namespace Nord\Shipfunk\Plugin;

use Magento\Sales\Api\OrderRepositoryInterface;
use Nord\Shipfunk\Model\Order\SelectedPickupFactory;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Framework\App\ObjectManager;

class LoadOrderAfter
{
    /**
     * @var OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * @var SelectedPickupFactory
     */
    protected $orderSelectedPickupFactory;
    
    /**
     * Constructor
     *
     * @param SelectedPickupFactory $orderSelectedPickupFactory
     * @param OrderExtensionFactory|null $orderExtensionFactory
     */
    public function __construct(
        SelectedPickupFactory $orderSelectedPickupFactory = null,
        OrderExtensionFactory $orderExtensionFactory = null
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory ?: ObjectManager::getInstance()
            ->get(OrderExtensionFactory::class);
        $this->orderSelectedPickupFactory = $orderSelectedPickupFactory ?: ObjectManager::getInstance()
            ->get(SelectedPickupFactory::class);
    }
  
    public function afterGet(OrderRepositoryInterface $subject, $result, $orderId)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $result;
        $orderExtension = $order->getExtensionAttributes();
        if ($orderExtension === null) {
            $orderExtension = $this->orderExtensionFactory->create();
        }
        // should be rewriten not to use load() but either a resource-load or collection
        $orderSelectedPickup = $this->orderSelectedPickupFactory->create()->load($orderId, 'order_id');
        $orderExtension->setSelectedPickup($orderSelectedPickup);
        $order->setExtensionAttributes($orderExtension);
        return $order;
    }
}