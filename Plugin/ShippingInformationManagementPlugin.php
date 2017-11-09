<?php

namespace Nord\Shipfunk\Plugin;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Checkout\Api\Data\ShippingInformationExtensionFactory;
use Magento\Framework\App\ObjectManager;

class ShippingInformationManagementPlugin
{
    /**
     * @var \Magento\Checkout\Api\Data\ShippingInformationExtensionFactory
     */
    private $shippingInformationExtensionFactory;
  
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
  
    private $logger;
  
    /**
     * Constructor
     *
     * @param ShippingInformationExtensionFactory|null $shippingInformationExtensionFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ShippingInformationExtensionFactory $shippingInformationExtensionFactory = null,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;     
        $this->shippingInformationExtensionFactory = $shippingInformationExtensionFactory ?: ObjectManager::getInstance()
            ->get(ShippingInformationExtensionFactory::class);
    }
  
    public function afterSaveAddressInformation(ShippingInformationManagementInterface $subject, $result, $cartId, $addressInformation)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
      
        $shippingInformationExtension = $addressInformation->getExtensionAttributes();
        if ($shippingInformationExtension === null) {
            $shippingInformationExtension = $this->shippingInformationExtensionFactory->create();
        }

        $pickupName = $shippingInformationExtension->getPickupName();
        $pickupAddress = $shippingInformationExtension->getPickupAddr();
        $pickupPostcode = $shippingInformationExtension->getPickupPostal();
        $pickupCity = $shippingInformationExtension->getPickupCity();
        $pickupCountry = $shippingInformationExtension->getPickupCountry();
        $pickupId = $shippingInformationExtension->getPickupId();
        $pickupOpeningHours = $shippingInformationExtension->getPickupOpeninghours();
        $pickupOpeningHoursException = $shippingInformationExtension->getPickupOpeninghoursExcep();
        $pickupHash = implode('',[
          $pickupName, $pickupAddress, $pickupCity, $pickupCountry, $pickupId, $pickupOpeningHours, $pickupOpeningHoursException
        ]);
      
        
        $this->logger->debug($pickupHash);
      
        return $result;
    }
}