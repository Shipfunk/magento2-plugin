<?php

namespace Nord\Shipfunk\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\Data\ShippingMethodExtensionFactory;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ShippingMethodConverterPlugin
 *
 * @package Nord\Shipfunk\Plugin
 */
class ShippingMethodConverterPlugin
{
    /**
     * @var ShippingMethodExtensionFactory
     */
    protected $shippingMethodExtension;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ShippingMethodConverterPlugin constructor.
     *
     * @param ShippingMethodExtensionFactory $shippingMethodExtension
     * @param ScopeConfigInterface           $scopeConfig
     * @param StoreManagerInterface          $storeManager
     */
    public function __construct(
        ShippingMethodExtensionFactory $shippingMethodExtension,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->shippingMethodExtension = $shippingMethodExtension;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param ShippingMethodConverter $subject
     * @param                         $proceed
     * @param                         $rateModel
     * @param                         $quoteCurrencyCode
     *
     * @return mixed
     */
    public function aroundModelToDataObject(ShippingMethodConverter $subject, $proceed, $rateModel, $quoteCurrencyCode)
    {
        $return = $proceed($rateModel, $quoteCurrencyCode);
        $extensionModel = $this->shippingMethodExtension->create();
        $defaultCategory = "DEFAULT".$this->getConfigValue('category_default');
        $useCategorySorting = $this->getConfigValue('category_sorting');

        if ($rateModel->getCarrier() === 'shipfunk') {

            $x = explode("||", $rateModel->getMethodDescription());

            /** @noinspection PhpUndefinedMethodInspection */
            $extensionModel->setMethodDescription($x[0]);

            $category = empty($x[1]) ? $defaultCategory : $x[1];

            if (empty($useCategorySorting)) {
                $category = 'ZZZZZZ';
            }

            /** @noinspection PhpUndefinedMethodInspection */
            $extensionModel->setCategory($category);
            /** @noinspection PhpUndefinedMethodInspection */
            $extensionModel->setDelivtime($x[2]);

            /** @noinspection PhpUndefinedMethodInspection */
        } else {

            if (empty($useCategorySorting)) {
                $defaultCategory = 'ZZZZZZ';
            }
            /** @noinspection PhpUndefinedMethodInspection */
            $extensionModel->setCategory($defaultCategory);
        }

        return $return->setExtensionAttributes($extensionModel);
    }

    /**
     * @param $field
     *
     * @return mixed
     */
    protected function getConfigValue($field)
    {
        $path = 'carriers/shipfunk/'.$field;
        $store = $this->storeManager->getStore();

        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
