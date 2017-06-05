<?php

namespace Nord\Shipfunk\Model\Carrier;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
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
use Nord\Shipfunk\Helper\UnitConverter;
use Nord\Shipfunk\Model\Api\Shipfunk\CreateNewPackageCards;
use Nord\Shipfunk\Model\Api\Shipfunk\DeleteParcels;
use Nord\Shipfunk\Model\Api\Shipfunk\GetDeliveryOptions;
use Nord\Shipfunk\Model\Api\Shipfunk\GetTrackingEvents;
use Nord\Shipfunk\Model\Api\Shipfunk\Helper\ParcelHelper;
use Nord\Shipfunk\Model\Api\Shipfunk\OrderPaid;
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
     * @var Context
     */
    protected $_context;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Attribute
     */
    protected $_eavAttribute;

    /**
     * @var int
     */
    protected $weight;

    /**
     * @var int
     */
    protected $length;

    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var UnitConverter
     */
    protected $unitConverter;

    /**
     * @var mixed
     */
    protected $product;

    /**
     * @var array
     */
    protected $products;

    /**
     * @var ShipfunkPacker
     */
    protected $packer;

    /**
     * @var Http
     */
    protected $httpRequest;

    /**
     * @var RateRequest
     */
    protected $rateRequest;

    /**
     * @var OrderPaid
     */
    protected $OrderPaid;

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
     * @param Attribute               $eavAttribute
     * @param ProductRepository       $productRepo
     * @param ShipfunkPacker          $packer
     * @param UnitConverter           $unitConverter
     * @param OrderPaid               $OrderPaid
     * @param GetDeliveryOptions      $GetDeliveryOptions
     * @param CreateNewPackageCards   $CreateNewPackageCards
     * @param ParcelHelper            $parcelHelper
     * @param Http                    $httpRequest
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
        Attribute $eavAttribute,
        ProductRepository $productRepo,
        ShipfunkPacker $packer,
        UnitConverter $unitConverter,
        OrderPaid $OrderPaid,
        GetDeliveryOptions $GetDeliveryOptions,
        CreateNewPackageCards $CreateNewPackageCards,
        ParcelHelper $parcelHelper,
        Http $httpRequest,
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
        $this->_context = $context;
        $this->_state = $state;
        $this->_eavAttribute = $eavAttribute;
        $this->packer = $packer;
        $this->unitConverter = $unitConverter;
        $this->httpRequest = $httpRequest;
        $this->parcelHelper = $parcelHelper;
        $this->shippingMethodExtension = $shippingMethodExtension;

        $this->OrderPaid = $OrderPaid;
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
        return false;
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

        $this->setRateRequest($request);

        $result = $this->_rateFactory->create();

        if (!$request->getDestStreet()) {
            return $result;
        }
        // THE REQUEST NEEDS TO GET VALIDATED HERE BEFORE TRYING TO CONTACT SHIPFUNK
        // SHIPFUNK REQUIRES AN ADDRESS, BUT MAGE DOESN'T INCLUDE STREET ADDRESS IN SHIPPING REQUEST BY DEFAULT
        //$simulate = json_decode('{"response":{"90000000":{"Carriercode":"90000000","Companyname":"Shipfunk","Options":[{"carriercode":90000002,"productname":"Kotiinkuljetus","productcode":"0000002","realprice":"106.04","discounted_price":"106.04","delivtime":"3-4","info":"Vastaanottajalle ilmoitetaan saapuneesta paketista puhelimitse ja sovitaan jakeluaika.","green_delivery":false,"category":null,"normal_delivery":false,"home_delivery":true,"express_oneday_delivery":false,"express_sameday_delivery":false,"express_sameday_final_ordertime":false,"announcement":null,"haspickups":false},{"carriercode":90000001,"productname":"Noutopistetoimitus","productcode":"0000001","realprice":"100.66","discounted_price":"100.66","delivtime":"3-4","info":"Paketti noudetaan saapumisilmoituksessa ilmoitetusta toimipisteestä. Paketti luovutetaan henkilötodistusta vastaan. Henkilöllisyyden voi todistaa ajokortilla, passilla tai poliisin myöntämällä henkilökortilla. Jos paketin noutaja on muu kuin osoitekorttiin merkitty vastaanottaja, täytyy paketin noutajalla olla mukana henkilötodistuksensa lisäksi valtakirja.","green_delivery":false,"category":null,"normal_delivery":true,"home_delivery":false,"express_oneday_delivery":false,"express_sameday_delivery":false,"express_sameday_final_ordertime":false,"announcement":null,"haspickups":false}]}}}');
        //$this->_debug($simulate);

        $shippingMethods = $this->getShipfunkShippingMethods();

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
        // NEEDS TO BE FIXED, SAVES ALL methods in quote_shipping_rate table, INSTEAD only selected one
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
                        $carrier->Carriercode.'_'.$carrierOption->carriercode.'_'.$carrierOption->productcode
                    );
                    // not visible anywhere but saved in database quote_shipping_rate
                    // to add it on frontend needs plugin on Magento\Quote\Model\Cart\ShippingMethodConverter::modelToDataObject  and possibly extension_attribute for Magento\Quote\Api\Data\ShippingMethodInterface

                    $method->setMethodDescription(
                        $carrierOption->info."||".$carrierOption->category."||".$carrierOption->delivtime . " ".__('days')
                    );
                    $method->setCarrierTitle($carrier->Companyname);
                    $method->setMethodTitle($carrierOption->productname);
                    $method->setPrice($carrierOption->discounted_price);
                    $method->setCost($carrierOption->realprice);
                    $result->append($method);
                }
            }

            return $result;
        }
    }

    /**
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
     * {@inheritdoc}
     */
    protected function getShipfunkShippingMethods()
    {
        $request = $this->getRateRequest();
        $items = $request->getAllItems();

        $packedBoxes = $this->parcelHelper->packWithBoxPacker($items, $request);

        /** @noinspection PhpUndefinedMethodInspection */
        $result = $this->GetDeliveryOptions
            ->setParcels($packedBoxes)
            ->setProducts($this->products)
            ->setOrder($request->getAllItems()[0]->getQuote())
            ->setRequest($request)
            ->getResult();


        return json_decode($result->body);
    }

    /**
     * @return RateRequest
     */
    public function getRateRequest()
    {
        return $this->rateRequest;
    }

    /**
     * @param RateRequest $rateRequest
     *
     * @return $this
     */
    public function setRateRequest($rateRequest)
    {
        $this->rateRequest = $rateRequest;

        return $this;
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
