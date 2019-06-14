require(["jquery"], function($) {

    // click +/-
    $('.increaseQty, .decreaseQty').on("click", function() {
        switch ($(this).attr("data-action")) {
            case "-":
                if ($("[data-id=" + $(this).attr("data-target") + "]").val() > 0) $("[data-id=" + $(this).attr("data-target") + "]").val(parseInt($("[data-id=" + $(this).attr("data-target") + "]").val()) - 1);
                break;
            case "+":
                $("[data-id=" + $(this).attr("data-target") + "]").val(parseInt($("[data-id=" + $(this).attr("data-target")).val() + "]") + 1);
                break;
        }
        // data changed
        $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "true");
    });

    // change input
    $(".inputProductQty").on("change", function() {
        // data changed
        $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "true");
    });

    // blur input
    $(".inputProductQty").on("blur", function() {
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
        if ($("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed") == "true") {
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
        var iterChecker = 0;
        updateQtyAllItems();
    });

});

function addToCartProduct(productId, type, qty){
    require(["jquery"], function($) {
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
            async: true,
            /** @inheritdoc */
            success: function(res) {
                //console.log(res);
            },
            
            /** @inheritdoc */
            error: function(res) {
                console.info("error add - productCart.js");
                //console.log(res);
            }
        });
    });
}

function updateQtySomeProduct(productId){
    require(["jquery"], function($) {
        var pid = productId;
        var addr = $("#addresses").val();
        var type = -1;
        if($("[data-product-id="+pid+"]").parent().find(".swatch-option[aria-checked='true']").length > 0) {
            type = $("[data-product-id="+pid+"]").parent().find(".swatch-option[aria-checked='true']").attr("option-id");
        }
        updateQtyItem(pid, addr, type);
    });
}

function updateQtyConfAllItems(){
    require(["jquery"], function($) {
        $("#addresses").attr("disabled", "true");
        setTimeout(() => {
            $("#addresses").removeAttr("disabled");
        }, 5000);
        $(".swatch-attribute-options").each(function(){
            var me = $(this).parents(".product-item-details").find("[data-product-id]");
            var pid = $(me).attr("data-product-id");
            var addr = $("#addresses").val();
            var type = $(this).find(".swatch-option.selected").attr("option-id");
            updateQtyItem(pid, addr, type);
        });
    });
    
}

function updateQtyAllItems(){
    require(["jquery"], function($) {
        $("#addresses").attr("disabled", "true");
        setTimeout(() => {
            $("#addresses").removeAttr("disabled");
        }, 5000);
        $("[data-product-id]").each(function(){
            var pid = $(this).attr("data-product-id");
            var addr = $("#addresses").val();
            var type = -1;
            if($(this).parent().find(".swatch-option[aria-checked='true']").length > 0) {
                type = $(this).parent().find(".swatch-option[aria-checked='true']").attr("option-id");
            }
            updateQtyItem(pid, addr, type);
        });
    });
}

/*
{
"data":[
    parseInt(productId),
    parseInt(addressId),
    parseInt(type),
    parseInt($("#quoteId").text())
]
}
*/

async function updateQtyItem(productId, addressId, type){
    if(productId && addressId && type)
    require(["jquery"], function($) {
        
        //console.log("updateQtyItem: "+productId+" "+addressId+" "+type);
        var j = {
            productId: productId, 
            addressId: addressId, 
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
            async: true,
            /** @inheritdoc */
            success: function(res) {
                var json = JSON.parse(res);
                console.info(res);
                $("[data-target='product-qty-"+productId+"']").val(json.qty);
            },
            
            /** @inheritdoc */
            error: function(res) {
                console.info("error update - productCart.js");
                console.log(res);
            }
        });
    });
}
