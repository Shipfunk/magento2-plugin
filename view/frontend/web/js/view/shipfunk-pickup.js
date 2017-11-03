define([
    'uiComponent',
    'Nord_Shipfunk/js/model/shipfunk-popup'

], function (Component, shipfunkPopup) {
    'use strict';

    return Component.extend({
        selectedPickup: shipfunkPopup.getSelectedPickup(),
        getCarrierData: shipfunkPopup.getCarrierData(),
        defaults: {
            template: 'Nord_Shipfunk/shipfunk-pickup'
        }
    });
});
