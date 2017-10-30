<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

/**
 * Class DeleteParcels
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class DeleteParcels extends AbstractEndpoint
{
    public function execute()
    {
      
    }

    /**
     * @return void
     */
    public function removeAllParcels()
    {
        $this->setSimpleXml();

        $this->appendToXml($this->getWebshop(), $this->simpleXml);
        $this->appendToXml([
            'order' => [
                'orderid'        => $this->getOrderId(),
                'rm_all_parcels' => 1,
            ],
        ], $this->simpleXml);

        $xml = $this->simpleXml->asXML();

        $this->setRouteAndFieldname('delete_parcel')->post($xml);
    }

    /**
     * @return void
     */
    public function removeParcel()
    {
        $this->setSimpleXml();

        $this->appendToXml($this->getWebshop(), $this->simpleXml);
        $this->appendToXml([
            'order' => [
                'orderid' => $this->getOrderId(),
            ],
        ], $this->simpleXml);

        $this->appendToXml([
            'parcel' => [
                'parcelCode'   => $this->getParcelCode(),
                'trackingCode' => $this->getTrackingCode(),
            ],
        ], $this->simpleXml);

        $xml = $this->simpleXml->asXML();

        $this->setRouteAndFieldname('delete_parcel')->post($xml);
    }
}