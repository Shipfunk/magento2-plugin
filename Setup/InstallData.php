<?php

namespace Nord\Shipfunk\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 *
 * @package Nord\Shipfunk\Setup
 */
class InstallData implements InstallDataInterface
{
    private $productAttributesSetupFactory;

    /**
     * InstallData constructor.
     *
     * @param ProductAttributesSetupFactory $productAttributesSetupFactory
     */
    public function __construct(
        ProductAttributesSetupFactory $productAttributesSetupFactory
    ) {
        $this->productAttributesSetupFactory = $productAttributesSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $productSetup = $this->productAttributesSetupFactory->create(['setup' => $setup]);
        $setup->startSetup();
        $productSetup->installProductAttributes();
        $setup->endSetup();
    }
}