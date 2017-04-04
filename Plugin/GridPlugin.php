<?php

namespace Nord\Shipfunk\Plugin;

use Magento\Sales\Model\Order\Shipment\ItemFactory,
    Magento\Framework\Registry,
    Nord\Shipfunk\Helper\Data as ShipfunkDataHelper;

/**
 * Class GridPlugin
 * @package Nord\Shipfunk\Plugin
 */
class GridPlugin
{
    /**
     * @var ItemFactory
     */
    protected $_shipmentItemFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var ShipfunkDataHelper
     */
    protected $helper;

    /**
     * GridPlugin constructor.
     * @param ItemFactory $shipmentItemFactory
     * @param Registry $registry
     * @param ShipfunkDataHelper $helper
     */
    public function __construct(ItemFactory $shipmentItemFactory, Registry $registry, ShipfunkDataHelper $helper)
    {
        $this->_shipmentItemFactory = $shipmentItemFactory;
        $this->_coreRegistry = $registry;
        $this->helper = $helper;
    }

    /**
     * @param $interceptor
     *
     * @return array
     */
    public function aroundGetCollection($interceptor)
    {
        $defaultWeight = $this->helper->getConfigData('weight_default');

        if ($this->getShipment()->getId()) {
            $collection = $this->_shipmentItemFactory->create()->getCollection()->setShipmentFilter(
                $this->getShipment()->getId()
            );
        } else {
            $collection = $this->getShipment()->getAllItems();
        }

        foreach ($collection as $item) {
            if (is_null($item->getWeight())) {
                $item->setWeight($defaultWeight);
            }
        }

        return $collection;
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }
}