<?php

namespace Nord\Shipfunk\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
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
 * @package Nord\Shipfunk\Helper
 */
class ParcelHelper extends AbstractHelper
{
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
     * @param \Magento\Framework\App\Helper\Context $context
     * @param ShipfunkPacker $packer
     * @param UnitConverter $unitConverter
     * @param ShipfunkDataHelper $shipfunkDataHelper
     */
    public function __construct(
        Context $context,
        ShipfunkPacker $packer,
        UnitConverter $unitConverter,
        ShipfunkDataHelper $shipfunkDataHelper
    ) {
        parent::__construct($context);
        $this->packer = $packer;
        $this->unitConverter = $unitConverter;
        $this->helper = $shipfunkDataHelper;

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
            if (!$cartItem->getProduct()->isVirtual()) {
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
    
    /*
     * @param RateRequest $request
     * @return array
     */
    public function parseProducts($request)
    {
        $products = [];
        foreach ($request->getAllItems() as $item) {
            // get the info only from child products, since dimensions and weight might be different based on configuration
            if ($item->getHasChildren()) {
                continue;
            }
            $product = $item->getProduct();
            if (!$product->isVirtual()) {
                $products[] = [
                    'amount' => $item->getQty(),
                    'code' => $product->getSku(),
                    'name' => $product->getName(),
                    'weight'     => [
                        'unit'  => $this->helper->getConfigData('weight_unit'),
                        'amount'  => $this->getProductValue($product, 'weight')
                    ],
                    'dimensions'     => [
                        'unit' => $this->helper->getConfigData('dimensions_unit'),
                        'width' => $this->getProductValue($product, 'width'),
                        'depth' => $this->getProductValue($product, 'depth'),
                        'height' => $this->getProductValue($product, 'height')
                    ]
                ];
            }
        }
      
        return $products;
    }

    /**
     * @return mixed
     */
    public function getProductValue($product, $attribute)
    {
        $mageAttribute = $this->helper->getConfigData($attribute . '_mapping');
        $attributeValue = $product->getData($mageAttribute);  // @todo VALUE IS NOT BEING PASSED HERE
        $value = is_numeric($attributeValue) && $attributeValue ? $attributeValue : $this->helper->getConfigData($attribute . '_default');
        return $value;
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
