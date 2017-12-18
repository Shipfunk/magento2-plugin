<?php

namespace Nord\Shipfunk\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class WeightUnits
 *
 * @package Nord\Shipfunk\Model\Config\Source
 */
class WeightUnits implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'g', 'label' => __('grams')],
            ['value' => 'kg', 'label' => __('kilograms')],
            ['value' => 'oz', 'label' => __('ounces')],
            ['value' => 'lb', 'label' => __('pounds')],
            ['value' => 'st', 'label' => __('stones')]
        ];
    }

}