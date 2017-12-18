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
        'Magento_Ui/js/form/form',
        'Magento_Checkout/js/checkout-data',
        'Nord_Shipfunk/js/model/shipfunk-popup',
        'mage/translate',
        'mage/url',
        'Magento_Ui/js/modal/alert',
        'mage/validation'
    ],
    function ($, ko, Component, /*loginAction, */ checkoutData, shipfunkPopup, $t, url, alert) {
        'use strict';

        return Component.extend({
            webshopid: window.shipfunkPopup.webshopid,
            apiUrl: window.shipfunkPopup.apiUrl,
            modalWindow: null,
            isLoading: ko.observable(false),
            description: shipfunkPopup.getProductDescription(),
            selectedPickupId: shipfunkPopup.getSelectedPickupId(),
            shippingPoints: shipfunkPopup.getShippingPoints(),

            defaults: {
                template: 'Nord_Shipfunk/shipfunk-popup'
            },

            /**
             * Init
             */
            initialize: function () {
                var self = this;
                this._super();
            },

            /** Init popup window */
            setModalElement: function (element) {
                if (shipfunkPopup.modalWindow == null) {
                    shipfunkPopup.createPopUp(element);
                }
            },

            /** Show popup window */
            showModal: function () {
                if (this.modalWindow) {
                    $(this.modalWindow).modal('openModal');
                } else {
                    alert({
                        content: $t('Points selection is disabled.')
                    });
                }
            },

            selectPickupPoint: function (pickupPoint) {
                shipfunkPopup.selectDelivery(pickupPoint);
                return true;
            }
        });
    }
);
