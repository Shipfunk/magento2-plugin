<?php

namespace Nord\Shipfunk\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class ProductAttributes
 *
 * @package Nord\Shipfunk\Model\Config\Source
 */
class ProductAttributes implements ArrayInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * ProductAttributes constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Use filter 4 for product attributes only
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributes = [];
        $coll       = $this->_objectManager->create(Collection::class);
        $coll->addFieldToFilter(Set::KEY_ENTITY_TYPE_ID, 4);
        $attrCollection = $coll->load()->getItems();

        /**
         * @var \Magento\Eav\Model\Entity\Attribute $attr
         */
        foreach ($attrCollection as $attr) {
            $attributes[] = ['value' => $attr->getAttributeCode(), 'label' => $attr->getDefaultFrontendLabel()];
        }

        $attributes = $this->sortArrayByColumn($attributes, 'label');

        return $attributes;
    }

    /**
     * @param array  $arr
     * @param string $col
     * @param int    $dir
     *
     * @return array
     */
    protected function sortArrayByColumn($arr, $col, $dir = SORT_ASC)
    {
        $sortCol = [];
        foreach ($arr as $key => $row) {
            $sortCol[$key] = $row[$col];
        }

        array_multisort($sortCol, $dir, $arr);

        return $arr;
    }
}