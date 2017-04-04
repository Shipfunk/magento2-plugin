<?php

namespace Nord\Shipfunk\Plugin\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView,
    Magento\Framework\UrlInterface,
    Magento\Framework\AuthorizationInterface,
    Magento\Backend\Block\Template\Context;

/**
 * Class View
 *
 * @package Nord\Shipfunk\Plugin\Block\Adminhtml\Order
 */
class View
{
    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * View constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->urlBuilder    = $context->getUrlBuilder();
        $this->authorization = $context->getAuthorization();

    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     *
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->authorization->isAllowed($resourceId);
    }

    /**
     * @param OrderView $view
     */
    public function beforeSetLayout(OrderView $view)
    {
        $order = $view->getOrder();

        if ($this->_isAllowedAction('Magento_Sales::ship') && $order->canShip() && !$order->getForcedShipmentWithInvoice()) {
            $message = 'Products will be packed into the parcels by an automatic box packer which could make mistakes, if the product details are not suffiecient or the product dimensions are somehow difficult for the algorithm';
            $url     = $this->urlBuilder->getUrl('shipfunk/boxpacker', ['id' => $view->getOrderId()]);

            $view->addButton(
                'order_myaction',
                [
                    'label'   => __('Ship with BoxPacker'),
                    'class'   => 'shipWithBoxpacker',
                    'onclick' => "confirmSetLocation('{$message}', '{$url}')",
                ]
            );
        } else {
            return;
        }

    }
}