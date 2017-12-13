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
     * @var \Magento\Shipping\Model\Shipping\LabelGenerator
     */
    protected $labelGenerator;

    /**
     * @param DeleteParcels       $DeleteParcels
     * @param \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator
     */
    public function __construct(
        DeleteParcels $DeleteParcels,
        \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator
    ) {
        $this->DeleteParcels = $DeleteParcels;
        $this->labelGenerator = $labelGenerator;
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
        if (isset($deleteResult->Error) || (isset($deleteResult->Info) && $deleteResult->Info->Code == '10007')) {
            if (isset($deleteResult->Error)) {
              $message = $deleteResult->Error->Message;
            } else {
              $message = $deleteResult->Info->Message;
            }
            throw new \Magento\Framework\Exception\LocalizedException(__($message));
        } 
        // @todo update the shipping labels and if possible packages info. Has some sort of race condition bug, might need to be done somewhere else
        if (isset($deleteResult->Info) && $deleteResult->Info->Code == '10011') {
            // $tracking->getShipment()->setShippingLabel(null)->setPackages([])->save();
        }
      
        if (isset($deleteResult->response) && isset($deleteResult->response->parcels)) {
            $labelsContent = [];
            foreach ($deleteResult->response->parcels as $parcel) {
                $sendCard = base64_decode($parcel->package_cards->send);
                $labelsContent[] = $sendCard;
            }
          
            $outputPdf = $this->labelGenerator->combineLabelsPdf($labelsContent);
            // $tracking->getShipment()->setShippingLabel($outputPdf->render())->save();
        }
        
        return $this;
    }
}