<?php

namespace Nord\Shipfunk\Model\ParcelResource;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ParcelTemplates
 *
 * @package Nord\Shipfunk\Model\ParcelResource
 */
class ParcelTemplates extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('nord_shipfunk_parcel_templates', 'nord_shipfunk_parcel_template_id');
    }
}