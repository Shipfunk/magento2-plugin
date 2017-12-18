define([
    'jquery',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/checkout-data',
    'Nord_Shipfunk/js/model/shipfunk-popup',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service'
], function ($,
             selectShippingMethodAction,
             checkoutData,
             shipfunkPopupModel,
             alert,
             $t,
             wrapper,
             quote,
             shippingService) {
    'use strict';

    var shippingPoints = shipfunkPopupModel.getShippingPoints();
    var selectedPickup = shipfunkPopupModel.getSelectedPickup();
    var selectedPointId = shipfunkPopupModel.getSelectedPickupId();

    var mixin = {
        selectShippingMethod: function (shippingMethod) {
            var result = this._super();

            if (shippingMethod && 'shipfunk' == shippingMethod.carrier_code) {
                shipfunkPopupModel.setCarrierData(shippingMethod);
            } else {
                shippingPoints(null);
                selectedPickup(false);
            }

            return result;
        },
        validateShippingInformation: function () {
            var result = this._super();
            if (!result) {
                return result;
            }

            var selectedMethod = quote.shippingMethod();
            if ('shipfunk' !== selectedMethod.carrier_code) {
                return result;
            }
            var carrierPoints = shippingPoints();
            if (carrierPoints !== undefined && carrierPoints !== null) {
                var selectedPoint = carrierPoints.filter(function (obj) {
                    return obj.pickup_id == selectedPointId();
                });
                if (!selectedPoint.length) {
                    alert({
                        content: $t("Please select shipping method and/or pickup point.")
                    });
                    return false;
                }
                return true;
            } else {
                // option without pickup point
                shipfunkPopupModel.selectDelivery();
            }

            return true;
        },
        categories: shippingService.getShippingCategories()
    };

    return function (target) {
        return target.extend(mixin);
    };
});