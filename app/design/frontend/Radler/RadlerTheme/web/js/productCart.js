require(["jquery"], function($) {

    // click +/-
    $('.increaseQty, .decreaseQty').on("click", function() {
        if(!$("[data-id=" + $(this).attr("data-target") + "]").attr("disabled")){
            switch ($(this).attr("data-action")) {
                case "-":
                    if ($("[data-id=" + $(this).attr("data-target") + "]").val() > 0) 
                    $("[data-id=" + $(this).attr("data-target") + "]").val(parseInt($("[data-id=" + $(this).attr("data-target") + "]").val()) - 1);
                    break;
                case "+":
                    $("[data-id=" + $(this).attr("data-target") + "]").val(parseInt($("[data-id=" + $(this).attr("data-target")).val() + "]") + 1);
                    break;
            }
            // data changed
            $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "true");
        }
    });

    // change input
    $(".inputProductQty").on("change", function() {
        // data changed
        $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "true");
    });

    // focusout input
    $(".inputProductQty").on("focusout", function() {
        if (parseInt($(this).val()) < 0) {
            $(this).val(0);
            $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "true");
        }
        if ($("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed") == "true") {
            // add to cart
            $("[data-id=addToCart_" + $(this).attr("data-target") + "]").click();
            console.info("Add to cart");
        }
        $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "false");
    });

    $('.increaseQty, .decreaseQty').on("mouseleave", function() {
        if ($("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed") == "true" &&  !$("[data-id=" + $(this).attr("data-target") + "]").attr("disabled")) {
            // add to cart
            $("[data-id=addToCart_" + $(this).attr("data-target") + "]").click();
            console.info("Add to cart");
        }
        $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "false");
    });


    $("#addresses").on("change", function(){
        updateQtyAllItems();
    });

    $(document).on("ready", function(){
        if($("#addresses").length>0){
            var checkSwatch = setInterval(() => {
                if($(".swatch-option.selected").length>0){
                    updateQtyAllItems();

                    $(".swatch-option").on("click", function(){
                        updateQtyItem($(this).parent().parent().parent().parent().children(".price-final_price").attr("data-product-id"),$(this).attr('option-id'));
                    });

                    clearInterval(checkSwatch);
                }
            }, 200);
        }
    });



});

function addToCartProduct(productId, type, qty){
    console.info("Add to cart : new");
    require(["jquery"], function($) {
        if($("#addresses").length>0){
            var j = {
                productId:productId,
                quoteId:parseInt($("#quoteId").text()),
                type:type,
                addressId:$("#addresses").val(),
                qty:qty
            };
            j = JSON.stringify(j);
            $.ajax({
                url: $("#homePath").text()+"/rest/V1/blmCart/add/",
                data: j,
                type: 'POST',
                dataType: 'json',
                cache: false,
                contentType: 'application/json',
                processData: false,
                /** @inheritdoc */
                success: function(res) {
                    //console.log(res);
                },
                
                /** @inheritdoc */
                error: function(res) {
                    console.error("error add - productCart.js");
                    //console.log(res);
                }
            });
        }
    });
}

function updateQtySomeProduct(productId){
    console.log("update qty some item");
    require(["jquery"], function($) {
        if($("#addresses").length>0){
            var pid = productId;
            var addr = $("#addresses").val();
            var type = 0;
            if($("[data-product-id="+pid+"]").parent().find(".swatch-option[aria-checked='true']").length > 0) {
                type = $("[data-product-id="+pid+"]").parent().find(".swatch-option[aria-checked='true']").attr("option-id");
            }
            updateQtyItem(pid, addr, type);
        }
    });
}


function updateQtyAllItems(){
    console.log("update qty all items");

    require(["jquery"], function($) {    
        if($("#addresses").length>0){
            var updateProducts = {
                    address: $("#addresses").val(),
                    quoteid: parseInt($("#quoteId").text()),
                    quote: []
            }
            
            $("#addresses").attr("disabled", "true");

            $("[data-product-id]").each(function(){
                var pid = $(this).attr("data-product-id");
                var addr = $("#addresses").val();
                var type = 0;

                $("[data-id='product-qty-"+pid+"']").attr("disabled", "true");

                if($(this).parent().find(".swatch-option[aria-checked='true']").length > 0) {
                    type = $(this).parent().find(".swatch-option[aria-checked='true']").attr("option-id");
                }
                updateProducts.quote[updateProducts.quote.length] = {
                    productid: pid,
                    type: type
                };

            });

            var j = JSON.stringify({
                CartData: JSON.stringify(updateProducts)
            });
            $.ajax({
                url: $("#homePath").text()+"/rest/V1/blmCart/getCartQty/",
                data: j,
                type: 'POST',
                dataType: 'json',
                cache: false,
                contentType: 'application/json',
                processData: false,
                /** @inheritdoc */
                success: function(res) {
                    var json = JSON.parse(res);
                    $(".inputProductQty").val(0);
                    $.each(json, function(){
                        $("[data-id='product-qty-"+this.productId+"']").val(this.qty);
                        $("[data-id='product-qty-"+this.productId+"']").removeAttr("disabled");
                    });
                    $(".inputProductQty").removeAttr("disabled");
                    $("#addresses").removeAttr("disabled");
                    console.log("updated all products");
                },
                
                /** @inheritdoc */
                error: function(res) {
                    console.error("error update - productCart.js");
                    //console.log(res);
                    $("#addresses").removeAttr("disabled");
                }
            });
        
        }
    });
}

function updateQtyItem(productId, type){
    console.log("update qty item "+productId);
    
    require(["jquery"], function($) {
        if(productId && type && $("#addresses").length>0){
            $("[data-id='product-qty-"+productId+"']").attr("disabled", "true");
            var j = {
                productId: productId, 
                addressId: $("#addresses").val(), 
                type: type,
                quoteId: parseInt($("#quoteId").text())
            };
            j = JSON.stringify(j);
            $.ajax({
                url: $("#homePath").text()+"/rest/V1/blmCart/get/",
                data: j,
                type: 'POST',
                dataType: 'json',
                cache: false,
                contentType: 'application/json',
                processData: false,
                /** @inheritdoc */
                success: function(res) {
                    var json = JSON.parse(res);
                    //console.info(res);
                    $("[data-id='product-qty-"+productId+"']").val(json.qty);
                    $("[data-id='product-qty-"+productId+"']").removeAttr("disabled");
                },
                
                /** @inheritdoc */
                error: function(res) {
                    console.error("error update - productCart.js");
                    //console.log(res);
                }
            });
        }
    });
}
