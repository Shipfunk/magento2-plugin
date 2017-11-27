<?php

namespace Nord\Shipfunk\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Nord\Shipfunk\Model\Api\Shipfunk\DeleteParcels;

/**
 *
 * @package Nord\Shipfunk\Observer
 */
class TrackingDeleteBeforeObserver implements ObserverInterface
{
    /**
     * @var DeleteParcels
     */
    protected $DeleteParcels;

    /**
     * @param DeleteParcels       $DeleteParcels
     */
    public function __construct(
        DeleteParcels $DeleteParcels
    ) {
        $this->DeleteParcels = $DeleteParcels;
    }
  
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $tracking = $observer->getEvent()->getTrack();
        $trackingCode = $tracking->getTrackNumber();
        $orderId = $tracking->getShipment()->getOrder()->getRealOrderId();
        $shipfunkResponse = $this->DeleteParcels->setOrderId($orderId)->setTrackingCode($trackingCode)->execute();
        $deleteResult = json_decode($shipfunkResponse->getBody());
        if (isset($deleteResult->Error) || isset($deleteResult->Info)) {
            if (isset($deleteResult->Error)) {
              $message = $deleteResult->Error->Message;
            } else {
              $message = $deleteResult->Info->Message;
            }
            throw new \Magento\Framework\Exception\LocalizedException(__($message));
        } else {
            // @todo update the shipping labels and if possible packages info, when delete parcels API becomes stable enough
        }
        return $this;
    }
}