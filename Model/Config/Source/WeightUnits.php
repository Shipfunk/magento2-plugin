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
            ['value' => 'kg', 'label' => 'kg'],
            ['value' => 'lb', 'label' => 'lb'],
        ];
    }

}