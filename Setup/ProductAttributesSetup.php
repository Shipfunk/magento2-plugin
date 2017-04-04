<?php

namespace Nord\Shipfunk\Setup;

use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;

/**
 * Class ProductAttributesSetup
 *
 * @package Nord\Shipfunk\Setup
 */
class ProductAttributesSetup extends EavSetup
{
    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * ProductAttributesSetup constructor.
     *
     * @param ModuleDataSetupInterface $setup
     * @param Context                  $context
     * @param CacheInterface           $cache
     * @param CollectionFactory        $attrGroupCollectionFactory
     * @param Config                   $eavConfig
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Context $context,
        CacheInterface $cache,
        CollectionFactory $attrGroupCollectionFactory,
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
        parent::__construct($setup, $context, $cache, $attrGroupCollectionFactory);
    }

    /**
     * @return Config
     */
    public function getEavConfig()
    {
        return $this->eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultEntities()
    {
        $entities = [
            \Magento\Catalog\Model\Product::ENTITY => [
                'attributes' => [
                    'shipfunk_length' => [
                        'remove' => true,
                        'add' => [
                            'type' => 'decimal',
                            'label' => 'Length',
                            'input' => 'text',
                            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                            'required'  => false,
                            'sort_order' => 4,
                            'group' => 'General Information',
                            'user_defined' => true
                        ],
                        'set' => []
                    ],
                    'shipfunk_width' => [
                        'remove' => true,
                        'add' => [
                            'type' => 'decimal',
                            'label' => 'Width',
                            'input' => 'text',
                            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                            'required'  => false,
                            'sort_order' => 4,
                            'group' => 'General Information',
                            'user_defined' => true
                        ],
                        'set' => []
                    ],
                    'shipfunk_depth' => [
                        'remove' => true,
                        'add' => [
                            'type' => 'decimal',
                            'label' => 'Depth',
                            'input' => 'text',
                            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                            'required'  => false,
                            'sort_order' => 4,
                            'group' => 'General Information',
                            'user_defined' => true
                        ],
                        'set' => []
                    ]
                ]
            ]
        ];
        return $entities;
    }

    /**
     * {@inheritdoc}
     */
    public function installProductAttributes()
    {
        $this->cleanCache();
 
        $attributes   = $this->getDefaultEntities();
        foreach ($attributes as $entityType => $entityData ) {
            $defaultSetId = $this->getDefaultAttributeSetId($entityType);
            $defaultGroupId = $this->getDefaultAttributeGroupId($entityType, $defaultSetId);
            foreach ((array) $entityData['attributes'] as $attrCode => $attrData ) {
                if (in_array('remove', array_keys($attrData)) && $attrData['remove'] ) {
                    $this->removeAttribute($entityType, $attrCode);
                }

                if (in_array('add', array_keys($attrData)) && $attrData['add'] ) {
                    $attrData['add']['attribute_set_id'] = $defaultSetId;
                    //$groupId = $this->getAttributeGroupId($entityType, $defaultSetId, $attrData['add']['group']);
                    $attrData['add']['attribute_group_id'] = $defaultGroupId;
                    $this->addAttribute($entityType, $attrCode, $attrData['add']);
                }
            }
        }
    }
}