<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

use Nord\Shipfunk\Model\Api\Shipfunk\Helper\AbstractApiHelper;

/**
 * Class DeleteParcels
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class DeleteParcels extends AbstractApiHelper
{
    /**
     * @var string
     */
    protected $parcelCode;

    /**
     * @var string
     */
    protected $trackingCode;

    /**
     * @return mixed
     */
    public function getParcelCode()
    {
        return $this->parcelCode;
    }

    /**
     * @param string $parcelCode
     *
     * @return DeleteParcels
     */
    public function setParcelCode($parcelCode)
    {
        $this->parcelCode = $parcelCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTrackingCode()
    {
        return $this->trackingCode;
    }

    /**
     * @param string $trackingCode
     *
     * @return DeleteParcels
     */
    public function setTrackingCode($trackingCode)
    {
        $this->trackingCode = $trackingCode;

        return $this;
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