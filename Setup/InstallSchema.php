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
            ->newTable($setup->getTable('quote_selected_pickup'))
            ->addColumn(
                'selected_pickup_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Identifier'
            )
            ->addColumn('pickup_name', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('pickup_addr', Table::TYPE_TEXT, 255, ['nullable' => true])
            ->addColumn('pickup_postal', Table::TYPE_TEXT, 255, ['nullable' => true])
            ->addColumn('pickup_city', Table::TYPE_TEXT, 255, ['nullable' => true])
            ->addColumn('pickup_country', Table::TYPE_TEXT, 255, ['nullable' => true])
            ->addColumn('pickup_id', Table::TYPE_TEXT, 255, ['nullable' => true])
            ->addColumn('pickup_openinghours', Table::TYPE_TEXT, 255, ['nullable' => true])
            ->addColumn('pickup_openinghours_excep', Table::TYPE_TEXT, 255, ['nullable' => true])
            ->addColumn('quote_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'Quote ID')
            ->addIndex($setup->getIdxName('quote_selected_pickup', ['quote_id']), ['quote_id'])
            ->addIndex(
                $setup->getIdxName(
                    'quote_cgi_delivery_terms',
                    ['quote_id', 'pickup_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['quote_id', 'pickup_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $setup->getFkName('quote_selected_pickup', 'quote_id', 'quote', 'entity_id'),
                'quote_id',
                $setup->getTable('quote'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Quote Selected Pickup');
        $setup->getConnection()->createTable($table);


        $table = $setup->getConnection()
            ->newTable($setup->getTable('sales_order_selected_pickup'))
            ->addColumn(
                'selected_pickup_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Identifier'
            )
            ->addColumn('pickup_name', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('pickup_addr', Table::TYPE_TEXT, 255, ['nullable' => true])
            ->addColumn('pickup_postal', Table::TYPE_TEXT, 255, ['nullable' => true])
            ->addColumn('pickup_city', Table::TYPE_TEXT, 255, ['nullable' => true])
            ->addColumn('pickup_country', Table::TYPE_TEXT, 255, ['nullable' => true])
            ->addColumn('pickup_id', Table::TYPE_TEXT, 255, ['nullable' => true])
            ->addColumn('pickup_openinghours', Table::TYPE_TEXT, 255, ['nullable' => true])
            ->addColumn('pickup_openinghours_excep', Table::TYPE_TEXT, 255, ['nullable' => true])
            ->addColumn('order_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'Order ID')
            ->addIndex($setup->getIdxName('sales_order_selected_pickup', ['order_id']), ['order_id'])
            ->addIndex(
                $setup->getIdxName(
                    'sales_order_selected_pickup',
                    ['order_id', 'pickup_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['order_id', 'pickup_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $setup->getFkName('sales_order_selected_pickup', 'order_id', 'sales_order', 'entity_id'),
                'order_id',
                $setup->getTable('sales_order'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Order Selected Pickup');
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}