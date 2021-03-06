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
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Customer/js/model/address-list',
        'Magento_Ui/js/modal/alert',
        'mage/translate',
        'mage/storage',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, ko, modal, checkoutData, quote, resourceUrlManager, addressList, alert, $t, storage, fullScreenLoader) {
        'use strict';
        var carrierData = ko.observable();
        var carriercode = ko.observable();
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
                fullScreenLoader.startLoader();
                storage.post(
                    resourceUrlManager.getUrlForGetPickupPoints(quote),
                    JSON.stringify({query: JSON.stringify(sf_data)})
                ).done(
                    function (response) {
                        var responseJson = JSON.parse(response.response);
                        response = responseJson.response;
                      
                        if (response !== undefined && response.length) {
                            self.showModal();
                            shippingPoints(response);
                        }
                        else {
                            shippingPoints(null);
                            selectedPickup(false);
                        }
                        shippingPoints.valueHasMutated();
                        fullScreenLoader.stopLoader();
                    }
                ).fail(
                    function (response) {
                        fullScreenLoader.stopLoader();
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
                fullScreenLoader.startLoader();
                storage.post(
                    resourceUrlManager.getUrlForSelectedDelivery(quote),
                    JSON.stringify({query: JSON.stringify(selectedData)})
                ).done(
                    function (response) {
                        var responseJson = JSON.parse(response.response);
                        response = responseJson.response;
                        if (point) {
                            selectedPickup(point);
                            self.hideModal();
                        }
                        fullScreenLoader.stopLoader();
                    }
                ).fail(
                    function (response) {
                        alert({
                            content: $t("Something went wrong test")
                        });
                        fullScreenLoader.stopLoader();
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
