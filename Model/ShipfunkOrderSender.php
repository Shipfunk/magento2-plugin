<?php

namespace Nord\Shipfunk\Model;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use Nord\Shipfunk\Block\Adminhtml\Order\View\Custom;

/**
 * Class ShipfunkOrderSender
 * @package Nord\Shipfunk\Model
 */
class ShipfunkOrderSender extends Sender\OrderSender
{
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var OrderResource
     */
    protected $orderResource;

    /**
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var Custom
     */
    protected $pickupInformation;

    /**
     * ShipfunkOrderSender constructor.
     *
     * @param Template                                           $templateContainer
     * @param OrderIdentity                                      $identityContainer
     * @param Order\Email\SenderBuilderFactory                   $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface                           $logger
     * @param Renderer                                           $addressRenderer
     * @param PaymentHelper                                      $paymentHelper
     * @param OrderResource                                      $orderResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param ManagerInterface                                   $eventManager
     * @param Custom                                             $pickupInformation
     */
    public function __construct(
        Template $templateContainer,
        OrderIdentity $identityContainer,
        Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        OrderResource $orderResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager,
        Custom $pickupInformation
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $senderBuilderFactory,
            $logger,
            $addressRenderer,
            $paymentHelper,
            $orderResource,
            $globalConfig,
            $eventManager
        );

        $this->pickupInformation = $pickupInformation;
    }

    /**
     * Prepare email template with variables
     *
     * @param Order $order
     *
     * @return void
     */
    protected function prepareTemplate(Order $order)
    {
        $transport = [
            'order'                    => $order,
            'billing'                  => $order->getBillingAddress(),
            'payment_html'             => $this->getPaymentHtml($order),
            'store'                    => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress'  => $this->getFormattedBillingAddress($order),
            'formattedPickupAddress'   => $this->getFormattedPickupAddress($order),
        ];
        $transport = new \Magento\Framework\DataObject($transport);

        $this->eventManager->dispatch(
            'email_order_set_template_vars_before',
            ['sender' => $this, 'transport' => $transport]
        );

        $this->templateContainer->setTemplateVars($transport->getData());

        Sender::prepareTemplate($order);
    }

    /**
     * @param $order
     *
     * @return bool|null|string
     */
    protected function getFormattedPickupAddress($order)
    {
        $this->pickupInformation->setOrder($order);

        if ($this->pickupInformation->hasPickupInformation()) {
            return $this->pickupInformation->getPickupInformation();
        } else {
            return false;
        }
    }
}
