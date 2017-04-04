<?php
namespace Nord\Shipfunk\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 *
 * @package Nord\Shipfunk\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $table = $setup->getConnection()
            ->newTable($setup->getTable('nord_shipfunk_pickups'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('pickup_id', Table::TYPE_TEXT, null, ['nullable' => false])
            ->addColumn('pickup', Table::TYPE_TEXT, null, ['nullable' => false]);

        $setup->getConnection()->createTable($table);


        $table = $setup->getConnection()
            ->newTable($setup->getTable('nord_shipfunk_selected_pickup'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('quote_id', Table::TYPE_INTEGER, null, ['nullable' => false])
            ->addColumn('pickup_id', Table::TYPE_TEXT, null, ['nullable' => false]);

        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}