<?php

namespace Nord\Shipfunk\Model\Carrier;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Rate\Result;
use Nord\Shipfunk\Model\Api\Shipfunk\CreateNewPackageCards;
use Nord\Shipfunk\Model\Api\Shipfunk\DeleteParcels;
use Nord\Shipfunk\Model\Api\Shipfunk\GetDeliveryOptions;
use Nord\Shipfunk\Model\Api\Shipfunk\GetTrackingEvents;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection;

/**
 * Class Shipfunk
 *
 * @package Nord\Shipfunk\Model\Carrier
 */
class Shipfunk extends AbstractCarrierOnline implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * Code
     *
     * @var string
     */
    protected $_code = 'shipfunk';

    /**
     * @var GetDeliveryOptions
     */
    protected $GetDeliveryOptions;

    /**
     * @var CreateNewPackageCards
     */
    protected $CreateNewPackageCards;

    /**
     * @var GetTrackingEvents
     */
    protected $GetTrackingEvents;

    /**
     * @var State
     */
    protected $_state;

    /**
     * @var DeleteParcels
     */
    protected $DeleteParcels;
 
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection
     */
    protected $trackCollection;

    /**
     * Shipfunk constructor.
     *
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Framework\App\Helper\Context                 $context
     * @param \Magento\Framework\Xml\Security                       $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory      $xmlElFactory,
     * @param \Magento\Shipping\Model\Rate\ResultFactory            $rateFactory,
     * @param \Magento\Shipping\Model\Tracking\ResultFactory        $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory  $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory                $regionFactory
     * @param \Magento\Directory\Model\CountryFactory               $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory              $currencyFactory
     * @param \Magento\Directory\Helper\Data                        $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface  $stockRegistry
     * @param GetDeliveryOptions                                    $GetDeliveryOptions
     * @param CreateNewPackageCards                                 $CreateNewPackageCards
     * @param Data                                                  $helper
     * @param State                                                 $state
     * @param GetTrackingEvents                                     $GetTrackingEvents
     * @param DeleteParcels                                         $DeleteParcels
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection $trackCollection
     * @param array                                                 $data
     */
    public function __construct(
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Xml\Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        GetDeliveryOptions $GetDeliveryOptions,
        CreateNewPackageCards $CreateNewPackageCards,
        State $state,
        GetTrackingEvents $GetTrackingEvents,
        DeleteParcels $DeleteParcels,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection $trackCollection,
        array $data = []
    ) {
        parent::__construct(
            $context->getScopeConfig(),
            $rateErrorFactory,
            $context->getLogger(),
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
        
        $this->_state = $state;
        $this->trackCollection = $trackCollection;
        $this->GetDeliveryOptions = $GetDeliveryOptions;
        $this->CreateNewPackageCards = $CreateNewPackageCards;
        $this->DeleteParcels = $DeleteParcels;
        $this->GetTrackingEvents = $GetTrackingEvents;
    }

    /**
     * Generates list of allowed carrier`s shipping methods
     * Displays on cart price rules page
     *
     * @return array
     * @api
     */
    public function getAllowedMethods()
    {
        return [$this->getCarrierCode() => __($this->getConfigData('name'))];
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     * @api
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Check if carrier has shipping label option available
     *
     * @return bool
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }

    /**
     * Collect rates
     *
     * @param RateRequest $request
     *
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->canCollectRates()) {
            return $this->getErrorMessage();
        }

        $result = $this->_rateFactory->create();
        $shipfunkResponse = $this->GetDeliveryOptions->setRequest($request)->execute();
        $shippingMethods = json_decode($shipfunkResponse->getBody());
        $this->_debug($shippingMethods);
        if (isset($shippingMethods->Error)) {
            return $this->getErrorMessage();
        }
        if (isset($shippingMethods) && isset($shippingMethods->response)) {
            foreach ($shippingMethods->response as $carrierCode => $carrier) {
                foreach ($carrier->Options as $carrierOption) {
                    $method = $this->_rateMethodFactory->create();
                    $method->setCarrier('shipfunk');
                    $method->setMethod($carrier->Carriercode.'_'.$carrierOption->carriercode);
                    // not visible anywhere but saved in database quote_shipping_rate
                    // to add it on frontend has plugin on Magento\Quote\Model\Cart\ShippingMethodConverter::modelToDataObject and extension_attribute for Magento\Quote\Api\Data\ShippingMethodInterface
                    $method->setMethodDescription($carrierOption->info."||".$carrierOption->category."||".$carrierOption->delivtime . " ".__('days'));
                    $method->setCarrierTitle($carrier->Companyname);
                    $method->setMethodTitle($carrierOption->productname);
                    $method->setPrice($carrierOption->customer_price);
                    $method->setCost($carrierOption->calculated_price);
                    $result->append($method);
                }
            }

            return $result;
        }
    }

    /**
     * Disable on admin side
     * @return bool
     */
    public function isActive()
    {
        if ($this->_state->getAreaCode() == FrontNameResolver::AREA_CODE) {
            return false;
        }

        return parent::isActive();
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     *
     * @return Result|null
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }
      
        $return    = $this->_trackFactory->create();
        $resultArr = [];
      
        foreach ($trackings as $trackingCode) {
            $trackModel = $this->trackCollection->addFieldToFilter('track_number', $trackingCode)->getFirstItem();
            $orderId = $trackModel->getOrderId();
            $shipfunkResponse = $this->GetTrackingEvents->setTrackingCode($trackingCode)->setOrderId($orderId)->execute();
            $result = json_decode($shipfunkResponse->getBody());
            $this->_debug($result);
            if (isset($result->Error) || isset($result->Info)) {
                $error = $this->_trackErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setTracking($trackingCode);
                if (isset($result->Error)) {
                  $message = $result->Error->Message;
                } else {
                  $message = $result->Info->Message;
                }
                $error->setErrorMessage("Shipfunk Error (GetTrackingEvents) : " . $message);
                $return->append($error);
            } else {
                $dataCollection = [];
                foreach ($result->response->tracked as $tracked) {
                    foreach ($tracked->Events as $event) {
                        $data = [
                            'activity'         => $event->TrackingDescription,
                            'deliverydate'     => $event->TrackingDate,
                            'deliverytime'     => $event->TrackingTime,
                            'deliverylocation' => $event->TrackingPlace,
                        ];
                        $dataCollection[] = $data;
                    }
                    $progress['progressdetail'] = $dataCollection;
                    //$progress['carrier']        = $trackingCarrier;
                    $progress['title']          = $tracked->ServiceName;
                    $resultArr[$trackingCode] = $progress;
                }
            }
        }
      
        foreach ($resultArr as $trackNum => $data) {
            $tracking = $this->_trackStatusFactory->create();
            $tracking->setCarrier($this->_code);
            $tracking->setCarrierTitle($data['title']);
            $tracking->setTracking($trackNum);
            $tracking->addData($data);
            $return->append($tracking);
        }

        return $return;
    }
  
    public function requestToShipment($request)
    {
        $orderId = $request->getOrderShipment()->getOrder()->getRealOrderId();
        $this->DeleteParcels->setOrderId($orderId)->execute();
        return parent::requestToShipment($request);
    }

    /**
     * Sending the single CreateNewPackageCards API request for each Magento package
     *
     * @return DataObject
     */
    protected function _doShipmentRequest(DataObject $request)
    {
        $this->_prepareShipmentRequest($request);
        $this->_debug(var_export($request->debug(), true));
        $orderId = $request->getOrderShipment()->getOrder()->getRealOrderId();
        $packages = [
          $request->getPackageId() => [
            'params' => $request->getPackageParams()->toArray(),
            'items' => $request->getPackageItems()
          ]
        ];
        $shipfunkResponse = $this->CreateNewPackageCards
                              ->setPackages($packages)
                              ->setOrderId($orderId)
                              ->setRequest($request)
                              ->execute();
        $shipfunkResponse = json_decode($shipfunkResponse->getBody());
        $this->_debug($shipfunkResponse);
      
        $response = new DataObject();
        if (isset($shipfunkResponse->Error)) {
          $response->setError(true);
          // $errorMessage = __("shipfunk_error_7");
          $response->setErrors($shipfunkResponse->Error->Message); // @todo Check if error messages are returned in correct language
          $response->setMessage($shipfunkResponse->Error->Message);
        } else {
           if (isset($shipfunkResponse->response) && isset($shipfunkResponse->response->parcels)) {
              $parcelInformation = $shipfunkResponse->response->parcels[0];
              $sendTrCode = $parcelInformation->send_trcode;
              $sendCard = base64_decode($parcelInformation->send_card);
              $response->setTrackingNumber($sendTrCode);
              $response->setShippingLabelContent($sendCard);
           }
        }
        $response->setGatewayResponse(json_encode($shipfunkResponse));
        return $response;
    }

    /**
     * Return container types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getContainerTypes(DataObject $params = null)
    {
        return [];
    }
}
