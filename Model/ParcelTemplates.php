<?php

namespace Nord\Shipfunk\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class ParcelTemplates
 *
 * @package Nord\Shipfunk\Model
 */
class ParcelTemplates extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Nord\Shipfunk\Model\Resource\ParcelTemplates');
    }
}