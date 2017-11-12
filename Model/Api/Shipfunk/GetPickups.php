<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;
use Nord\Shipfunk\Helper\Data as ShipfunkDataHelper;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Locale\Resolver;

/**
 * Class GetPickups
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class GetPickups extends AbstractEndpoint
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
     *
     * @param LoggerInterface    $logger
     * @param ShipfunkDataHelper $shipfunkDataHelper
     * @param ZendClientFactory  $httpClientFactory
     * @param CheckoutSession    $checkoutSession
     */
    public function __construct(
        LoggerInterface $logger,
        ShipfunkDataHelper $shipfunkDataHelper,
        ZendClientFactory $httpClientFactory,
        CheckoutSession $checkoutSession,
        \Magento\Framework\Locale\Resolver $localeResolver
    ) {
        parent::__construct($logger, $shipfunkDataHelper, $httpClientFactory);
        $this->checkoutSession = $checkoutSession;
        $this->_localeResolver = $localeResolver;
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
          $query = [
             'query' => [
                'order' => [
                    'language' => $this->_getLanguageCode(),
                    'carriercode' => $this->checkoutSession->getQuote()->getSelectedCarrierCode(), // @todo MAYBE THIS SHOULD BE ALLOWED
                    'return_count' => 15 // @todo MAYBE HAVE THIS AS SYS CONFIG
                ],
                'customer' => [
                    'postal_code'     => $this->checkoutSession->getQuote()->getShippingAddress()->getPostcode(),
                    'country'         => $this->checkoutSession->getQuote()->getShippingAddress()->getCountryId()
                ]
             ]
          ];
        } else {
          $query['query']['order']['language'] = $this->_getLanguageCode();
        }
      
        $query = utf8_encode(json_encode($query));
        $this->setEndpoint('get_pickups');
        $result = $this->post($query);
      
        return $result;
    }
}