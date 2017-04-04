<?php
namespace Nord\Shipfunk\Plugin;

use Magento\Checkout\Block\Checkout\LayoutProcessor;

class CheckoutLayoutProcessor
{
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        LayoutProcessor $subject,
        array  $jsLayout
    ) {

        $code = 'email';
        $field = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/hidden',
                'id'        => 'address-email'
            ],
            'dataScope' => 'shippingAddress.email',
            //'label' => 'Shadow Email Field',
            'provider' => 'checkoutProvider',
            'sortOrder' => 0,
            'filterBy' => null,
            'customEntry' => null,
            'visible' => false,
            'id'        => 'address-email'
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$code] = $field;

        return $jsLayout;
    }
}