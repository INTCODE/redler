require(["jquery"], function($) { 

    $( document ).ready(function() {
        var idAddress= $( "#addresses" ).val();
        $('input[name="addressId"]').val(idAddress);
    });

    // click +/-
    $("#addresses").on("change", function() {
        var idAddress= $( "#addresses" ).val();
        $('input[name="addressId"]').val(idAddress);

        updateProductCart();
    });

});