<?php

namespace Nord\Shipfunk\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface OrderSelectedPickupInterface extends ExtensibleDataInterface
{
    const SELECTED_PICKUP_ID = 'selected_pickup_id';
    const PICKUP_NAME = 'pickup_name';
    const PICKUP_ADDRESS = 'pickup_addr';
    const PICKUP_POSTCODE = 'pickup_postal';
    const PICKUP_CITY = 'pickup_city';
    const PICKUP_COUNTRY = 'pickup_country';
    const PICKUP_ID = 'pickup_id';
    const PICKUP_OPENING_HOURS = 'pickup_openinghours';
    const PICKUP_OPENING_HOURS_EXCEPTION = 'pickup_openinghours_excep';
    const ORDER_ID = 'order_id';

    /**
     * @return int|null
     */
    public function getSelectedPickupId();

    /**
     * @param int $id
     * @return $this
     */
    public function setSelectedPickupId($id);

    /**
     * @return string
     */
    public function getPickupName();

    /**
     * @param string $pickupName
     * @return $this
     */
    public function setPickupName($pickupName);
  
    /**
     * @return string
     */
    public function getPickupAddress();

    /**
     * @param string $pickupAddress
     * @return $this
     */
    public function setPickupAddress($pickupAddress);
  
    /**
     * @return string
     */
    public function getPickupPostcode();

    /**
     * @param string $pickupPostcode
     * @return $this
     */
    public function setPickupPostcode($pickupPostcode);
  
    /**
     * @return string
     */
    public function getPickupCity();

    /**
     * @param string $pickupCity
     * @return $this
     */
    public function setPickupCity($pickupCity);
  
    /**
     * @return string
     */
    public function getPickupCountry();

    /**
     * @param string $pickupCountry
     * @return $this
     */
    public function setPickupCountry($pickupCountry);
  
    /**
     * @return string
     */
    public function getPickupId();

    /**
     * @param string $pickupId
     * @return $this
     */
    public function setPickupId($pickupId);
  
    /**
     * @return string
     */
    public function getPickupOpeningHours();

    /**
     * @param string $pickupOpeningHours
     * @return $this
     */
    public function setPickupOpeningHours($pickupOpeningHours);
  
    /**
     * @return string
     */
    public function getPickupOpeningHoursException();

    /**
     * @param string $pickupOpeningHoursException
     * @return $this
     */
    public function setPickupOpeningHoursException($pickupOpeningHoursException);
  
    /**
     * @return int|null
     */
    public function getOrderId();

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Nord\Shipfunk\Api\Data\OrderSelectedPickupExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Nord\Shipfunk\Api\Data\OrderSelectedPickupExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Nord\Shipfunk\Api\Data\OrderSelectedPickupExtensionInterface $extensionAttributes
    );
}