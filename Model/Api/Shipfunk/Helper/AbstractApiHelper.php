<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk\Helper;

use Magento\Framework\View\Element\Template\Context,
    SimpleXMLElement,
    Magento\Framework\HTTP\ZendClient,
    Magento\Framework\HTTP\ZendClientFactory,
    Magento\Quote\Model\Quote,
    DVDoug\BoxPacker\PackedBoxList,
    Psr\Log\LoggerInterface,
    Nord\Shipfunk\Helper\Data as ShipfunkDataHelper,
    Magento\Sales\Model\Order,
    Magento\Framework\DataObject;
use Magento\Shipping\Model\Shipment\Request;

/**
 * Class ApiHelper
 *
 * @package Nord\Shipfunk\Helper
 */
class AbstractApiHelper extends \Magento\Framework\DataObject
{
    /**
     * Code
     *
     * @var string
     */
    protected $_code = 'shipfunk';

    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * @var string
     */
    protected $headers;

    /**
     * @var string
     */
    protected $route;

    /**
     * @var array
     */
    protected $webshop;

    /**
     * @var array
     */
    protected $customer;

    /**
     * @var SimpleXMLElement
     */
    protected $simpleXml;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $pass;

    /**
     * @var mixed
     */
    protected $args;

    /**
     * @var array
     */
    protected $products;

    /**
     * @var Quote
     */
    protected $order;

    /**
     * @var Order\Shipment
     */
    protected $orderShipment;

    /**
     * @var Quote\Address\RateRequest
     */
    protected $request;

    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var string
     */
    protected $fieldname;

    /**
     * @var array
     */
    protected $parcels;

    /**
     * @var ShipfunkDataHelper
     */
    protected $helper;

    /**
     * @var string
     */
    protected $quoteId;

    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $cardDirection;

    /**
     * @var string
     */
    protected $format;

    /**
     * @var string
     */
    protected $dpi;

    /**
     * @var string
     */
    protected $size;

    /**
     * @var string
     */
    protected $reversed;

    /**
     * @var string
     */
    protected $carrierCode;

    /**
     * @var string
     */
    protected $productCode;

    /**
     * @var float
     */
    protected $realPrice;

    /**
     * @var float
     */
    protected $discountedPrice;

    /**
     * @var string
     */
    protected $pickupId;

    /**
     * @var string
     */
    protected $restFormat = "/xml/json";
  
    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $_httpClientFactory;
  
    /**
     * AbstractApiHelper constructor.
     *
     * @param Context            $context
     * @param ShipfunkDataHelper $shipfunkDataHelper
     * @param CustomerHelper     $customerHelper
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ShipfunkDataHelper $shipfunkDataHelper,
        CustomerHelper $customerHelper,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
    ) {
        $this->customerHelper = $customerHelper;
        $this->helper         = $shipfunkDataHelper;
        $this->log            = $logger;
        $this->_httpClientFactory = $httpClientFactory;

        $this->setLogin(
            $this->helper->getConfigData('shipfunk_username'),
            $this->helper->getConfigData('shipfunk_password')
        );
    }

    /**
     * @param string $id
     * @param string $pass
     *
     * @return $this
     */
    public function setLogin($id, $pass)
    {
        $this->id   = $id;
        $this->pass = $pass;

        return $this;
    }

    /**
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @return string
     */
    public function getWebshopId()
    {
        return $this->id;

    }

    /**
     * @return array
     */
    public function getWebshop()
    {
        return [
            'webshop' => [
                'id'   => $this->getWebshopId(),
                'pass' => $this->getPass(),
            ],
        ];
    }

    /**
     * @param bool $includeWebshopId
     *
     * @return string
     */
    public function getApiUrl($includeWebshopId = false)
    {
        $environmentUrl = $this->helper->getConfigData('shipfunk_url');
        if ($includeWebshopId) {
            return $environmentUrl.$this->getRoute()."/".$this->getWebshopId().$this->getRestFormat();
        }

        return $environmentUrl.$this->getRoute().$this->getRestFormat();
      
      
        // return $this->helper->getConfigData('api_url') . $this->getRoute() . '/' . 'true' . '/' . $this->getRestFormat() . '/' . $this->getOrderId();
    }

    /**
     * @return CustomerHelper
     */
    public function getCustomerHelper()
    {
        return $this->customerHelper;
    }

    /**
     * @param CustomerHelper $customerHelper
     *
     * @return $this
     */
    public function setCustomerHelper($customerHelper)
    {
        $this->customerHelper = $customerHelper;

        return $this;
    }

    /**
     * @param string $fieldname
     *
     * @return $this
     */
    public function setFieldname($fieldname)
    {
        $this->fieldname = $fieldname;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldname()
    {
        return $this->fieldname;
    }

    /**
     * @param string $var
     *
     * @return $this
     */
    public function setRouteAndFieldname($var)
    {
        $this->fieldname = $var;
        $this->route     = $var;

        return $this;
    }

    /**
     * @param $restFormat
     *
     * @return $this
     */
    public function setRestFormat($restFormat)
    {
        $this->restFormat = $restFormat;

        return $this;
    }

    /**
     * @return string
     */
    public function getRestFormat()
    {
        return $this->restFormat;
    }

    /**
     * @return Quote|Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Quote|Order $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @param Quote\Address\RateRequest|DataObject|Request $request
     *
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return Quote\Address\RateRequest|DataObject|Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return PackedBoxList
     */
    public function getBoxes()
    {
        return $this->args;
    }

    /**
     * @param PackedBoxList $args
     *
     * @return $this
     */
    public function setBoxes($args)
    {
        $this->args = $args;

        return $this;
    }

    /**
     * @return Order\Shipment
     */
    public function getOrderShipment()
    {
        return $this->orderShipment;
    }

    /**
     * @param Order\Shipment $orderShipment
     *
     * @return AbstractApiHelper
     */
    public function setOrderShipment($orderShipment)
    {
        $this->orderShipment = $orderShipment;

        return $this;
    }


    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param array $products
     *
     * @return $this
     */
    public function setProducts($products)
    {
        $this->products = $products;

        return $this;
    }



    /**
     * @return array
     */
    public function getParcels()
    {
        return $this->parcels;
    }

    /**
     * @param array $parcels
     *
     * @return $this
     */
    public function setParcels($parcels)
    {
        $this->parcels = $parcels;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param string $route
     *
     * @return $this
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeaders()
    {
        return ['Accept' => 'application/json'];

    }

    /**
     * @return string
     */
    public function getQuoteId()
    {
        return $this->quoteId;
    }

    /**
     * @param int $quoteId
     *
     * @return $this
     */
    public function setQuoteId($quoteId)
    {
        $this->quoteId = $quoteId;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCardDirection()
    {
        return $this->cardDirection;
    }

    /**
     * @param string $cardDirection
     *
     * @return $this
     */
    public function setCardDirection($cardDirection)
    {
        $this->cardDirection = $cardDirection;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     *
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return string
     */
    public function getDpi()
    {
        return $this->dpi;
    }

    /**
     * @param string $dpi
     *
     * @return $this
     */
    public function setDpi($dpi)
    {
        $this->dpi = $dpi;

        return $this;
    }

    /**
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param string $size
     *
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return string
     */
    public function getReversed()
    {
        return $this->reversed;
    }

    /**
     * @param string $reversed
     *
     * @return $this
     */
    public function setReversed($reversed)
    {
        $this->reversed = $reversed;

        return $this;
    }

    /**
     * @return string
     */
    public function getCarrierCode()
    {
        return $this->carrierCode;
    }

    /**
     * @param string $carrierCode
     *
     * @return $this
     */
    public function setCarrierCode($carrierCode)
    {
        $this->carrierCode = $carrierCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductCode()
    {
        return $this->productCode;
    }

    /**
     * @param string $productCode
     *
     * @return $this
     */
    public function setProductCode($productCode)
    {
        $this->productCode = $productCode;

        return $this;
    }

    /**
     * @return float
     */
    public function getRealPrice()
    {
        return $this->realPrice;
    }

    /**
     * @param float $realPrice
     *
     * @return $this
     */
    public function setRealPrice($realPrice)
    {
        $this->realPrice = $realPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountedPrice()
    {
        return $this->discountedPrice;
    }

    /**
     * @param float $discountedPrice
     *
     * @return $this
     */
    public function setDiscountedPrice($discountedPrice)
    {
        $this->discountedPrice = $discountedPrice;

        return $this;
    }

    /**
     * @return string
     */
    public function getPickupId()
    {
        return $this->pickupId;
    }

    /**
     * @param string $pickupId
     *
     * @return AbstractApiHelper
     */
    public function setPickupId($pickupId)
    {
        $this->pickupId = $pickupId;

        return $this;
    }

    /**
     * @return array
     */
    public function getCustomer()
    {
        //$customerHelper = $this->getCustomerHelper();

        //$customerHelper->setOrderData($this->getOrder());
        //$customerHelper->setRequest($this->getRequest());

        /** @noinspection PhpUndefinedMethodInspection */
        $customer = [
            'customer' => [
                'fname'      => $this->getRequest()->getDestFirstname(),
                'lname'      => $this->getRequest()->getDestLastname(),
                'streetaddr' => $this->getRequest()->getDestStreet(),
                'postal'     => $this->getRequest()->getDestPostcode(),
                'city'       => $this->getRequest()->getDestCity(),
                'country'    => $this->getRequest()->getDestCountryId(),
                'phone'      => $this->getRequest()->getDestTelephone(),
                'email'      => $this->getRequest()->getDestEmail(),
            ],
        ];

        return $customer;
    }

    /**
     * @return SimpleXMLElement
     */
    public function getSimpleXml()
    {
        return $this->simpleXml;
    }

    /**
     * @return $this
     */
    public function setSimpleXml()
    {
        $this->simpleXml = new SimpleXMLElement("<?xml version=\"1.0\"  encoding=\"UTF-8\" ?><query></query>");

        return $this;
    }

    /**
     * @param array            $data
     * @param SimpleXMLElement &$simpleXml
     * @param bool|string      $root
     *
     * @return void
     */
    protected function appendToXml($data, &$simpleXml, $root = false)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item'.$key;
            }
            if (is_array($value)) {
                if ($root) {
                    $subnode = $simpleXml->$root->addChild($key);
                    $this->appendToXml($value, $subnode);
                } else {
                    $subnode = $simpleXml->addChild($key);
                    $this->appendToXml($value, $subnode);
                }

            } else {
                $simpleXml->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @param bool             $transmitWebshopId
     *
     * @return \Zend_Http_Response
     */
    protected function post($xml, $transmitWebshopId = false)
    {
        $data = ['sf_'.$this->getFieldname() => $xml];
        $client = $this->_httpClientFactory->create();
        $client->setUri((string) $this->getApiUrl($transmitWebshopId));
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $client->setHeaders($this->getHeaders());
        $client->setParameterPost($data);
        $result = $client->request(\Magento\Framework\HTTP\ZendClient::POST);
      
        return $result;
    }

    /**
     * @param  SimpleXMLElement $xml
     * @param bool              $transmitWebshopId
     *
     * @return \Zend_Http_Response
     */
    protected function get($xml, $transmitWebshopId = false)
    {
        $data = ['sf_'.$this->getFieldname() => $xml];
      
        $client = $this->_httpClientFactory->create();
        $client->setUri((string) $this->getApiUrl($transmitWebshopId));
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $client->setHeaders($this->getHeaders());
        $client->setParameterGet($data);
        $result = $client->request(\Magento\Framework\HTTP\ZendClient::GET);
      
        return $result;
    }
}