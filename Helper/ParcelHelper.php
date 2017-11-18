<?php

namespace Nord\Shipfunk\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Nord\Shipfunk\Helper\Data as ShipfunkDataHelper;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item as cartItem;

/**
 * Class Parcel
 *
 * @package Nord\Shipfunk\Helper
 */
class ParcelHelper extends AbstractHelper
{
    /**
     * @var ShipfunkDataHelper
     */
    protected $helper;


    /**
     * ParcelHelper constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param ShipfunkDataHelper $shipfunkDataHelper
     */
    public function __construct(
        Context $context,
        ShipfunkDataHelper $shipfunkDataHelper
    ) {
        parent::__construct($context);
        $this->helper = $shipfunkDataHelper;

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

}
