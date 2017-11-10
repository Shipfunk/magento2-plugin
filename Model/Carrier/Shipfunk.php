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
use Nord\Shipfunk\Model\BoxPacker\Box;
use Nord\Shipfunk\Model\BoxPacker\ShipfunkPacker;

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
     * @var ShipfunkPacker
     */
    protected $packer;

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
     * Shipfunk constructor.
     *
     * @param ErrorFactory            $rateErrorFactory
     * @param ResultFactory           $rateFactory
     * @param MethodFactory           $rateMethodFactory
     * @param Context                 $context
     * @param Security                $xmlSecurity
     * @param ElementFactory          $xmlElFactory
     * @param ProductRepository       $productRepo
     * @param ShipfunkPacker          $packer
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
        ShipfunkPacker $packer,
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
        $this->packer = $packer;
        $this->parcelHelper = $parcelHelper;
        $this->shippingMethodExtension = $shippingMethodExtension;

        $this->GetDeliveryOptions = $GetDeliveryOptions;
        $this->CreateNewPackageCards = $CreateNewPackageCards;
        $this->DeleteParcels = $DeleteParcels;
        $this->GetTrackingEvents = $GetTrackingEvents;

        $this->getBoxDimensions();
    }

    /**
     * {@inheritdoc}
     */
    public function getBoxDimensions()
    {
        $parcels = $this->getConfigData('parcels');

        foreach ($parcels as $item) {
            $this->packer->addBox(
                new Box(
                    $item['parcel_name'],
                    $item['outer_width'],
                    $item['outer_length'],
                    $item['outer_depth'],
                    $item['empty_weight'],
                    $item['inner_width'],
                    $item['inner_length'],
                    $item['inner_depth'],
                    $item['max_weight']
                )
            );
        }
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
                        $carrier->Carriercode.'_'.$carrierOption->carriercode // .'_'.$carrierOption->productcode
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
     * Get tracking information
     *
     * @param string $tracking
     *
     * @return string|false
     * @api
     */
    public function getTrackingInfo($tracking)
    {
        $result = $this->getTracking($tracking);

        if ($result instanceof \Magento\Shipping\Model\Tracking\Result) {
            $trackings = $result->getAllTrackings();
            if ($trackings) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
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
        return $this->GetTrackingEvents->getResult($trackings);
    }

    /**
     * Do request to shipment
     *
     * @param Request $request
     *
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function requestToShipment($request)
    {
        $this->deleteParcels($request->getOrderShipment()->getOrder()->getRealOrderId());

        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            throw new LocalizedException(__('No packages for request'));
        }
        if ($request->getStoreId() != null) {
            $this->setStore($request->getStoreId());
        }

        $data = [];
        $result = null;

        foreach ($packages as $packageId => $package) {
            $request->setPackageId($packageId);
            $request->setPackagingType($package['params']['container']);
            $request->setPackageWeight($package['params']['weight']);
            $request->setPackageParams(new DataObject($package['params']));
            $request->setPackageItems($package['items']);
            $result = $this->_doShipmentRequest($request);

            if ($result->hasErrors()) {
                $this->rollBack($data);
                break;
            } else {
                $data[] = [
                    'tracking_number' => $result->getTrackingNumber(),
                    'label_content'   => $result->getShippingLabelContent(),
                ];
            }
            if (!isset($isFirstRequest)) {
                $request->setMasterTrackingId($result->getTrackingNumber());
                $isFirstRequest = false;
            }
        }

        $response = new DataObject(['info' => $data]);
        if ($result->getErrors()) {
            $response->setErrors($result->getErrors());
        }

        return $response;
    }

    /**
     * @param $orderId
     *
     * @return $this
     */
    public function deleteParcels($orderId)
    {
        $this->DeleteParcels->setOrderId($orderId)->removeAllParcels();

        return $this;
    }

    /**
     * @param DataObject $request
     *
     * @return DataObject
     */
    protected function _doShipmentRequest(DataObject $request)
    {
        $this->_debug('WITH Shipping Labels -- Listen to Mage Packages');
        $this->_prepareShipmentRequest($request);

        $this->CreateNewPackageCards
            ->setRequest($request);
        $response = $this->CreateNewPackageCards->getResult();

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

        $boxPackerBoxes = $this->packer->getBoxes();
        $boxes = [''];

        while (!$boxPackerBoxes->isEmpty()) {
            $box = $boxPackerBoxes->extract();

            if (!in_array($box->getReference(), $boxes)) {
                $boxes[] = $box->getReference();
            }
        }

        return $boxes;
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
