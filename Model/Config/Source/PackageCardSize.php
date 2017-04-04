<?php
/**
 * @author Nord Software
 * @package Nord_Shipfunk
 */

namespace Nord\Shipfunk\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class PackageCardSize
 *
 * @package Nord\Shipfunk\Model\Config\Source
 */
class PackageCardSize implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'A4', 'label' => 'A4'],
            ['value' => 'A5', 'label' => 'A5'],
            ['value' => '4x6', 'label' => '4x6'],
        ];
    }

}