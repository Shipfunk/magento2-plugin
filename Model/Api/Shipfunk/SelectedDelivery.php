<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

use Magento\Framework\View\Element\Template\Context;
use Nord\Shipfunk\Model\Api\Shipfunk\Helper\AbstractApiHelper;
use Nord\Shipfunk\Model\Api\Shipfunk\Helper\CustomerHelper;
use Nord\Shipfunk\Helper\Data as ShipfunkDataHelper;

/**
 * Class SelectedDelivery
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class SelectedDelivery extends AbstractApiHelper
{
    /**
     * SelectedDelivery constructor.
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

    /**
     * @return \Requests_Response|string
     */
    protected function execute()
    {
        $this->setSimpleXml();

        $selectedOption = [
            'carriercode'     => $this->getCarrierCode(),
            'productcode'     => $this->getProductCode(),
            'orderid'         => $this->getQuoteId(),
            'webshopid'       => $this->getWebshopId(),
            'realprice'       => $this->getRealPrice(),
            'discountedprice' => $this->getDiscountedPrice(),
        ];

        if ($this->getPickupId()) {
            $selectedOption['pickupid'] = $this->getPickupId();
        }

        $this->appendToXml([
            'selected_option' => $selectedOption,
        ], $this->simpleXml);

        $xml = $this->simpleXml->asXML();

        $result = $this->setRoute('selected_delivery')->setFieldname('selected')->get($xml);

        return $result;
    }
}