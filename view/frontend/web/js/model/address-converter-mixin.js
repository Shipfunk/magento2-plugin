/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/checkout-data',
], function ($, wrapper, checkoutData) {
    'use strict';

    return function (targetX) {
        var newFunctionX                      = targetX.formAddressDataToQuoteAddress;
        newFunctionX                          = wrapper.wrap(newFunctionX, function (originalActionX) {
            var observedValX   = originalActionX();
            observedValX.email = checkoutData.getValidatedEmailValue();
            return observedValX;
        });
        targetX.formAddressDataToQuoteAddress = newFunctionX;

        var newFunctionY                   = targetX.formDataProviderToFlatData;
        newFunctionY                       = wrapper.wrap(newFunctionY, function (originalActionY) {
            var observerValY   = originalActionY();
            observerValY.email = checkoutData.getValidatedEmailValue();
            return observerValY;
        });
        targetX.formDataProviderToFlatData = newFunctionY;

        return targetX;
    };
});