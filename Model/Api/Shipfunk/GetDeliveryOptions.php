<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;
use Nord\Shipfunk\Helper\Data as ShipfunkDataHelper;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

/**
 * Class GetDeliveryOptions
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class GetDeliveryOptions extends AbstractEndpoint
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
  
    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_localeResolver;
  
    /**
     * @var PriceHelper
     */
    protected $_priceHelper;
  
    /**
     *
     * @param LoggerInterface    $logger
     * @param ShipfunkDataHelper $shipfunkDataHelper
     * @param ZendClientFactory  $httpClientFactory
     * @param CheckoutSession    $checkoutSession
     * @param PriceHelper        $priceHelper
     */
    public function __construct(
        LoggerInterface $logger,
        ShipfunkDataHelper $shipfunkDataHelper,
        ZendClientFactory $httpClientFactory,
        CheckoutSession $checkoutSession,
        \Magento\Framework\Locale\Resolver $localeResolver,
        PriceHelper        $priceHelper
    ) {
        parent::__construct($logger, $shipfunkDataHelper, $httpClientFactory);
        $this->checkoutSession = $checkoutSession;
        $this->_localeResolver = $localeResolver;
        $this->_priceHelper = $priceHelper;
    }
  
    protected function _getLanguageCode()
    {
        $currentLocale = $this->_localeResolver->getLocale();
        $currentLocaleArray = explode('_', $currentLocale);
        return array_pop($currentLocaleArray);
    }
  
    public function execute($query = [])
    {
        if (!$query) {
          $request = $this->getRequest();
          $query = [
             'query' => [
                'order' => [
                    'language' => $this->_getLanguageCode(),
                    'monetary' => [
                        'currency' => $request->getPackageCurrency()->getCurrencyCode(),
                        'value' => $this->_priceHelper->currency($request->getBaseSubtotalInclTax(), false, false)
                    ],
                    'get_pickups' => [
                        'store' => 1,
                        'store_only' => 0,
                        'transport_company' => 0
                    ],
                    'products' => $this->_getProducts($request)
                ],
                'customer' => [
                    'postal_code'     => $request->getDestPostcode(),
                    'country'         => $request->getDestCountryId()
                ]
             ]
          ];
        }
        $query = utf8_encode(json_encode($query));
        $quoteId = $this->checkoutSession->getQuote()->getId();
        $result = $this->setEndpoint('get_delivery_options')
                      ->setOrderId($quoteId)
                      ->post($query);
      
        return $result;
    }
  
    /*
     * @param RateRequest $request
     * @return array
     */
    protected function _getProducts($request)
    {
        $products = [];
        foreach ($request->getAllItems() as $item) {
            // get the info only from child products, since dimensions and weight might be different based on configuration
            if ($item->getHasChildren()) {
                continue;
            }
            $product = $item->getProduct();
            if (!$product->isVirtual()) {
                $products[] = [
                    'amount' => $item->getQty(),
                    'code' => $product->getSku(),
                    'name' => $product->getName(),
                    'weight'     => [
                        'unit'  => $this->helper->getConfigData('weight_unit'),
                        'amount'  => $this->_getProductValue($product, 'weight')
                    ],
                    'dimensions'     => [
                        'unit' => $this->helper->getConfigData('dimensions_unit'),
                        'width' => $this->_getProductValue($product, 'width'),
                        'depth' => $this->_getProductValue($product, 'depth'),
                        'height' => $this->_getProductValue($product, 'height')
                    ]
                ];
            }
        }
      
        return $products;
    }
  
    /**
     * @return mixed
     */
    protected function _getProductValue($product, $attribute)
    {
        $mageAttribute = $this->helper->getConfigData($attribute . '_mapping');
        $attributeValue = $product->getData($mageAttribute);
        $value = is_numeric($attributeValue) && $attributeValue ? $attributeValue : $this->helper->getConfigData($attribute . '_default');
        return $value;
    }
}