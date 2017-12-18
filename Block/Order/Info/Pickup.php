<?php

namespace Nord\Shipfunk\Block\Order\Info;

use Nord\Shipfunk\Model\Order\SelectedPickupFactory;
use Magento\Framework\Registry;

class Pickup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var SelectedPickupFactory
     */
    protected $orderSelectedPickupFactory;
  
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param SelectedPickupFactory $orderSelectedPickupFactory
     * @param Registry $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        SelectedPickupFactory $orderSelectedPickupFactory,
        Registry $registry,
        array $data = []
    ) {
        $this->orderSelectedPickupFactory = $orderSelectedPickupFactory;
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getPickupInfo()
    {
        $order = $this->coreRegistry->registry('current_order');
        // @todo should be rewriten not to use load() but either a resource-load or collection
        $orderSelectedPickup = $this->orderSelectedPickupFactory->create()->load($order->getId(), 'order_id');
        if ($orderSelectedPickup) {
            return implode("<br>", [
              $orderSelectedPickup->getPickupName(),
              $orderSelectedPickup->getPickupAddress(),
              $orderSelectedPickup->getPickupPostcode(),
              $orderSelectedPickup->getPickupCity()
            ]) ;
        }
        
        return '';
        
    }
}
