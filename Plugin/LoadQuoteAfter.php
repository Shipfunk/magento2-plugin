<?php

namespace Nord\Shipfunk\Plugin;

use Magento\Quote\Api\CartRepositoryInterface;
use Nord\Shipfunk\Model\Quote\SelectedPickupFactory;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Framework\App\ObjectManager;

class LoadQuoteAfter
{
    /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var \Nord\Shipfunk\Model\Quote\SelectedPickupFactory
     */
    protected $quoteSelectedPickupFactory;
  
     private $logger;
  
    /**
     * Constructor
     *
     * @param SelectedPickupFactory $quoteSelectedPickupFactory
     * @param CartExtensionFactory|null $cartExtensionFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Nord\Shipfunk\Model\Quote\SelectedPickupFactory $quoteSelectedPickupFactory = null,
        CartExtensionFactory $cartExtensionFactory = null
    ) {
        $this->logger = $logger;
        $this->cartExtensionFactory = $cartExtensionFactory ?: ObjectManager::getInstance()
            ->get(CartExtensionFactory::class);
        $this->quoteSelectedPickupFactory = $quoteSelectedPickupFactory ?: ObjectManager::getInstance()
            ->get(SelectedPickupFactory::class);
    }
  
    // probably better if done as observer on load_after event
    public function afterGet(CartRepositoryInterface $subject, $result, $cartId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $result;
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }
        // should be rewriten not to use load() but either a resource-load or collection
        $quoteSelectedPickup = $this->quoteSelectedPickupFactory->create()->load($cartId, 'quote_id');
        $cartExtension->setSelectedPickup($quoteSelectedPickup);
        //$this->logger->debug(var_export($quoteSelectedPickup->debug(), true));
        $quote->setExtensionAttributes($cartExtension);
        return $quote;
    }
}