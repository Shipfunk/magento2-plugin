<?php

namespace Nord\Shipfunk\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class LengthUnits
 *
 * @package Nord\Shipfunk\Model\Config\Source
 */
class LengthUnits implements ArrayInterface
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