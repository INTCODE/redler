require(["jquery"], function($) {

    // click +/-
    $('.increaseQty, .decreaseQty').on("click", function() {
        if(!$("[data-id=" + $(this).attr("data-target") + "]").attr("disabled") &&
        parseInt($("[data-id=" + $(this).attr("data-target") + "]").attr("max")) > parseInt($("[data-id=" + $(this).attr("data-target") + "]").val())) {
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
        if(parseInt($(this).attr("max")) > parseInt($(this).val())){
            if (parseInt($(this).val()) < 0) {
                $(this).val(0);
                $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "true");
            }
            if ($("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed") == "true") {
                // add to cart
                $("[data-id=addToCart_" + $(this).attr("data-target") + "]").click();
                updateProductCart();

                console.info("Add to cart");
            }
            $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "false");
        }
    });

    $('.increaseQty, .decreaseQty').on("mouseleave", function() {
        if(parseInt($("[data-id=" + $(this).attr("data-target") + "]").attr("max")) > parseInt($("[data-id=" + $(this).attr("data-target") + "]").val())){
            if ($("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed") == "true" &&  !$("[data-id=" + $(this).attr("data-target") + "]").attr("disabled")) {
                // add to cart
                $("[data-id=addToCart_" + $(this).attr("data-target") + "]").click();
                updateProductCart();

                console.info("Add to cart");
            }
            $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "false");
        }
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

    jQuery("#mini-cart .buttonMinicartQty").click((e)=>{
        var obj = e.target;
        console.log(obj);
        //var $input = jQuery(jQuery(obj).parents(".product-item-details")).find("input")
 
     
       
    })


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

            console.log(j);
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
                    updateProductCart();
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
                    console.log(json);

                    $(".inputProductQty").val(0);
                    $.each(json, function(){
                        $("[data-id='product-qty-"+this.productId+"']").val(this.qty);
                        $("[data-id='product-qty-"+this.productId+"']").attr("max",this.stock);
                        $("[data-id='product-qty-"+this.productId+"']").removeAttr("disabled");
                    });
                    $(".inputProductQty").removeAttr("disabled");
                    $("#addresses").removeAttr("disabled");
                    console.log("updated all products");
                },
                
                /** @inheritdoc */
                error: function(res) {
                    console.error("error update - productCart.js");
                    console.log(res);
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
                    $("[data-id='product-qty-"+productId+"']").attr("max",json.stock);
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


function updateProductCart(){
    require(["jquery"], function ($) {
        var j = {
            quoteId: parseInt($("#quoteId").text()),
            addressId: $("#addresses").val(),
        };
        j = JSON.stringify(j);
        $.ajax({
            url: $("#homePath").text() + "/rest/V1/blmCart/getCartByAddress/",
            data: j,
            type: 'POST',
            dataType: 'json',
            cache: false,
            contentType: 'application/json',
            processData: false,
            /** @inheritdoc */
            success: function (res) {
                $("#mini-cart").html('');
                var itemsOutput = JSON.parse(res);
                var output="";
                $.each(itemsOutput.data,(index,item)=>{
                    output+=getItemTemplate(item);
                })
                var itemPrice=itemsOutput.TotalData.addressCost!=null?itemsOutput.TotalData.addressCost:0;
                var itemCount = itemsOutput.TotalData.addressQty!=null?itemsOutput.TotalData.addressQty:0;
                $("#mini-cart").append(output);
                $("#itemCount").html(itemCount);
                $("#sidebarItemCount").html(`${itemCount} items`);
                $("#itemPrice").html(`£${itemPrice}`);
                $("#sidebaritemCost").html(`£${itemPrice}`);
                
                $("#minicart-content-wrapper").css("display","block");
                addRemoveListener();
                console.log(JSON.parse(res));
            },

            /** @inheritdoc */
            error: function (res) {
                $("#minicart-content-wrapper").css("display","block");
                console.info("error add - productCart.js");
                console.log(res);
            }
        });
    });
}



function getItemTemplate(item){
    item.price=parseFloat(item.price).toFixed(2);

    var typeString="";
    switch(item.type){
        case "21":
            typeString="BOX"
            break;
        case "22":
            typeString="Palette"
            break
    }
    return `
    <li class="item product product-item odd last" data-role="product-item" data-collapsible="true">
    <div class="product">
       
        <a tabindex="-1" class="product-item-photo" href="${item.url}" title="${item.name}">

<span class="product-image-container" style="width: 75px;">
    <span class="product-image-wrapper" style="padding-bottom: 100%;">
        <img class="product-image-photo" src="${item.image}" alt="${item.name}" style="max-width: 75px; max-height: 75px;">
    </span>
</span>

        <div class="product-item-details">
            <strong class="product-item-name">
                <a href="${item.url}">${item.name}</a>
            </strong>

            <div class="product options" role="tablist" data-collapsible="true">
                <span data-role="title" class="toggle" role="tab" aria-selected="false" aria-expanded="false" tabindex="0"><span>See Details</span></span>

                <div data-role="content" class="content" role="tabpanel" aria-hidden="true" style="display: none;">
                    <strong class="subtitle"><span>Options Details</span></strong>
                    <dl class="product options list">
                        <dt class="label">Package Type</dt>
                        <dd class="values">
                                <span>Box</span>
                        </dd>
                       
                    </dl>
                </div>
            </div>
            <div class="product-item-pricing">
            <div class="price-container">
                <span class="price-wrapper">   <span class="price-excluding-tax" data-label="Excl. Tax"> <span class="minicart-price"> <span class="price">${typeString}</span></span> </span>  </span>
            </div>
            <div class="price-container">
                <span class="price-wrapper">   <span class="price-excluding-tax" data-label="Excl. Tax"> <span class="minicart-price"> <span class="price">£${item.price}</span></span> </span>  </span>
            </div>

                <div class="details-qty qty">
                    <label class="label" for="cart-item-${item.productId}-qty">Qty</label>
                    <input value: qty" type="number" value="${item.qty}" size="4" class="item-qty cart-item-qty" product-id="${item.productId}" id="cart-item-${item.crontab_id}-qty" product-type="${item.type}" data-cart-crontab-id="${item.crontab_id}" data-cart-item="${item.productId}" data-item-qty="${item.qty}" data-cart-item-id="${item.name}">
                    <button class="update-cart-item" style="display: none" id="update-cart-item-${item.productId}" data-cart-item="${item.productId}" title="Update">
                        <span>Update</span>
                    </button>
                    <div class="buttonMinicartQty" onclick="if(jQuery(this).parent().find('.cart-item-qty').val() < ${item.stock})jQuery(this).parent().find('.cart-item-qty').val(parseInt(jQuery(this).parent().find('.cart-item-qty').val())+1)" id="update-cart-item-${item.productId}" data-cart-item="${item.productId}" data-cart-item-crontab="${item.crontab_id}">+</div>
                    <div class="buttonMinicartQty" onclick="if(jQuery(this).parent().find('.cart-item-qty').val() > 0) jQuery(this).parent().find('.cart-item-qty').val(parseInt(jQuery(this).parent().find('.cart-item-qty').val())-1)" id="update-cart-item-${item.productId}" data-cart-item="${item.productId}" data-cart-item-crontab="${item.crontab_id}">-</div>
                </div>
            </div>

            <div class="product actions">
                <div class="secondary">
                    <a class="action delete" data-cart-item="${item.productId}" title="Remove item">
                        <span>Remove</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</li>`;
};

function addRemoveListener(){
    jQuery("#mini-cart a.action.delete").click((e)=>{
        var obj = e.target;
        var $input = jQuery(jQuery(obj).parents(".product-item-details")).find("input")
        var type = $input.attr("product-type");
        var id=$input.attr("product-id");
        console.log(type);
        console.log(id);
        addToCartProduct(id, type, 0);
    
    })

}
