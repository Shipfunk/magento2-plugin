<?php

namespace Nord\Shipfunk\Model\Resource\ParcelTemplates;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Nord\Shipfunk\Model\Resource\ParcelTemplates
 */
class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Nord\Shipfunk\Model\ParcelTemplates',
            'Nord\Shipfunk\Model\Resource\ParcelTemplates'
        );
    }
}