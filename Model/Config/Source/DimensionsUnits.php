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
            ['value' => 'cm', 'label' => 'cm'],
            ['value' => 'm', 'label' => 'm'],
            ['value' => 'ft', 'label' => 'ft'],
        ];
    }

}