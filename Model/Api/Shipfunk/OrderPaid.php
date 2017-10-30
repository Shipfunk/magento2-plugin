<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

/**
 * Class OrderPaid
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class OrderPaid extends AbstractEndpoint
{
    public function execute()
    {
      
    }

    /**
     * @return string
     */
    public function getRestFormat()
    {
        return "json";
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param bool              $transmitWebshopId
     *
     * @return \Zend_Http_Response
     */
    protected function get($xml, $transmitWebshopId = false)
    {
        $environmentUrl = $this->helper->getConfigData('shipfunk_url');
        $url            = $environmentUrl.$this->getRoute()."/".
            $this->getQuoteId()."/".
            $this->getWebshopId()."/".
            $this->getOrderId()."/".
            $this->getRestFormat();      
      
        $client = $this->_httpClientFactory->create();
        $client->setUri((string) $url);
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $client->setHeaders($this->getHeaders());
        $result = $client->request(\Magento\Framework\HTTP\ZendClient::GET);

        return $result;
    }

    /**
     * @return void
     */
    public function getResult()
    {
        $this->setQuoteId($this->getOrder()->getQuoteId());
        $this->setOrderId($this->getOrder()->getRealOrderId());

        $this->setSimpleXml();

        $this->appendToXml($this->getWebshop(), $this->simpleXml);

        $this->appendToXml([
            'order' => [
                'orderid'       => $this->getQuoteId(),
                'final_orderid' => $this->getOrderId(),
            ],
        ], $this->simpleXml);

        $xml = $this->simpleXml->asXML();
        $this->log->debug(var_export($xml, true));

        $result = $this
            ->setRouteAndFieldname('orderpaid')
            ->get($xml, false);

        $this->log->debug(var_export($result, true));
    }
}