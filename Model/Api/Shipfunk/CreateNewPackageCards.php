<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

use Magento\Sales\Api\OrderItemRepositoryInterface;

/**
 * Class CreateNewPackageCards
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class CreateNewPackageCards extends AbstractEndpoint
{
    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;
  
    /**
     *
     * @param \Psr\Log\LoggerInterface    $logger
     * @param \Nord\Shipfunk\Helper\Data $shipfunkDataHelper
     * @param \Magento\Framework\HTTP\ZendClientFactory  $httpClientFactory
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Nord\Shipfunk\Helper\Data $shipfunkDataHelper,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        parent::__construct($logger, $shipfunkDataHelper, $httpClientFactory);
        $this->orderItemRepository = $orderItemRepository;
    }
  
    public function execute($query = [])
    {
        if (!$query) {
          $request = $this->getRequest(); 
          // decide here, based on available data, are we sending parcels or product codes
          $packages = $this->getPackages();
          
          $parcels = [];
          foreach ($packages as $pkgId => $pkg) {
              $skus = [];
              foreach ($pkg['items'] as $itemId => $itemData) {
                  $orderItem = $this->orderItemRepository->get($itemId);
                  $skus[] = $orderItem->getSku();
              }
              $parcels[] = [
                  'warehouse' => $this->helper->getConfigData('warehouse'),
                  'product_codes' => $skus  // @todo How is split qty per package affecting Shipfunk ? 
              ];
          }
          $query = [
             'query' => [
                'order' => [
                    'return_cards' => 1, // @todo should this be configurable ?
                    'parcels' => $parcels
                ],
                'customer' => [
                    'first_name' => $request->getRecipientContactPersonFirstName(),
                    'last_name' => $request->getRecipientContactPersonLastName(),
                    'street_address' => $request->getRecipientAddressStreet(),
                    'postal_code' => $request->getRecipientAddressPostalCode(),
                    'city' => $request->getRecipientAddressCity(),
                    'country' => $request->getRecipientAddressCountryCode(),
                    'phone' => $request->getRecipientContactPhoneNumber(),
                    'email' => $request->getRecipientEmail()
                ]
             ]
          ];
        }
      
      
        $query = utf8_encode(json_encode($query));
        $result = $this->setEndpoint('create_new_package_cards')->post($query);
      
        return $result;
    }
}
