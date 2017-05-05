define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, quote, alert, $t) {
    'use strict';

    var mixin = {
        getShippingMethodTitle: function () {
            if (!this.isCalculated()) {
                return '';
            }
            var shippingMethod = quote.shippingMethod();
            var quoteId = quote.getQuoteId();
            var data = ['select', true, quoteId, 'true'];

            mixin.createPickupBox();

            $.ajax({
                type: "POST",
                url: window.shipfunkPopup.baseUrl + "shipfunk/index/index",
                timeout: 5000, // 5 second timeout in millis!
                data: {'data': data},
                dataType: "json",
                success: function (data, textStatus, jqXHR) {
                    if (data) {
                        mixin.createPickupBox();
                        $('#pickup').html(data);
                    }
                    else {
                        console.debug('no');
                        $('#pickupLocation').remove();
                    }
                },
                error: function (jqXHR, textStatus) {
                    $('#pickupLocation').remove();
                }
            });

            return shippingMethod ? shippingMethod.carrier_title + " - " + shippingMethod.method_title : '';
        },
        createPickupBox: function () {
            if ($('#pickupLocation').length === 0) {
                var shippingInformation = $('.ship-to').parent();
                $('<div id="pickupLocation" class="ship-to"><div class="shipping-information-title"><span>Pickup:</span></div><div class="shipping-information-content" id="pickup"></div></div>').prependTo(shippingInformation);
            }
        }

    };

    return function (target) {
        return target.extend(mixin);
    };
});
