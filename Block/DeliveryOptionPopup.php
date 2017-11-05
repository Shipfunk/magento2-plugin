<?php

namespace Nord\Shipfunk\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Nord\Shipfunk\Helper\Data as ShipfunkDataHelper;

/**
 * Class DeliveryOptionPopup
 *
 * @package Nord\Shipfunk\Block
 */
class DeliveryOptionPopup extends Template
{
    /**
     * @var array
     */
    protected $jsLayout;

    protected $log;
  
    protected $helper;

    /**
     * DeliveryOptionPopup constructor.
     *
     * @param Context            $context
     * @param ShipfunkDataHelper $shipfunkDataHelper
     * @param array              $data
     */
    public function __construct(
        Context $context,
        ShipfunkDataHelper $shipfunkDataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $shipfunkDataHelper;
        $this->log      = $context->getLogger();
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        return \Zend_Json::encode($this->jsLayout);
    }

    /**
     * Returns popup config
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            //'apiKey'     => $this->helper->getConfigData('test_mode') ? $this->helper->getConfigData('test_api_key') : $this->helper->getConfigData('live_api_key'),
            //'apiUrl'     => $this->helper->getConfigData('api_url'),
            'baseUrl'    => $this->getBaseUrl(),
        ];
    }

    /**
     * Return base url.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}