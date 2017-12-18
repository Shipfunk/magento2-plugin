<?php

namespace Nord\Shipfunk\Model\Order;

use Magento\Sales\Model\Order;
use Nord\Shipfunk\Api\Data\OrderSelectedPickupInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class SelectedPickup extends AbstractExtensibleModel implements OrderSelectedPickupInterface
{  
    protected $eventPrefix = 'sales_order_selected_pickup';
  
    protected $eventObject = 'pickup';

    protected function _construct()
    {
        $this->_init(\Nord\Shipfunk\Model\ResourceModel\Order\SelectedPickup::class);
    }
  
    /**
     * {@inheritdoc}
     */
    public function getSelectedPickupId()
    {
        return $this->_getData(static::SELECTED_PICKUP_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setSelectedPickupId($id)
    {
        return $this->setData(self::SELECTED_PICKUP_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getPickupName()
    {
        return $this->_getData(static::PICKUP_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setPickupName($pickupName)
    {
        return $this->setData(self::PICKUP_NAME, $pickupName);
    }
  
    /**
     * {@inheritdoc}
     */
    public function getPickupAddress()
    {
        return $this->_getData(static::PICKUP_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function setPickupAddress($pickupAddress)
    {
        return $this->setData(self::PICKUP_ADDRESS, $pickupAddress);
    }
  
    /**
     * {@inheritdoc}
     */
    public function getPickupPostcode()
    {
        return $this->_getData(static::PICKUP_POSTCODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPickupPostcode($pickupPostcode)
    {
        return $this->setData(self::PICKUP_POSTCODE, $pickupPostcode);
    }
  
    /**
     * {@inheritdoc}
     */
    public function getPickupCity()
    {
        return $this->_getData(static::PICKUP_CITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setPickupCity($pickupCity)
    {
        return $this->setData(self::PICKUP_CITY, $pickupCity);
    }
  
    /**
     * {@inheritdoc}
     */
    public function getPickupCountry()
    {
        return $this->_getData(static::PICKUP_COUNTRY);
    }

    /**
     * {@inheritdoc}
     */
    public function setPickupCountry($pickupCountry)
    {
        return $this->setData(self::PICKUP_COUNTRY, $pickupCountry);
    }
  
    /**
     * {@inheritdoc}
     */
    public function getPickupId()
    {
        return $this->_getData(static::PICKUP_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setPickupId($pickupId)
    {
        return $this->setData(self::PICKUP_ID, $pickupId);
    }
  
    /**
     * {@inheritdoc}
     */
    public function getPickupOpeningHours()
    {
        return $this->_getData(static::PICKUP_OPENING_HOURS);
    }

    /**
     * {@inheritdoc}
     */
    public function setPickupOpeningHours($pickupOpeningHours)
    {
        return $this->setData(self::PICKUP_OPENING_HOURS, $pickupOpeningHours);
    }
  
    /**
     * {@inheritdoc}
     */
    public function getPickupOpeningHoursException()
    {
        return $this->_getData(static::PICKUP_OPENING_HOURS_EXCEPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setPickupOpeningHoursException($pickupOpeningHoursException)
    {
        return $this->setData(self::PICKUP_OPENING_HOURS_EXCEPTION, $pickupOpeningHoursException);
    }
  
    /**
     * {@inheritdoc}
     */
    public function getOrderId()
    {
        return $this->_getData(static::ORDER_ID);
    }
  
    /**
     * {@inheritdoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }
  
    /**
     * Add order data to selected pickup
     *
     * @param Order $order
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->setOrderId($order->getId());
      
        return $this;
    }
  
    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }
    
    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Nord\Shipfunk\Api\Data\OrderSelectedPickupExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}