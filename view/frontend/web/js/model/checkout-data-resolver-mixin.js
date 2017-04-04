/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'mage/utils/wrapper'
    ],
    function ($,
              wrapper) {
        'use strict';

        return function (targetX) {
            var newFunctionX = targetX.resolveShippingRates;
            newFunctionX     = wrapper.wrap(newFunctionX, function (resolveShippingRates) {
                var observedValX = resolveShippingRates();
                var shipping     = $('.table-checkout-shipping-method');

                if (shipping.length === 1) {
                    var methods = shipping.find('input');
                    if (methods.length === 1) {
                        var method = methods.first();

                        if (method.attr('disabled') === 'disabled' && method.prop('checked') === true) {
                            method.prop('checked', false);
                            method.removeAttr('disabled');
                        }
                    }
                }

                return observedValX;
            });

            targetX.resolveShippingRates = newFunctionX;

            return targetX;
        };
    }
);
