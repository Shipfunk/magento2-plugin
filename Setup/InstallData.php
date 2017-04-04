<?php

namespace Nord\Shipfunk\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Psr\Log\LoggerInterface;

/**
 * Class InstallData
 *
 * @package Nord\Shipfunk\Setup
 */
class InstallData implements InstallDataInterface
{
    private $log;
    private $productAttributesSetupFactory;

    /**
     * InstallData constructor.
     *
     * @param LoggerInterface               $log
     * @param ProductAttributesSetupFactory $productAttributesSetupFactory
     */
    public function __construct(
        LoggerInterface $log,
        ProductAttributesSetupFactory $productAttributesSetupFactory
    ) {
        $this->log = $log;
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

        $tableName = 'nord_shipfunk_pickups';

        $setup->endSetup();
    }
}