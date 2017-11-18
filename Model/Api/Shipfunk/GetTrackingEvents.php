<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

use Magento\Framework\Locale\Resolver;
use Magento\Framework\HTTP\ZendClientFactory;
use Nord\Shipfunk\Helper\Data as ShipfunkDataHelper;
use Psr\Log\LoggerInterface;

/**
 * Class GetTrackingEvents
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class GetTrackingEvents extends AbstractEndpoint
{ 
    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_localeResolver;

    /**
     *
     * @param LoggerInterface    $logger
     * @param ShipfunkDataHelper $shipfunkDataHelper
     * @param ZendClientFactory $httpClientFactory
     * @param Resolver $localeResolver
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ShipfunkDataHelper $shipfunkDataHelper,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Framework\Locale\Resolver $localeResolver
    ) {
        parent::__construct($logger, $shipfunkDataHelper, $httpClientFactory);
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
          $trackingCode = $this->getTrackingCode();
          $query = [
             'query' => [
                'order' => [
                    'tracking_code' => $trackingCode,
                    'language' => $this->_getLanguageCode()
                ]
             ]
          ];
        }
    
        $query = utf8_encode(json_encode($query));
        $result = $this->setEndpoint('get_tracking_events')->get($query);
      
        return $result;
    }
}