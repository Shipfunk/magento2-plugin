<?php

namespace Nord\Shipfunk\Plugin;

use Magento\Quote\Api\Data\ShippingMethodExtensionFactory;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Nord\Shipfunk\Helper\Data;

class ShippingMethodConverterPlugin
{
    /**
     * @var ShippingMethodExtensionFactory
     */
    protected $shippingMethodExtension;

    /**
     * @var Data
     */
    protected $_heler;

    /**
     * ShippingMethodConverterPlugin constructor.
     *
     * @param ShippingMethodExtensionFactory $shippingMethodExtension
     * @param Data           $helper
     */
    public function __construct(
        ShippingMethodExtensionFactory $shippingMethodExtension,
        Data $helper
    ) {
        $this->shippingMethodExtension = $shippingMethodExtension;
        $this->_helper = $helper;
    }

    /**
     * @param ShippingMethodConverter $subject
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface $proceed
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     *
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface
     */
    public function afterModelToDataObject(ShippingMethodConverter $subject, $result, $rateModel)
    {
        $errorMessage = $rateModel->getErrorMessage();
        $extensionModel = $this->shippingMethodExtension->create();
        $defaultCategory = "DEFAULT".$this->_helper->getConfigData('category_default');
        $useCategorySorting = $this->_helper->getConfigData('category_sorting');

        if ($rateModel->getCarrier() === 'shipfunk' && empty($errorMessage)) {
            $x = explode("||", $rateModel->getMethodDescription());
            $extensionModel->setMethodDescription($x[0]);
            $category = empty($x[1]) ? $defaultCategory : $x[1];
            if (empty($useCategorySorting)) {
                $category = 'ZZZZZZ';
            }
            $extensionModel->setCategory($category);
            $extensionModel->setDelivtime($x[2]);
        } else {
            if (empty($useCategorySorting)) {
                $defaultCategory = 'ZZZZZZ';
            }
            $extensionModel->setCategory($defaultCategory);
        }

        return $result->setExtensionAttributes($extensionModel);
    }
}
