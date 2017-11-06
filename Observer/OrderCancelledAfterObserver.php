<?php

namespace Nord\Shipfunk\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Nord\Shipfunk\Model\Api\Shipfunk\SetOrderStatus;

/**
 * Class OrderCancelledAfterObserver
 *
 * @package Nord\Shipfunk\Observer
 */
class OrderCancelledAfterObserver implements ObserverInterface
{
    /**
     * @var SetOrderStatus
     */
    protected $SetOrderStatus;

    /**
     * @param SetOrderStatus       $SetOrderStatus
     */
    public function __construct(
        SetOrderStatus $SetOrderStatus
    ) {
        $this->SetOrderStatus = $SetOrderStatus;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        /** @noinspection PhpUndefinedMethodInspection */
        $order = $observer->getEvent()->getOrder();

        $this->SetOrderStatus
            ->setOrderId($order->getRealOrderId())
            ->setOrderStatus(SetOrderStatus::STATUS_CANCEL)
            ->execute();
    }
}