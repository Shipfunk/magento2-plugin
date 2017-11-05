<?php

namespace Nord\Shipfunk\Model\Api\Shipfunk;

use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;
use Nord\Shipfunk\Helper\Data as ShipfunkDataHelper;
use Magento\Framework\HTTP\ZendClientFactory;

/**
 * Class SelectedDelivery
 *
 * @package Nord\Shipfunk\Model\Api\Shipfunk
 */
class SelectedDelivery extends AbstractEndpoint
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     *
     * @param LoggerInterface    $logger
     * @param ShipfunkDataHelper $shipfunkDataHelper
     * @param ZendClientFactory  $httpClientFactory
     * @param CheckoutSession    $checkoutSession
     */
    public function __construct(
        LoggerInterface $logger,
        ShipfunkDataHelper $shipfunkDataHelper,
        ZendClientFactory $httpClientFactory,
        CheckoutSession $checkoutSession
    ) {
        parent::__construct($logger, $shipfunkDataHelper, $httpClientFactory);
        $this->checkoutSession = $checkoutSession;
    }
  
    public function execute($query = [])
    {
        if (!$query) {
          $query = [
             'query' => [
                'order' => [
                  'selected_option' => [
                    'carriercode' => $this->getCarrierCode(),
                    'pickupid' => $this->getPickupId(),
                    'calculated_price' => $this->getCalculatedPrice(),
                    'customer_price' => $this->getCustomerPrice(),
                    'return_prices' => $this->getReturnPrice()
                  ]
                ]
             ]
          ];
        }
      
        $query = utf8_encode(json_encode($query));
        $quoteId = $this->checkoutSession->getQuote()->getId();
        $this->setEndpoint('selected_delivery');
        if (!$this->getOrderId() && $quoteId) {
          $this->setOrderId($quoteId);
        }
        $result = $this->get($query);
      
        return $result;
    }
}