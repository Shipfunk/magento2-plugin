define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Nord_Shipfunk/js/model/shipfunk-popup',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, quote, shipfunkPopupModel, alert, $t) {
    'use strict';

    var selectedPoint = shipfunkPopupModel.getSelectedPickup();
  
    var mixin = {
        getShippingMethodTitle: function () {
            if (!this.isCalculated()) {
                return '';
            }
            var shippingMethod = quote.shippingMethod();
            var quoteId = quote.getQuoteId();
            var selected = selectedPoint();
            //if (selected) {
                mixin.createPickupBox();
                $('#pickup').html(selected.pickup_name);
            //}

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
