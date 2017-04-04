<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

use Nord\Shipfunk\Model\Api\Shipfunk\Helper\AbstractApiHelper;

/**
 * Class ChangeCustomerInfo
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class ChangeCustomerInfo extends AbstractApiHelper
{
    /**
     * @return string
     */
    public function getRestFormat()
    {
        return "/true";
    }

    /**
     * @return void
     */
    public function changeCustomerInfo()
    {
        $this->customerHelper;

        $this->setSimpleXml();

        $this->appendToXml($this->getWebshop(), $this->simpleXml);

        $this->appendToXml([
            'order' => [
                'orderid' => $this->getOrderId(),
            ],
        ], $this->simpleXml);

        $this->appendToXml([
            'customer' => [
                'fname'      => $this->customerHelper->getLastName(),
                'lname'      => $this->customerHelper->getLastName(),
                'streetaddr' => $this->customerHelper->getStreet(),
                'postal'     => $this->customerHelper->getPostalCode(),
                'city'       => $this->customerHelper->getCity(),
                'country'    => $this->customerHelper->getCountry(),
                'phone'      => $this->customerHelper->getPhone(),
                'email'      => $this->customerHelper->getEmail(),
            ],
        ], $this->simpleXml);

        $xml = $this->simpleXml->asXML();

        $this->setRouteAndFieldname('change_customer_info')->post($xml);
    }
}