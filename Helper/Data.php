<?php
namespace Nord\Shipfunk\Helper;

use Magento\Framework\App\Helper\AbstractHelper,
    Magento\Store\Api\StoreResolverInterface,
    Magento\Framework\App\Helper\Context,
    Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Nord\Shipfunk\Helper
 */
class Data extends AbstractHelper
{
    const SHIPFUNK_CONFIG_PREFIX = 'carriers/shipfunk';

    /**
     * @var StoreResolverInterface
     */
    protected $_storeResolver;

    /**
     * Data constructor.
     *
     * @param \Magento\Store\Api\StoreResolverInterface $storeResolver
     * @param \Magento\Framework\App\Helper\Context     $context
     */
    public function __construct(
        StoreResolverInterface $storeResolver,
        Context $context
    ) {
        $this->_storeResolver = $storeResolver;
        parent::__construct($context);
    }

    /**
     * @param $field
     *
     * @return mixed
     */
    public function getConfigData($field)
    {
        $path = self::SHIPFUNK_CONFIG_PREFIX.'/'.$field;

        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->_storeResolver->getCurrentStoreId()
        );
    }

    /**
     * @param $field
     *
     * @return bool
     */
    public function getConfigFlag($field)
    {
        $path = self::SHIPFUNK_CONFIG_PREFIX.'/'.$field;

        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->_storeResolver->getCurrentStoreId()
        );
    }
}