<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

use Nord\Shipfunk\Model\Api\Shipfunk\Helper\AbstractApiHelper;
use Nord\Shipfunk\Model\Api\Shipfunk\Helper\CustomerHelper;
use Nord\Shipfunk\Helper\Data as ShipfunkDataHelper;
use Magento\Framework\View\Element\Template\Context;
use Requests;

/**
 * Class OrderPaid
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class OrderPaid extends AbstractApiHelper
{

    /**
     * OrderPaid constructor.
     *
     * @param Context            $context
     * @param ShipfunkDataHelper $shipfunkDataHelper
     * @param CustomerHelper     $customerHelper
     */
    public function __construct(
        Context $context,
        ShipfunkDataHelper $shipfunkDataHelper,
        CustomerHelper $customerHelper
    ) {
        parent::__construct($context, $shipfunkDataHelper, $customerHelper);
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
     * @return \Requests_Response
     */
    protected function get($xml, $transmitWebshopId = false)
    {
        $environmentUrl = $this->helper->getConfigData('shipfunk_url');
        $url            = $environmentUrl.$this->getRoute()."/".
            $this->getQuoteId()."/".
            $this->getWebshopId()."/".
            $this->getOrderId()."/".
            $this->getRestFormat();
        $result         = Requests::get($url, $this->getHeaders());

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