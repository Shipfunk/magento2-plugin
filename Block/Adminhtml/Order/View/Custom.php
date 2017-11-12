<?php

namespace Nord\Shipfunk\Block\Adminhtml\Order\View;

use Nord\Shipfunk\Api\Data\OrderSelectedPickupInterface;

/**
 * Class Custom
 * @package Nord\Shipfunk\Block\Adminhtml\Order\View
 */
class Custom extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * @var \Nord\Shipfunk\Api\Data\OrderSelectedPickupInterface
     */
    protected $pickup;

    /**
     * @return bool
     */
    public function hasPickupInformation()
    {
        $orderExtension = $this->getOrder()->getExtensionAttributes();
        if ($orderExtension && $orderExtension->getSelectedPickup() && $orderExtension->getSelectedPickup()->getPickupName()) {
            $this->pickup = $orderExtension->getSelectedPickup();
            return true;
        }
    }

    /**
     * @return string
     */
    public function getPickupInformation()
    {
        return $this->pickup->getPickupName();
    }
}