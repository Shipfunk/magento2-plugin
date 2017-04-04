<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Store\Model\ScopeInterface;

/**
 * Class CustomerHelper
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk\Helper
 */
class CustomerHelper
{
    /**
     * @var array
     */
    protected $orderData;

    /**
     * @var RateRequest
     */
    protected $request;

    /**
     * CustomerHelper constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->storeInformation = $scopeConfig->getValue('general/store_information',
            ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $orderData
     *
     * @return $this
     */
    public function setOrderData($orderData)
    {
        $this->orderData = $orderData;

        return $this;
    }

    /**
     * @return RateRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param RateRequest $request
     *
     * @return CustomerHelper
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }


    /**
     * @return mixed|string
     */
    public function getFirstName()
    {
        if (null !== $this->getRequest()->getDestFirstname()) {
            $firstname = $this->getRequest()->getDestFirstname();

        } else {
            $firstname = "DummyFirstname";
        }

        return $firstname;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        if (null !== $this->getRequest()->getDestLastname()) {
            $lastname = $this->getRequest()->getDestLastname();

        } else {
            $lastname = "DummyLastname";
        }

        return $lastname;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        if (null !== $this->getRequest()->getDestTelephone()) {
            $phone = $this->getRequest()->getDestTelephone();

        } else {
            $phone = "000000000";
        }

        return $phone;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return "dummy@shipfunk.com";
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        if (null !== $this->getRequest()->getDestStreet()) {
            $street = $this->getRequest()->getDestStreet();

        } else {
            $street = "Kauppakatu 1";
        }

        return $street;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return '00180';
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return 'Helsinki';
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return 'FI';
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    protected function getStoreInformation($key)
    {
        return $this->storeInformation[$key];
    }
}