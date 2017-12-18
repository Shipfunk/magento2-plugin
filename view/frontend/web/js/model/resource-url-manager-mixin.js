/*jshint browser:true jquery:true*/
/*global alert*/
define(['jquery'],
 function ($) {
    'use strict';

     var mixin = {

          /**
           * @param {Object} quote
           * @return {*}
           */
          getUrlForGetPickupPoints: function (quote) {
              var params = this.getCheckoutMethod() == 'guest' ? //eslint-disable-line eqeqeq
                      {
                          cartId: quote.getQuoteId()
                      } : {},
                  urls = {
                      'guest': '/guest-carts/:cartId/get-pickup-points',
                      'customer': '/carts/mine/get-pickup-points'
                  };

              return this.getUrl(urls, params);
          },
       
          /**
           * @param {Object} quote
           * @return {*}
           */
          getUrlForSelectedDelivery: function (quote) {
              var params = this.getCheckoutMethod() == 'guest' ? //eslint-disable-line eqeqeq
                      {
                          cartId: quote.getQuoteId()
                      } : {},
                  urls = {
                      'guest': '/guest-carts/:cartId/selected-delivery',
                      'customer': '/carts/mine/selected-delivery'
                  };

              return this.getUrl(urls, params);
          },
     };

     return function (target) {
         return $.extend(target,mixin);
     }; 
});
