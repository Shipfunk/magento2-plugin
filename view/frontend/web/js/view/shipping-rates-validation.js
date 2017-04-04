/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Nord_Shipfunk/js/model/shipping-rates-validator',
        'Nord_Shipfunk/js/model/shipping-rates-validation-rules'
    ],
    function (Component,
              defaultShippingRatesValidator,
              defaultShippingRatesValidationRules,
              shipfunkShippingRatesValidator,
              shipfunkShippingRatesValidationRules) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('shipfunk', shipfunkShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('shipfunk', shipfunkShippingRatesValidationRules);

        return Component;
    }
);
