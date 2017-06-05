<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk\Helper;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Nord\Shipfunk\Helper\UnitConverter;
use Nord\Shipfunk\Model\BoxPacker\Box;
use Nord\Shipfunk\Model\BoxPacker\Item;
use Nord\Shipfunk\Model\BoxPacker\ShipfunkPackedBox;
use Nord\Shipfunk\Helper\Data as ShipfunkDataHelper;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item as cartItem;
use Nord\Shipfunk\Model\BoxPacker\ShipfunkPacker;

/**
 * Class Parcel
 *
 * @package Nord\Shipfunk\Model\Api
 */
class ParcelHelper
{
    /**
     * @var string
     */
    protected $dimens;

    /**
     * @var string
     */
    protected $warehouse;

    /**
     * @var string
     */
    protected $contents;

    /**
     * @var string
     */
    protected $parcelCode;

    /**
     * @var string
     */
    protected $weight;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $oldRemoval = 0;

    /**
     * @var string
     */
    protected $installation = 0;

    /**
     * @var string
     */
    protected $fragile = 0;

    /**
     * @var string
     */
    protected $vakCode = 0;

    /**
     * @var string
     */
    protected $vakDescription = 0;

    /**
     * @var string
     */
    protected $vakWeight = '0.0';

    /**
     * @var int
     */
    protected $stackable = 0;

    /**
     * @var int
     */
    protected $toppleable = 0;

    /**
     * @var string
     */
    protected $tracking_code;

    /**
     * @var string
     */
    protected $tracking_code_return;

    /**
     * @var array
     */
    protected $order;

    /**
     * @var int
     */
    protected $quoteId;

    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var mixed
     */
    protected $product;

    /**
     * @var array
     */
    protected $products;

    /**
     * @var UnitConverter
     */
    protected $unitConverter;

    /**
     * @var ShipfunkPacker
     */
    protected $packer;

    /**
     * @var ShipfunkDataHelper
     */
    protected $helper;


    /**
     * ParcelHelper constructor.
     *
     * @param ShipfunkPacker $packer
     * @param UnitConverter $unitConverter
     * @param ShipfunkDataHelper $shipfunkDataHelper
     */
    public function __construct(
        ShipfunkPacker $packer,
        UnitConverter $unitConverter,
        ShipfunkDataHelper $shipfunkDataHelper
    ) {
        $this->packer = $packer;
        $this->unitConverter = $unitConverter;
        $this->helper = $shipfunkDataHelper;

    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * TODO: THIS COULD BE A BUG ! CHECK IF orderid is really the quote id
     *
     * @param mixed $order
     *
     * @return ParcelHelper
     */
    public function setOrder($order)
    {
        $this->order = $order;
        $this->setQuoteId($order['orderid']);

        return $this;
    }

    /**
     * @return int
     */
    public function getQuoteId()
    {
        return $this->quoteId;
    }

    /**
     * @param int $quoteId
     *
     * @return ParcelHelper
     */
    public function setQuoteId($quoteId)
    {
        $this->quoteId = $quoteId;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     *
     * @return ParcelHelper
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @param mixed $product
     *
     * @return ParcelHelper
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return array
     */
    public function getParcel()
    {
        return [
            'contents'             => $this->getContents(),
            'parcelCode'           => $this->getParcelCode(),
            'weight'               => $this->getWeight(),
            'value'                => $this->getValue(),
            'old_removal'          => $this->getOldRemoval(),
            'installation'         => $this->getInstallation(),
            'fragile'              => $this->getFragile(),
            'toppleable'           => $this->getToppleable(),
            'stackable'            => $this->getStackable(),
            'vakCode'              => $this->getVakCode(),
            'vakDescription'       => $this->getVakDescription(),
            'vakWeight'            => $this->getVakWeight(),
            'dimens'               => $this->getDimens(),
            'warehouse'            => $this->getWarehouse(),
            'tracking_code'        => $this->getTrackingCode(),
            'tracking_code_return' => $this->getTrackingCodeReturn(),
        ];
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @param string $contents
     *
     * @return $this
     */
    public function setContents($contents)
    {
        $this->contents = $contents;

        return $this;
    }

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
     * @return $this
     */
    public function setParcelCode($parcelCode)
    {
        $this->parcelCode = $parcelCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param string $weight
     *
     * @return $this
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getOldRemoval()
    {
        return $this->oldRemoval;
    }

    /**
     * @param string $oldRemoval
     *
     * @return $this
     */
    public function setOldRemoval($oldRemoval)
    {
        $this->oldRemoval = $oldRemoval;

        return $this;
    }

    /**
     * @return int
     */
    public function getInstallation()
    {
        return $this->installation;
    }

    /**
     * @param int $installation
     *
     * @return $this
     */
    public function setInstallation($installation)
    {
        $this->installation = $installation;

        return $this;
    }

    /**
     * @return int
     */
    public function getFragile()
    {
        return $this->fragile;
    }

    /**
     * @param int $fragile
     *
     * @return $this
     */
    public function setFragile($fragile)
    {
        $this->fragile = $fragile;

        return $this;
    }

    /**
     * @return int
     */
    public function getToppleable()
    {
        return $this->toppleable;
    }

    /**
     * @param int $toppleable
     *
     * @return $this
     */
    public function setToppleable($toppleable)
    {
        $this->toppleable = $toppleable;

        return $this;
    }

    /**
     * @return int
     */
    public function getStackable()
    {
        return $this->stackable;
    }

    /**
     * @param int $stackable
     *
     * @return $this
     */
    public function setStackable($stackable)
    {
        $this->stackable = $stackable;

        return $this;
    }

    /**
     * @return string
     */
    public function getVakCode()
    {
        return $this->vakCode;
    }

    /**
     * @param string $vakCode
     *
     * @return $this
     */
    public function setVakCode($vakCode)
    {
        $this->vakCode = $vakCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getVakDescription()
    {
        return $this->vakDescription;
    }

    /**
     * @param string $vakDescription
     *
     * @return $this
     */
    public function setVakDescription($vakDescription)
    {
        $this->vakDescription = $vakDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getVakWeight()
    {
        return $this->vakWeight;
    }

    /**
     * @param string $vakWeight
     *
     * @return $this
     */
    public function setVakWeight($vakWeight)
    {
        $this->vakWeight = $vakWeight;

        return $this;
    }

    /**
     * @return string
     */
    public function getDimens()
    {
        return $this->dimens;
    }

    /**
     * @param $dimens
     *
     * @return $this
     */
    public function setDimens($dimens)
    {
        $this->dimens = $dimens;

        return $this;
    }

    /**
     * @return string
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * @param string $warehouse
     *
     * @return $this
     */
    public function setWarehouse($warehouse)
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTrackingCode()
    {
        return $this->tracking_code;
    }

    /**
     * @param $tracking_code
     *
     * @return $this
     */
    public function setTrackingCode($tracking_code)
    {
        $this->tracking_code = $tracking_code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTrackingCodeReturn()
    {
        return $this->tracking_code_return;
    }

    /**
     * @param $tracking_code_return
     *
     * @return $this
     */
    public function setTrackingCodeReturn($tracking_code_return)
    {
        $this->tracking_code_return = $tracking_code_return;

        return $this;
    }

    /**
     * @param                         $items
     * @param RateRequest|DataObject $request
     *
     * @return array
     */
    public function packWithBoxPacker($items, $request)
    {

        /** @var cartItem $cartItem */
        foreach ($items as $cartItem) {

            if ($cartItem instanceof \Magento\Sales\Model\Order\Shipment\Item) {
                $cartItem = $cartItem->getOrderItem();
            }

            if ($cartItem->getParentItem()) {
                continue;
            }

            /** @noinspection PhpUndefinedMethodInspection */
            if ($cartItem->getHasChildren() && $cartItem->isShipSeparately()) {
                foreach ($cartItem->getChildren() as $child) {
                    if ($child->getProduct()->isVirtual()) {
                        $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                    }
                }
            } elseif ($cartItem->getProduct()->isVirtual()) {
                $request->setPackageValue($request->getPackageValue() - $cartItem->getBaseRowTotal());
            } else {

                $this->product = $cartItem->getProduct();

                $item = new Item(
                    $this->product->getData('name').'/'.$this->product->getData('sku'),
                    $this->getProductWidth(),
                    $this->getProductLength(),
                    $this->getProductDepth(),
                    $this->getProductWeight(),
                    true,
                    $this->product->getData('price'),
                    $cartItem->getId(),
                    $cartItem->getProduct()->getId(),
                    $cartItem->getQty()
                );

                $this->packer->addItem($item, $cartItem->getQty());

            }
        }

        $packedBoxes = $this->packer->pack();

        $convertedBoxes = $this->convertBoxPackerToMagento($packedBoxes, false);

        return $convertedBoxes;
    }

    /**
     * @return mixed
     */
    public function getProductWidth()
    {
        $widthAttribute = $this->product->getCustomAttribute('shipfunk_width');

        /** @noinspection PhpUndefinedMethodInspection */
        $value = $widthAttribute ? $widthAttribute->getValue() : $this->helper->getConfigData('width_default');

        return $this->unitConverter->from($value, $this->helper->getConfigData('width_unit'))->to('cm');
    }

    /**
     * @return mixed
     */
    public function getProductLength()
    {
        $lengthAttribute = $this->product->getCustomAttribute('shipfunk_length');
        /** @noinspection PhpUndefinedMethodInspection */
        $value = $lengthAttribute ? $lengthAttribute->getValue() : $this->helper->getConfigData('length_default');

        return $this->unitConverter->from($value, $this->helper->getConfigData('length_unit'))->to('cm');
    }

    /**
     * @return mixed
     */
    public function getProductDepth()
    {
        $depthAttribute = $this->product->getCustomAttribute('shipfunk_depth');

        /** @noinspection PhpUndefinedMethodInspection */
        $value = $depthAttribute ? $depthAttribute->getValue() : $this->helper->getConfigData('depth_default');

        return $this->unitConverter->from($value, $this->helper->getConfigData('depth_unit'))->to('cm');
    }

    /**
     * @return mixed
     */
    public function getProductWeight()
    {
        $productValue = $this->product->getData('weight');

        $value = $productValue ? $productValue : $this->helper->getConfigData('weight_default');

        return $this->unitConverter->from($value, $this->helper->getConfigData('weight_unit'))->to('kg');
    }

    /**
     * @param      $boxPackerParcels
     * @param bool $convertUnits
     *
     * @return array
     */
    public function convertBoxPackerToMagento($boxPackerParcels, $convertUnits = true)
    {
        $convertedParcels = [];

        /** @var ShipfunkPackedBox $boxPackerParcel */
        foreach ($boxPackerParcels as $boxPackerParcel) {
            $boxPackerParcelBox = $boxPackerParcel->getBox();

            $parcelCustomsValue = 0;
            $parcelWeight = $boxPackerParcelBox->getEmptyWeight();

            if ($convertUnits) {
                $parcelWeight = $parcelWeight / 1000;
            }

            $items = [];

            /** @var Item $item */
            foreach ($boxPackerParcel->getItems() as $item) {

                $itemWeight = $this->unitConverter->from(
                    $item->getWeight(),
                    $this->helper->getConfigData('weight_unit')
                )->to('kg');

                $parcelWeight = $parcelWeight + $itemWeight;

                if (isset($items[$item->getCartItemId()])) {
                    $items[$item->getCartItemId()]['qty'] = $items[$item->getCartItemId()]['qty'] + 1;
                    $items[$item->getCartItemId()]['customs_value'] = $items[$item->getCartItemId(
                        )]['customs_value'] + $item->getPrice();
                    continue;
                }

                $items[$item->getCartItemId()] = [
                    'qty'           => 1,
                    'customs_value' => (float)$item->getPrice(),
                    'price'         => $item->getPrice(),
                    'name'          => $item->getDescription(),
                    'weight'        => $itemWeight,
                    'product_id'    => $item->getProductId(),
                    'order_item_id' => $item->getCartItemId(),
                ];

                $parcelCustomsValue = $items[$item->getCartItemId()]['customs_value'];
            }

            if ($convertUnits) {
                /** @var Box $boxPackerParcelBox */
                $params = [
                    'container'          => $boxPackerParcelBox->getReference(),
                    'weight'             => $parcelWeight,
                    'customs_value'      => $parcelCustomsValue,
                    'length'             => $this->unitConverter->from(
                        $boxPackerParcelBox->getOuterLength(),
                        'mm'
                    )->to('cm'),
                    'width'              => $this->unitConverter->from(
                        $boxPackerParcelBox->getOuterWidth(),
                        'mm'
                    )->to('cm'),
                    'height'             => $this->unitConverter->from(
                        $boxPackerParcelBox->getOuterDepth(),
                        'mm'
                    )->to('cm'),
                    'weight_units'       => 'KILOGRAM',
                    'dimension_units'    => 'CENTIMETER',
                    'content_type'       => '',
                    'content_type_other' => '',
                ];
            } else {
                /** @var Box $boxPackerParcelBox */
                $params = [
                    'container'          => $boxPackerParcelBox->getReference(),
                    'weight'             => $parcelWeight,
                    'customs_value'      => $parcelCustomsValue,
                    'length'             => $boxPackerParcelBox->getOuterLength(),
                    'width'              => $boxPackerParcelBox->getOuterWidth(),
                    'height'             => $boxPackerParcelBox->getOuterDepth(),
                    'weight_units'       => 'KILOGRAM',
                    'dimension_units'    => 'CENTIMETER',
                    'content_type'       => '',
                    'content_type_other' => '',
                ];
            }

            $convertedParcels[] = [
                'params' => $params,
                'items'  => $items,
            ];
        }

        return $convertedParcels;
    }
}
