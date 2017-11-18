<?php

namespace Nord\Shipfunk\Model\Carrier;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Xml\Security;
use Magento\Quote\Api\Data\ShippingMethodExtension;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Item as cartItem;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Shipment\Request;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory as TrackErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Shipping\Model\Tracking\ResultFactory as TrackFactory;
use Nord\Shipfunk\Model\Api\Shipfunk\CreateNewPackageCards;
use Nord\Shipfunk\Model\Api\Shipfunk\DeleteParcels;
use Nord\Shipfunk\Model\Api\Shipfunk\GetDeliveryOptions;
use Nord\Shipfunk\Model\Api\Shipfunk\GetTrackingEvents;
use Nord\Shipfunk\Helper\ParcelHelper;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection;

/**
 * Class Shipfunk
 *
 * @package Nord\Shipfunk\Model\Carrier
 */
class Shipfunk extends AbstractCarrierOnline implements CarrierInterface
{
    /**
     * Code
     *
     * @var string
     */
    protected $_code = 'shipfunk';

    /**
     * @var ProductRepository
     */
    protected $_productRepo;

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
     * @var ParcelHelper
     */
    protected $parcelHelper;

    /**
     * @var State
     */
    protected $_state;

    /**
     * @var DeleteParcels
     */
    protected $DeleteParcels;

    /**
     * @var ShippingMethodExtension
     */
    protected $shippingMethodExtension;
  
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection
     */
    protected $trackCollection;

    /**
     * Shipfunk constructor.
     *
     * @param ErrorFactory            $rateErrorFactory
     * @param ResultFactory           $rateFactory
     * @param MethodFactory           $rateMethodFactory
     * @param Context                 $context
     * @param Security                $xmlSecurity
     * @param ElementFactory          $xmlElFactory
     * @param ProductRepository       $productRepo
     * @param GetDeliveryOptions      $GetDeliveryOptions
     * @param CreateNewPackageCards   $CreateNewPackageCards
     * @param ParcelHelper            $parcelHelper
     * @param State                   $state
     * @param TrackFactory            $trackFactory
     * @param TrackErrorFactory       $trackErrorFactory
     * @param StatusFactory           $trackStatusFactory
     * @param RegionFactory           $regionFactory
     * @param CountryFactory          $countryFactory
     * @param CurrencyFactory         $currencyFactory
     * @param Data                    $directoryData
     * @param StockRegistryInterface  $stockRegistry
     * @param GetTrackingEvents       $GetTrackingEvents
     * @param DeleteParcels           $DeleteParcels
     * @param ShippingMethodExtension $shippingMethodExtension
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection $trackCollection
     * @param array                   $data
     */
    public function __construct(
        ErrorFactory $rateErrorFactory,
        ResultFactory $rateFactory,
        MethodFactory $rateMethodFactory,
        Context $context,
        Security $xmlSecurity,
        ElementFactory $xmlElFactory,
        ProductRepository $productRepo,
        GetDeliveryOptions $GetDeliveryOptions,
        CreateNewPackageCards $CreateNewPackageCards,
        ParcelHelper $parcelHelper,
        State $state,
        TrackFactory $trackFactory,
        TrackErrorFactory $trackErrorFactory,
        StatusFactory $trackStatusFactory,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        CurrencyFactory $currencyFactory,
        Data $directoryData,
        StockRegistryInterface $stockRegistry,
        GetTrackingEvents $GetTrackingEvents,
        DeleteParcels $DeleteParcels,
        ShippingMethodExtension $shippingMethodExtension,
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
        
        $this->_productRepo = $productRepo;
        $this->_state = $state;
        $this->parcelHelper = $parcelHelper;
        $this->shippingMethodExtension = $shippingMethodExtension;
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
        if (!$this->isActive()) {
            return false;
        }

        $result = $this->_rateFactory->create();
        $products = $this->parcelHelper->parseProducts($request);
        $shipfunkResponse = $this->GetDeliveryOptions
                                ->setProducts($products)
                                ->setRequest($request)
                                ->execute();

        $shippingMethods = json_decode($shipfunkResponse->getBody());

        /**
         * if ($shippingMethods->Error) {
         * $error = $this->_rateErrorFactory->create(
         * [
         * 'data' => [
         * 'carrier'       => $this->_code,
         * 'carrier_title' => $this->getConfigData('title'),
         * 'error_message' => $shippingMethods->Error->Message,
         * ],
         * ]
         * );
         * $result->append($error);
         *
         * return $result;
         * }
         */

        $this->_debug($shippingMethods);
        //$this->getSession()->getQuote()->setExtShippingInfo(json_encode($shippingMethods->response));
        //$this->getSession()->getQuote()->save();
        if (isset($shippingMethods->Error)) {
            return $result;
        }
        if (isset($shippingMethods) && isset($shippingMethods->response)) {
            foreach ($shippingMethods->response as $carrierCode => $carrier) {
                foreach ($carrier->Options as $carrierOption) {
                    $method = $this->_rateMethodFactory->create();
                    $method->setCarrier('shipfunk');
                    $method->setMethod(
                        $carrier->Carriercode.'_'.$carrierOption->carriercode
                    );
                    // not visible anywhere but saved in database quote_shipping_rate
                    // to add it on frontend needs plugin on Magento\Quote\Model\Cart\ShippingMethodConverter::modelToDataObject  and possibly extension_attribute for Magento\Quote\Api\Data\ShippingMethodInterface

                    $method->setMethodDescription(
                        $carrierOption->info."||".$carrierOption->category."||".$carrierOption->delivtime . " ".__('days')
                    );
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

    /**
     * Load product from productId
     *
     * @param string $id
     *
     * @return $this
     */
    protected function getProductById($id)
    {
        return $this->_productRepo
            ->getById($id);
    }
}
