<?php

namespace Nord\Shipfunk\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class DimensionsUnits
 *
 * @package Nord\Shipfunk\Model\Config\Source
 */
class DimensionsUnits implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'mm', 'label' => __('millimeters')],
            ['value' => 'cm', 'label' => __('centimeters')],
            ['value' => 'm', 'label' => __('meters')],
            ['value' => 'in', 'label' => __('inches')],
            ['value' => 'ft', 'label' => __('feets')],
        ];
    }

}