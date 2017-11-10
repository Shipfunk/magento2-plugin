<?php

namespace Nord\Shipfunk\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;

/**
 * Class Custom
 * @package Nord\Shipfunk\Block\Adminhtml\Order\View
 */
class Custom extends Template
{
    /**
     * @var []
     */
    protected $pickup;

    /**
     * Custom constructor.
     *
     * @param Template\Context  $context
     * @param PickupInformation $pickupInformation
     * @param array             $data
     */
    public function __construct(Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function hasPickupInformation()
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function getPickupInformation()
    {
        return $this->pickup;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please correct the parent block for this block.')
            );
        }
        $this->setOrder($this->getParentBlock()->getOrder());

        foreach ($this->getParentBlock()->getOrderInfoData() as $key => $value) {
            $this->setDataUsingMethod($key, $value);
        }

        parent::_beforeToHtml();
    }
}