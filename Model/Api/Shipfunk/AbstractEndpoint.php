<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Psr\Log\LoggerInterface;
use Nord\Shipfunk\Helper\Data as ShipfunkDataHelper;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Shipment\Request;

/**
 * Abstract endpoint call
 * @todo Exceptions handeling
 */
abstract class AbstractEndpoint extends \Magento\Framework\DataObject
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var ShipfunkDataHelper
     */
    protected $helper;

    /**
     * @var ZendClientFactory
     */
    protected $_httpClientFactory;

    /**
     * AbstractEndpoint constructor.
     *
     * @param LoggerInterface $logger
     * @param ShipfunkDataHelper $shipfunkDataHelper
     * @param ZendClientFactory $httpClientFactory
     */
    public function __construct(
        LoggerInterface $logger,
        ShipfunkDataHelper $shipfunkDataHelper,
        ZendClientFactory $httpClientFactory
    ) {
        $this->helper         = $shipfunkDataHelper;
        $this->_logger            = $logger;
        $this->_httpClientFactory = $httpClientFactory;
      
        $this->setHeaders([
          'Accept' => 'application/json',
          'Authorization' => $this->helper->getConfigData('test_mode') ? $this->helper->getConfigData('test_api_key') : $this->helper->getConfigData('live_api_key')
        ]);
    }
  
    /**
     * Execute the Shipfunk API endpoint call and return the result
     *
     * @param array $query
     * @return \Zend_Http_Response
     */
    abstract public function execute($query = []);

    /**
     * @return string
     */
    public function getApiUrl()
    {
        // v.1.2
        if ($this->getEndpoint()) {
          return $this->helper->getConfigData('api_url') . $this->getEndpoint() .  '/' . 'true' . '/' . $this->getRequestType() . '/' . $this->getReturnType() . '/' . $this->getOrderId();
        }            
    }
  
    /**
     * Make json default request type 
     * @return string
     */
    public function getRequestType()
    {
        return $this->getData('request_type') ?: 'json';
    }

    /**
     * Make json default return type
     * @return string
     */
    public function getReturnType()
    {
        return $this->getData('return_type') ?: 'json';
    }

    /**
     * @param string $requestData
     *
     * @return \Zend_Http_Response
     */
    protected function post($requestData)
    {
        $data = ['sf_' . $this->getEndpoint() => $requestData];
        $url = (string) $this->getApiUrl();
        $headers = $this->getHeaders();
        $debugData = ['request' => $data, 'url' => $url, 'headers' => $headers];
        $client = $this->_httpClientFactory->create();
        $client->setUri($url);
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $client->setHeaders($headers);
        $client->setParameterPost($data);
        $result = $client->request(\Magento\Framework\HTTP\ZendClient::POST);
        $debugData['response'] = $result->getBody();
        $this->_debug($debugData);
        return $result;
    }

    /**
     * @param  string $requestData
     *
     * @return \Zend_Http_Response
     */
    protected function get($requestData)
    {
        $data = ['sf_' . $this->getEndpoint() => $requestData];
        $url = (string) $this->getApiUrl();
        $headers = $this->getHeaders();
        $debugData = ['request' => $data, 'url' => $url, 'headers' => $headers];
        $client = $this->_httpClientFactory->create();
        $client->setUri($url);
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $client->setHeaders($headers);
        $client->setParameterGet($data);
        $result = $client->request(\Magento\Framework\HTTP\ZendClient::GET);
        $debugData['response'] = $result->getBody();
        $this->_debug($debugData);
        return $result;
    }
  
    /**
     * Log debug data to file
     *
     * @param mixed $debugData
     * @return void
     */
    protected function _debug($debugData)
    {
        if ($this->getDebugFlag()) {
            $this->_logger->debug(var_export($debugData, true));
        }
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     */
    public function getDebugFlag()
    {
        return $this->helper->getConfigData('debug');
    }
}