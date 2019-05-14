define(['jquery'], function ($) {
    'use strict';

    return function (addToCart) {
        $('#minusQty').click(function () {
            $('#qty').val(parseInt($('#qty').val())-1);
        });

        $('#addQty').click(function () {
            $('#qty').val(parseInt($('#qty').val())+1);
        });

        return addToCart;
    }
});