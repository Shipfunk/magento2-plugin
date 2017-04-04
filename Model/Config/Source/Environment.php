<?php
/**
 * @author Nord Software
 * @package Nord_Shipfunk
 */

namespace Nord\Shipfunk\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class WeightUnits
 *
 * @package Nord\Shipfunk\Model\Config\Source
 */
class Environment implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'https://dev.shipfunkservices.com/api/1.1/', 'label' => 'Yes'],
            ['value' => 'https://shipfunkservices.com/api/1.1/', 'label' => 'No'],
        ];
    }

}