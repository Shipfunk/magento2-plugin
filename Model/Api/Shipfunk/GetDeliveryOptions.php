<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

/**
 * Class GetDeliveryOptions
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class GetDeliveryOptions extends AbstractEndpoint
{
    protected function _getLanguageCode()
    {
        $currentLocale = $this->_localeResolver->getLocale();
        $currentLocaleArray = explode('_', $currentLocale);
        return array_pop($currentLocaleArray);
    }
  
    public function execute()
    {
        $request = $this->getRequest();
        $query = [
           'query' => [
              'order' => [
                  'language' => $this->_getLanguageCode(),
                  'monetary' => [
                      'currency' => $request->getPackageCurrency()->getCurrencyCode(),
                      'value' => $request->getBaseSubtotalInclTax() // @todo BUG WITH DIFFERENT BASE CURRENCY
                  ],
                  'get_pickups' => [ // get stores and carriers but without carrier pickup points
                      'store' => 1,
                      'store_only' => 0,
                      'transport_company' => 0
                  ],
                  'products' => $this->getProducts()
              ],
              'customer' => [ // we'll be sending the rest later
                  'postal_code'     => $request->getDestPostcode(),
                  'country'         => $request->getDestCountryId()
              ]
           ]
        ];
        $query = utf8_encode(json_encode($query));
        
        $result = $this->setEndpoint('get_delivery_options')
                      ->setRestFormat('/json/json')
                      ->setOrderId($request->getAllItems()[0]->getQuoteId())
                      ->post($query);
      
        return $result;
    }
}