<?php

namespace Nord\Shipfunk\Plugin;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Checkout\Api\Data\ShippingInformationExtensionFactory;
use Nord\Shipfunk\Model\Quote\SelectedPickupFactory;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Framework\App\ObjectManager;

class ShippingInformationManagementPlugin
{
    /**
     * @var \Magento\Checkout\Api\Data\ShippingInformationExtensionFactory
     */
    private $shippingInformationExtensionFactory;
  
    /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory
     */
    private $cartExtensionFactory;
  
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
  
    /**
     * @var \Nord\Shipfunk\Model\Quote\SelectedPickupFactory
     */
    protected $quoteSelectedPickupFactory;
  
     private $logger;
  
    /**
     * Constructor
     *
     * @param ShippingInformationExtensionFactory|null $shippingInformationExtensionFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param SelectedPickupFactory $quoteSelectedPickupFactory
     * @param CartExtensionFactory|null $cartExtensionFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ShippingInformationExtensionFactory $shippingInformationExtensionFactory = null,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Nord\Shipfunk\Model\Quote\SelectedPickupFactory $quoteSelectedPickupFactory = null,
        CartExtensionFactory $cartExtensionFactory = null
    ) {
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->cartExtensionFactory = $cartExtensionFactory ?: ObjectManager::getInstance()
            ->get(CartExtensionFactory::class);
        $this->shippingInformationExtensionFactory = $shippingInformationExtensionFactory ?: ObjectManager::getInstance()
            ->get(ShippingInformationExtensionFactory::class);
        $this->quoteSelectedPickupFactory = $quoteSelectedPickupFactory ?: ObjectManager::getInstance()
            ->get(SelectedPickupFactory::class);
    }
  
    public function afterSaveAddressInformation(ShippingInformationManagementInterface $subject, $result, $cartId, $addressInformation)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }
        // old state
        $selectedPickup = $cartExtension->getSelectedPickup();
        $isPickupPresent = $selectedPickup && $selectedPickup->getSelectedPickupId();
        // new state
        $shippingInformationExtension = $addressInformation->getExtensionAttributes();
        $isPickupSubmitted = $shippingInformationExtension && $shippingInformationExtension->getPickupName();
       // $this->logger->debug(var_export($isPickupSubmitted, true));
        // no changes
        if (!$isPickupPresent && !$isPickupSubmitted) {
            return $result;
        }
        // action needed
        $quoteSelectedPickup = $this->quoteSelectedPickupFactory->create();
        // delete needed
        if (!$isPickupSubmitted) {
            $quoteSelectedPickup->load($selectedPickup->getSelectedPickupId())->delete();
            //$cartExtension->setSelectedPickup(null);
            //$quote->setExtensionAttributes($cartExtension);
            return $result;
        }
        // update (or creation) needed
        if (!$isPickupPresent || $selectedPickup->getPickupId() != $shippingInformationExtension->getPickupId()) {
            if ($selectedPickup) {
                $quoteSelectedPickup->load($selectedPickup->getSelectedPickupId());
            }
            $quoteSelectedPickup->setPickupName($shippingInformationExtension->getPickupName())
                              ->setPickupAddress($shippingInformationExtension->getPickupAddr())
                              ->setPickupPostcode($shippingInformationExtension->getPickupPostal())
                              ->setPickupCity($shippingInformationExtension->getPickupCity())
                              ->setPickupCountry($shippingInformationExtension->getPickupCountry())
                              ->setPickupId($shippingInformationExtension->getPickupId())
                              ->setPickupOpeningHours($shippingInformationExtension->getPickupOpeninghours())
                              ->setPickupOpeningHoursException($shippingInformationExtension->getPickupOpeninghoursExcep())
                              ->setQuote($quote)
                              ->save();
          
        }
      
       // $this->logger->debug($pickupHash);
      
        return $result;
    }
}