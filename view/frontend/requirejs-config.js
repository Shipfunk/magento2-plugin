/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Nord_Shipfunk/js/view/shipping-mixin': true
            },
            'Magento_Checkout/js/model/address-converter': {
                'Nord_Shipfunk/js/model/address-converter-mixin': true
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Nord_Shipfunk/js/model/checkout-data-resolver-mixin': true
            },
            'Magento_Checkout/js/view/summary/shipping': {
                'Nord_Shipfunk/js/view/summary-shipping-mixin': true
            }
        }
    },
    map: {
        "*": {
            "Magento_Checkout/template/shipping.html": "Nord_Shipfunk/template/shipping.html",
            "Magento_Checkout/js/model/shipping-service": "Nord_Shipfunk/js/model/shipping-service-mixin"
        }
    },
    deps: [
        "Nord_Shipfunk/js/main"
    ]
};
