define(
    [
        'ko',
        'Magento_Checkout/js/model/checkout-data-resolver'
    ],
    function (ko, checkoutDataResolver) {
        "use strict";
        var shippingRates = ko.observableArray([]);
        var shippingCategories = ko.observableArray([]);

        return {
            isLoading: ko.observable(false),
            /**
             * Set shipping rates
             *
             * @param ratesData
             */
            setShippingRates: function (ratesData) {

                shippingRates(ratesData);
                shippingRates.valueHasMutated();
                checkoutDataResolver.resolveShippingRates(ratesData);

                var cats = [];
                var other = false;

                ratesData.forEach(function (rate, index) {
                    var curCat = rate.extension_attributes.category;
                    if (cats.indexOf(curCat) === -1 && curCat.indexOf('DEFAULT') === -1) {
                        cats.push(curCat);
                    }

                    if (curCat.indexOf('DEFAULT') > -1 && other === false) {
                        other = curCat.replace('DEFAULT', '');
                    }
                });

                if (other !== false) {
                    cats.push(other);
                }

                shippingCategories(cats);
                shippingCategories.valueHasMutated();

            },

            /**
             * Get shipping rates
             *
             * @returns {*}
             */
            getShippingRates: function () {
                return shippingRates;
            },
            getShippingCategories: function () {
                return shippingCategories;
            }
        };
    }
);
