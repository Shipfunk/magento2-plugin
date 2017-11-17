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
                        'value' => $this->_priceHelper->currency($request->getBaseSubtotalInclTax(), false, false) // @todo BUG WITH DIFFERENT BASE CURRENCY
                    ],
                    'get_pickups' => [ // get stores and carriers but without carrier pickup points
                        'store' => 1,
                        'store_only' => 0,
                        'transport_company' => 0
                    ],
                    'products' => $this->getProducts()
                ],
                'customer' => [ // we'll be sending the rest later
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
}