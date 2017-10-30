<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

use Magento\Framework\DataObject;

/**
 * Class CreateNewPackageCards
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class CreateNewPackageCards extends AbstractEndpoint
{
    /**
     * @return string
     */
    public function getRestFormat()
    {
        return "/both/xml";
    }

    /**
     * @return DataObject
     */
    public function getResult()
    {
        $request = $this->getRequest();

        $this->setOrder($request->getOrderShipment()->getOrder());

        $shippingDescription = $this->getOrder()->getData('shipping_description');
        $shipping = explode(" - ", $shippingDescription);
        $shippingCompany = $shipping[0];

        $parcelParams = $request->getPackageParams();
        $parcelId = $request->getPackageId();

        $this->setSimpleXml();
        $this->appendToXml($this->getWebshop(), $this->simpleXml);
        $this->appendToXml(
            [
                'order' => [
                    'orderid'   => $this->getOrder()->getRealOrderId(),
                    'returnNow' => 1,
                ],
            ],
            $this->simpleXml
        );

        $parcel = [
            'warehouse'  => $this->helper->getConfigData('warehouse'),
            'parcelCode' => $parcelId,
            'weight'     => $parcelParams->getWeight(),
            'dimens'     => $parcelParams->getLength()."x".$parcelParams->getWidth()."x".$parcelParams->getHeight(),
        ];

        $this->appendToXml(
            [
                'parcel' => $parcel,
            ],
            $this->simpleXml,
            'order'
        );

        $xml = $this->simpleXml->asXML();

        $result = $this
            ->setRoute('create_new_package_cards')
            ->setFieldname('createnewpgcard')
            ->post($xml);

        $resultXml = simplexml_load_string($result->getBody());
        $response = new DataObject();

        if (!isset($resultXml->parcel)) {
            // if we get an error here, it usually means that selecteddelivery has not been set or in most cases, we have a duplicate temp order id
            $response->setError(true);
            /*
            $this->logger->log(
                LogLevel::INFO,
                "Shipfunk Error (CreateNewPackageCards) : ".$resultXml->Error->Message->__toString()
            );
            */
            $errorMessage = __("shipfunk_error_7");

            $response->setErrors($errorMessage);
            $response->setMessage($errorMessage);
        } else {
            $parcelInformation = $resultXml->parcel;
            $sendTrCode = $parcelInformation->send_trcode->__toString();
            $sendCard = base64_decode($parcelInformation->send_card->__toString());

            $request->setPackageId($parcelId);
            $request->setPackagingType($parcelParams->getContainer());
            $request->setPackageWeight($parcelParams->getWeight());
            $request->setPackageParams(new DataObject($parcelParams->getData()));

            $response->setTrackingNumber($sendTrCode."/".$shippingCompany);
            $response->setShippingLabelContent($sendCard);
        }

        $response->setGatewayResponse($resultXml);

        return $response;
    }


}
