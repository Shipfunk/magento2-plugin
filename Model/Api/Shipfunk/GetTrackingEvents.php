<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

use Magento\Shipping\Model\Tracking\ResultFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory;
use Magento\Framework\HTTP\ZendClientFactory;
use Nord\Shipfunk\Helper\Data as ShipfunkDataHelper;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Locale\Resolver;

/**
 * Class GetTrackingEvents
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class GetTrackingEvents extends AbstractEndpoint implements LoggerAwareInterface
{
    use LoggerAwareTrait;


    /**
     * @var ErrorFactory
     */
    protected $trackErrorFactory;

    /**
     * @var StatusFactory
     */
    protected $trackStatusFactory;

    /**
     * @var ResultFactory
     */
    protected $trackFactory;

    /**
     * GetTrackingEvents constructor.
     *
     * @param LoggerInterface    $logger
     * @param ShipfunkDataHelper $shipfunkDataHelper
     * @param ErrorFactory       $trackErrorFactory
     * @param StatusFactory      $trackStatusFactory
     * @param ResultFactory      $trackFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ShipfunkDataHelper $shipfunkDataHelper,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Framework\Locale\Resolver $localeResolver,
        ErrorFactory $trackErrorFactory,
        StatusFactory $trackStatusFactory,
        ResultFactory $trackFactory
    ) {
        parent::__construct($logger, $shipfunkDataHelper, $httpClientFactory, $localeResolver);

        $this->trackErrorFactory  = $trackErrorFactory;
        $this->trackStatusFactory = $trackStatusFactory;
        $this->trackFactory       = $trackFactory;
        $this->logger             = new NullLogger();
    }
  
    public function execute()
    {
      
    }

    /**
     * @param $trackings
     *
     * @return \Magento\Shipping\Model\Tracking\Result
     */
    public function getResult($trackings)
    {
        if (is_string($trackings)) {
            $trackings = [$trackings];
        }

        $xml       = null;
        $return    = $this->trackFactory->create();
        $resultArr = [];

        foreach ($trackings as $tracking) {

            $result    = $this
                ->setRouteAndFieldname('gettrackingevents_with_code_company')
                ->setRestFormat('/xml/'.$tracking)
                ->get($xml, true);
          
            $resultXml = simplexml_load_string($result->getBody());

            if (strstr($tracking, "/")) {
                $trackingXpld    = explode("/", $tracking);
                $trackingNumber  = $trackingXpld[0];
                $trackingCarrier = $trackingXpld[1];
            } else {
                $trackingNumber  = $tracking;
                $trackingCarrier = "Shipfunk";
            }

            if (isset($resultXml->Error)) {
                $error = $this->trackErrorFactory->create();
                $error->setCarrier($trackingCarrier);
                $error->setCarrierTitle($trackingCarrier);
                $error->setTracking($trackingNumber);
                $error->setErrorMessage("Shipfunk Error (GetTrackingEvents) : " . $resultXml->Error->Message->__toString());
                $this->logger->log(LogLevel::INFO, "Shipfunk Error (GetTrackingEvents) : " . $resultXml->Error->Message->__toString());
                $return->append($error);
            } else {
                $dataCollection = [];
                foreach ($resultXml->tracked->Events as $event) {
                    $data             = [
                        'activity'         => $event->TrackingDescription->__toString(),
                        'deliverydate'     => $event->TrackingDate->__toString(),
                        'deliverytime'     => $event->TrackingTime->__toString(),
                        'deliverylocation' => $event->TrackingPlace->__toString(),
                    ];
                    $dataCollection[] = $data;
                }
                $progress['progressdetail'] = $dataCollection;
                $progress['carrier']        = $trackingCarrier;
                $progress['title']          = $trackingCarrier." ".$resultXml->tracked->ServiceName->__toString();

                $resultArr[$trackingNumber] = $progress;
            }
        }

        foreach ($resultArr as $trackNum => $data) {
            $tracking = $this->trackStatusFactory->create();
            $tracking->setCarrier($data['carrier']);
            $tracking->setCarrierTitle($data['title']);
            $tracking->setTracking($trackNum);
            $tracking->addData($data);
            $return->append($tracking);
        }

        return $return;
    }
}