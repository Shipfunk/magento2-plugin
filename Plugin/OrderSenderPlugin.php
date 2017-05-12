<?php

namespace Nord\Shipfunk\Plugin;

use Magento\Sales\Model\Order;

/**
 * Class OrderSenderPlugin
 * @package Nord\Shipfunk\Plugin
 */
class OrderSenderPlugin
{
    public function aroundPrepareTemplate($proceed, Order $order)
    {
        $this->getResponse()->setBody(0);
    }
}