<?php

namespace Nord\Shipfunk\Model;

use Magento\Quote\Model\ShippingMethodManagement as MageShippingMethodManagement,
    Magento\Quote\Api\Data\EstimateAddressInterface,
    Magento\Quote\Model\Quote;

/**
 * Class ShippingMethodManagement
 *
 * @package Nord\Shipfunk\Model
 */
class ShippingMethodManagement extends MageShippingMethodManagement
{
    /**
     * @param Quote  $quote
     * @param int    $country
     * @param string $postcode
     * @param int    $regionId
     * @param string $region
     * @param null   $firstname
     * @param null   $lastname
     * @param null   $street
     * @param null   $telephone
     * @param null   $city
     *
     * @return array|\Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    protected function getEstimatedRates(
        Quote $quote,
        $country,
        $postcode,
        $regionId,
        $region,
        $firstname = null,
        $lastname = null,
        $street = null,
        $telephone = null,
        $city = null
    ) {
        $data = [
            EstimateAddressInterface::KEY_COUNTRY_ID => $country,
            EstimateAddressInterface::KEY_POSTCODE   => $postcode,
            EstimateAddressInterface::KEY_REGION_ID  => $regionId,
            EstimateAddressInterface::KEY_REGION     => $region,
            'firstname'                              => $firstname,
            'lastname'                               => $lastname,
            'street'                                 => $street,
            'telephone'                              => $telephone,
            'city'                                   => $city,
        ];

        return $this->getShippingMethods($quote, $data);
    }

    /**
     * @param int $cartId
     * @param int $addressId
     *
     * @return array|\Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function estimateByAddressId($cartId, $addressId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }
        $address = $this->addressRepository->getById($addressId);
        $street  = is_array($address->getStreet()) ? $address->getStreet()[0] : $address->getStreet();

        return $this->getEstimatedRates(
            $quote,
            $address->getCountryId(),
            $address->getPostcode(),
            $address->getRegionId(),
            $address->getRegion(),
            $address->getFirstname(),
            $address->getLastname(),
            $street,
            $address->getTelephone(),
            $address->getCity()
        );
    }

    /**
     * @param Quote $quote
     * @param array $addressData
     *
     * @return array
     */
    private function getShippingMethods(Quote $quote, array $addressData)
    {
        $output          = [];
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->addData($addressData);
        $shippingAddress->setCollectShippingRates(true);

        $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }

        return $output;
    }
}