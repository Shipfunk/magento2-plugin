<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

/**
 * Class GetDeliveryOptions
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class GetDeliveryOptions extends AbstractEndpoint
{
    public function execute()
    {
        $request = $this->getRequest();
        $query = [
           'query' => [
              'order' => [
                  'language'      => $request->getDestCountryId(),
                  'monetary' => [
                      'currency' => $request->getPackageCurrency()->getCurrencyCode(),
                      'value' => $request->getBaseSubtotalInclTax() // @todo BUG WITH DIFFERENT BASE CURRENCY
                  ],
                /*
                  'get_pickups' => [
                      'store' => 1, // retrieve store pickups
                      'store_only' => 0, // retrieve other methods beside store pickups
                      'transport_company' => 0 // don't retrieve pickup points for carriers, we'll get them later
                  ],
                  */
                  'products' => [
                      [
                        'code' => '234',
                        'name' => 'Name',
                        'weight'     => [
                            'unit'  => 'kg',
                            'amount'  => 1.00
                        ],
                        'dimensions'     => [
                            'unit' => 'cm',
                            'width' => '30',
                            'depth' => '30',
                            'height' => '30'
                        ]
                      ],
                      [
                        'code' => '432',
                        'name' => 'Tagname',
                        'weight'     => [
                            'unit'  => 'kg',
                            'amount'  => 1.00
                        ],
                        'dimensions'     => [
                            'unit' => 'cm',
                            'width' => '30',
                            'depth' => '30',
                            'height' => '30'
                        ]
                      ]
                  ]
                /*,
                  'parcels' => [
                      [
                          'code' => 'asd',
                          'warehouse'  => $this->helper->getConfigData('warehouse'),
                          'weight'     => [
                              'unit'  => 'kg',
                              'amount'  => '1.00'
                          ],
                          'value'      => '30.00',
                          'dimensions'     => [
                              'unit' => 'cm',
                              'width' => '30',
                              'depth' => '30',
                              'height' => '30'
                          ]
                      ]
                  ]
                  */
              ],
              'customer' => [
                  'first_name'      => $request->getDestFirstname(),
                  'last_name'       => $request->getDestLastname(),
                  'street_address'  => $request->getDestStreet(),
                  'postal_code'     => $request->getDestPostcode(), // required in v.1.2
                  'city'            => $request->getDestCity(),
                  'country'         => $request->getDestCountryId(), // required in v.1.2
                  'phone'           => $request->getDestTelephone(),
                  'email'           => $request->getDestEmail(),
              ]
           ]
        ];
        $query = utf8_encode(json_encode($query));
      
        /*
        $this->setSimpleXml();
        // order-language
        $this->appendToXml([
          'order' => [
              'language'      => $request->getDestCountryId()
          ]
        ], $this->simpleXml);
        // order-monetary
        $this->appendToXml([
          'monetary' => [
              'currency' => $request->getPackageCurrency()->getCurrencyCode(),
              'value' => $request->getBaseSubtotalInclTax() // @todo BUG WITH DIFFERENT BASE CURRENCY
          ]
        ], $this->simpleXml, 'order');
        // order-pickups
        $this->appendToXml([
          'get_pickups' => [
              'store' => 1, // retrieve store pickups
              'store_only' => 0, // retrieve other methods beside store pickups
              'transport_company' => 0 // don't retrieve pickup points for carriers, we'll get them later
          ]
        ], $this->simpleXml, 'order');
        // order-products
        $this->appendToXml([
          'products' => [
            [
              'product' => [
                'amount' => 1,
                'code' => '234',
                'name' => 'Name'
              ]
            ],
            [
              'product' => [
                'amount' => 2,
                'code' => '432',
                'name' => 'Tagname'
              ]
            ]
          ]
        ], $this->simpleXml, 'order');
        // order-parcels
      
        // customer
        $this->appendToXml([
          'customer' => [
              'first_name'      => $request->getDestFirstname(),
              'last_name'       => $request->getDestLastname(),
              'street_address'  => $request->getDestStreet(),
              'postal_code'     => $request->getDestPostcode(), // required in v.1.2
              'city'            => $request->getDestCity(),
              'country'         => $request->getDestCountryId(), // required in v.1.2
              'phone'           => $request->getDestTelephone(),
              'email'           => $request->getDestEmail(),
          ]
        ], $this->simpleXml);
        
        $xml = $this->simpleXml->asXML();
        */
        
        $this->log->debug(var_export($query, true));
        $result = $this->setEndpoint('get_delivery_options')
                      ->setRestFormat('/json/json')
                      ->setOrderId($request->getAllItems()[0]->getQuoteId())
                      ->post($query);
        $this->log->debug(var_export($result->getBody(), true));
        return $result;
    }
  
    /**
     * @return \Requests_Response|string
     */
    public function getResult()
    {
        $request = $this->getRequest();
        $order   = [
            'orderid'           => $request->getAllItems()[0]->getQuoteId(),
            'order_currency'    => $request->getPackageCurrency()->getCurrencyCode(),
            'order_lang'        => $request->getDestCountryId(), // scopeConfig current locale
            'order_price'       => $request->getBaseSubtotalInclTax(),
            'order_get_pickups' => 0,
        ];

        $this->setSimpleXml();

        $this->appendToXml($this->getWebshop(), $this->simpleXml);
        $this->appendToXml($this->getCustomer(), $this->simpleXml);
        $this->appendToXml(['order' => $order], $this->simpleXml);

        $parcels = $this->getParcels();
        foreach ($parcels as $parcelCode => $parcel) {

            $this->appendToXml([
                'parcel' => [
                    'parcelCode' => $parcelCode,
                    'warehouse'  => $this->helper->getConfigData('warehouse'),
                    'weight'     => $parcel['params']['weight'],
                    'value'      => $parcel['params']['customs_value'],
                    'dimens'     =>
                        $parcel['params']['length'].'x'.
                        $parcel['params']['width'].'x'.
                        $parcel['params']['height'],
                ],
            ], $this->simpleXml, 'order');
        }
        $xml = $this->simpleXml->asXML();
        $this->log->debug(var_export($xml, true));
        $result = $this->setRouteAndFieldname('get_delivoptions')->post($xml);
        $this->log->debug(var_export($result->getBody(), true));
        return $result;
    }
}