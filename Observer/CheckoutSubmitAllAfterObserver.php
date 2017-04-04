<?php

namespace Nord\Shipfunk\Observer;

use Magento\Framework\Event\ObserverInterface,
    Magento\Framework\Event\Observer,
    Magento\Sales\Model\Order;
use Nord\Shipfunk\Model\Api\Shipfunk\Helper\ParcelHelper,
    Nord\Shipfunk\Model\Api\Shipfunk\OrderPaid;
use Psr\Log\LoggerInterface;

/**
 * Class CheckoutSubmitAllAfterObserver
 *
 * NOTE FROM SHIPFUNK: THIS WILL BE CALLED WHEN AN ORDER HAS BEEN MADE AND NOT WHEN AN ORDER HAS BEEN PAID!
 *                     THERE IS NO API TO SET AN ORDER AS PAID ANYMORE
 *
 * @package Nord\Shipfunk\Observer
 */
class CheckoutSubmitAllAfterObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var OrderPaid
     */
    protected $OrderPaid;

    /**
     * @var ParcelHelper
     */
    protected $parcelHelper;

    /**
     * CheckoutSubmitAllAfterObserver constructor.
     *
     * @param OrderPaid       $OrderPaid
     * @param LoggerInterface $log
     * @param ParcelHelper    $parcelHelper
     */
    public function __construct(
        OrderPaid $OrderPaid,
        LoggerInterface $log,
        ParcelHelper $parcelHelper
    ) {
        $this->parcelHelper = $parcelHelper;
        $this->log          = $log;

        $this->OrderPaid = $OrderPaid;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        /** @noinspection PhpUndefinedMethodInspection */
        $order = $observer->getEvent()->getOrder();

        $this->OrderPaid
            ->setOrder($order)
            ->getResult();
    }
}