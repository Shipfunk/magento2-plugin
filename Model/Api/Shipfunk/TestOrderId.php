<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

use Magento\Framework\View\Element\Template\Context;
use Nord\Shipfunk\Model\Api\Shipfunk\Helper\AbstractApiHelper;
use Nord\Shipfunk\Model\Api\Shipfunk\Helper\CustomerHelper;
use Nord\Shipfunk\Helper\Data as ShipfunkDataHelper;
use Requests;

/**
 * Class TestOrderId
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class TestOrderId extends AbstractApiHelper
{
    /**
     * @return string
     */
    public function getRestFormat()
    {
        return "json";
    }

    /**
     * TestOrderId constructor.
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
     * @return \Requests_Response|string
     */
    public function getResult()
    {

        $result = $this->execute();

        return $result;
    }

    protected function execute()
    {
        $this->setSimpleXml();
        $xml = $this->simpleXml->asXML();
        $this->log->debug(var_export($xml, true));

        $result = $this
            ->setRoute('test_order_id')
            ->get($xml, false);

        echo 0;
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
            $this->getWebshopId()."/".
            $this->getOrderId()."/".
            $this->getRestFormat();
        $result         = Requests::get($url, $this->getHeaders());

        return $result;
    }
}