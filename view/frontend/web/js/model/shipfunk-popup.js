/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'ko',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/address-list',
        'Magento_Ui/js/modal/alert',
        'mage/translate',
        'mage/storage'
    ],
    function ($, ko, modal, checkoutData, quote, addressList, alert, $t, storage) {
        'use strict';
        var carrierData = ko.observable();
        var carriercode = ko.observable();
        var productcode = ko.observable();
        var product_description = ko.observable();
        var price = ko.observable();
        var selectedPickup = ko.observable(false);
        var selectedPickupId = ko.computed(function() {
            var sel = selectedPickup();
            return sel.pickup_id;
        });
        var shippingPoints = ko.observableArray([]);

        return {
            modalWindow: null,

            /** Create popUp window for provided element */
            createPopUp: function (element) {
                this.modalWindow = element;
                var options = {
                    'type': 'popup',
                    'modalClass': 'popup-shipfunk',
                    'responsive': true,
                    'innerScroll': true,
                    'buttons': []
                };
                modal(options, $(this.modalWindow));
            },

            showModal: function () {
                $(this.modalWindow).modal('openModal');
            },

            hideModal: function () {
                $(this.modalWindow).modal('closeModal');
            },

            setCarrierData: function (method) {
                var self = this;
                var methodCodeArray = method.method_code.split('_');

                if (!checkoutData.getSelectedShippingRate()) {
                    return;
                }

                var address = quote.shippingAddress(); // this alone should be safe bet
                if (!address) {
                    address = checkoutData.getShippingAddressFromData();
                }

                carriercode(methodCodeArray[1]);
                carriercode.valueHasMutated();
                productcode(methodCodeArray[2]);
                productcode.valueHasMutated();
                price(method.amount);
                price.valueHasMutated();

                product_description(method.extension_attributes.method_description);
                product_description.valueHasMutated();

                var sf_data = {
                    "order": {
                      "carriercode": methodCodeArray[1],
                      "return_count": 15
                    },
                    "customer": {
                      "postal_code": address.postcode,
                      "country": address.countryId ? address.countryId : address.country_id
                    }
                };
                
                storage.post(
                    window.shipfunkPopup.baseUrl + "rest/all/V1/shipfunk/" + window.checkoutConfig.quoteData.entity_id + "/get-pickup-points",
                    JSON.stringify({query: JSON.stringify(sf_data)})
                ).done(
                    function (response) {
                        var response = JSON.parse(response.response);
                        response = response.response;
                      
                        if (response !== undefined && response.length) {
                            self.showModal();
                            shippingPoints(response);
                        }
                        else {
                            shippingPoints(null);
                            selectedPickup(false);
                        }
                        shippingPoints.valueHasMutated();
                    }
                ).fail(
                    function (response) {
                        
                    }
                );
            },
          
            selectDelivery: function (point = false) {
                var self = this;
                var selectedData = {
                    "order": {
                      "selected_option": {
                        "carriercode": carriercode(),
                        "pickupid": point ? point.pickup_id : "",
                        "calculated_price": price(),
                        "customer_price": price(),
                        "return_prices": "1"
                      }
                    }
                };

                storage.post(
                    window.shipfunkPopup.baseUrl + "rest/all/V1/shipfunk/" + window.checkoutConfig.quoteData.entity_id + "/selected-delivery",
                    JSON.stringify({query: JSON.stringify(selectedData)})
                ).done(
                    function (response) {
                        var response = JSON.parse(response.response);
                        response = response.response;
                        if (point) {
                            selectedPickup(point);
                            self.hideModal();
                        }
                    }
                ).fail(
                    function (response) {
                        alert({
                            content: $t("Something went wrong test")
                        });
                    }
                );
            },
           
            getShippingPoints: function () {
                return shippingPoints;
            },

            getCarrierData: function () {
                return carrierData;
            },

            getCarrierCode: function () {
                return carriercode;
            },

            getProductCode: function () {
                return productcode;
            },

            getSelectedPickup: function () {
                return selectedPickup;
            },
          
            getSelectedPickupId: function () {
                return selectedPickupId;
            },

            getProductDescription: function () {
                return product_description;
            },

            getPrice: function () {
                return price;
            }
        }
    }
);
