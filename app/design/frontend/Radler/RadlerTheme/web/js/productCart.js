require(["jquery"], function ($) {

    // click +/-
    $('.increaseQty, .decreaseQty').on("click", function () {
        if (!$("[data-id=" + $(this).attr("data-target") + "]").attr("disabled") &&
            parseInt($("[data-id=" + $(this).attr("data-target") + "]").attr("max")) > parseInt($("[data-id=" + $(this).attr("data-target") + "]").val())) {
            
            var selectedOption = 0;
            if($("[data-id=" + $(this).attr("data-target") + "]").parents(".product-item-details").find(".swatch-option.selected").length > 0){
                selectedOption = $(this).parent().find("[data-id=" + $(this).attr("data-target") + "]").parents(".product-item-details").find(".swatch-option.selected").attr("option-id");
            }

            var me = this;
            var val = parseInt($(this).parent().find("[data-id=" + $(this).attr("data-target") + "]").val());

            switch ($(me).attr("data-action")) {
                case "-":
                    if($(me).parent().find("[data-id=" + $(me).attr("data-target") + "]").val() > 0) val--;
                break;
                case "+":
                    val++;
                break;
            }
            
            $("[data-id=" + $(me).attr("data-target") + "]").each(function(){
                if($(this).parents(".product-item-details").find(".swatch-option.selected").length > 0){
                    if($(this).parents(".product-item-details").find(".swatch-option.selected").attr("option-id") == selectedOption){
                        $(this).val(val);
                    }
                }else{
                    $(this).val(val);
                }
            });

            // data changed
            $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "true");
        }
    });

    // change input
    $(".inputProductQty").on("change", function () {
        // data changed
        $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "true");
    });

    // focusout input
    $(".inputProductQty").on("focusout", function () {
        if (parseInt($(this).attr("max")) > parseInt($(this).val())) {
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

    $('.increaseQty, .decreaseQty').on("mouseleave", function () {
        if (parseInt($("[data-id=" + $(this).attr("data-target") + "]").attr("max")) > parseInt($("[data-id=" + $(this).attr("data-target") + "]").val())) {
            if ($("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed") == "true" && !$("[data-id=" + $(this).attr("data-target") + "]").attr("disabled")) {
                // add to cart
                $("[data-id=addToCart_" + $(this).attr("data-target") + "]").click();
                updateProductCart();

                console.info("Add to cart");
            }
            $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "false");
        }
    });


    $("#addresses").on("change", function () {
        updateQtyAllItems();
    });
    $(document).on("ready", function () {
        if ($("#addresses").length > 0) {
            var checkSwatch = setInterval(() => {
                if ($(".swatch-option.selected").length > 0) {
                    updateQtyAllItems();

                    $(".swatch-option").on("click", function () {
                        updateQtyItem($(this).parent().parent().parent().parent().children(".price-final_price").attr("data-product-id"), $(this).attr('option-id'));
                    });

                    clearInterval(checkSwatch);
                }
            }, 200);
            $("#minicart-content-wrapper").css("display", "block");
        }
    });

});

function addToCartProduct(productId, type, qty) {
    console.info("Add to cart : new");
    require(["jquery"], function ($) {
        if ($("#addresses").length > 0 && $("#minicart-content-wrapper").attr("data-change") == "true") {
            var j = {
                productId: productId,
                quoteId: parseInt($("#quoteId").text()),
                type: type,
                addressId: $("#addresses").val(),
                qty: qty
            };
            j = JSON.stringify(j);
            $.ajax({
                url: $("#homePath").text() + "/rest/V1/blmCart/add/",
                data: j,
                type: 'POST',
                dataType: 'json',
                cache: false,
                contentType: 'application/json',
                processData: false,
                /** @inheritdoc */
                success: function (res) {
                    updateProductCart();
                    //console.log(res);
                },

                /** @inheritdoc */
                error: function (res) {
                    console.error("error add - productCart.js");
                    console.log(res);
                }
            });
        }
    });
}

function updateQtySomeProduct(productId) {
    console.log("update qty some item");
    require(["jquery"], function ($) {
        if ($("#addresses").length > 0 && $("#minicart-content-wrapper").attr("data-change") == "true") {
            var pid = productId;
            var type = 0;
            if ($("[data-product-id=" + pid + "]").parent().find(".swatch-option[aria-checked='true']").length > 0) {
                type = $("[data-product-id=" + pid + "]").parent().find(".swatch-option[aria-checked='true']").attr("option-id");
            }
            updateQtyItem(pid, type);
        }
    });
}


function updateQtyAllItems() {
    console.log("update qty all items");

    require(["jquery"], function ($) {
        if ($("#addresses").length > 0) {
            var updateProducts = {
                address: $("#addresses").val(),
                quoteid: parseInt($("#quoteId").text()),
                quote: []
            }

            $("#addresses").attr("disabled", "true");

            $("[data-product-id]").each(function () {
                var pid = $(this).attr("data-product-id");
                var addr = $("#addresses").val();
                var type = 0;

                $("[data-id='product-qty-" + pid + "']").attr("disabled", "true");

                if ($(this).parent().find(".swatch-option[aria-checked='true']").length > 0) {
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
                url: $("#homePath").text() + "/rest/V1/blmCart/getCartQty/",
                data: j,
                type: 'POST',
                dataType: 'json',
                cache: false,
                contentType: 'application/json',
                processData: false,
                /** @inheritdoc */
                success: function (res) {
                    var json = JSON.parse(res);
                    console.log(json);

                    $(".inputProductQty").val(0);
                    $.each(json, function () {
                        var me = this;
                        $.each($("[data-id='product-qty-" + me.productId + "']"), function(){
                            if($(this).parents(".product-item-details").find(".swatch-option.selected").attr("option-id") == me.type){
                                $(this).val(me.qty);
                                $(this).attr("max", me.stock);
                            }else if($(this).parents(".product-item-details").find(".swatch-option").length == 0){
                                $(this).val(me.qty);
                                $(this).attr("max", me.stock);
                            }
                            $(this).removeAttr("disabled");
                        });
                    });
                    $(".inputProductQty").removeAttr("disabled");
                    $("#addresses").removeAttr("disabled");
                    console.log("updated all products");
                },

                /** @inheritdoc */
                error: function (res) {
                    console.error("error update - productCart.js");
                    $("#addresses").removeAttr("disabled");
                }
            });

        }
    });
}

function updateQtyItem(productId, type) {
    console.log("update qty item " + productId);

    require(["jquery"], function ($) {
        if (productId && type && $("#addresses").length > 0) {
            $("[data-id='product-qty-" + productId + "']").attr("disabled", "true");
            var j = {
                productId: productId,
                addressId: $("#addresses").val(),
                type: type,
                quoteId: parseInt($("#quoteId").text())
            };
            j = JSON.stringify(j);
            console.log(j);
            $.ajax({
                url: $("#homePath").text() + "/rest/V1/blmCart/get/",
                data: j,
                type: 'POST',
                dataType: 'json',
                cache: false,
                contentType: 'application/json',
                processData: false,
                /** @inheritdoc */
                success: function (res) {
                    var json = JSON.parse(res);
                    console.info(json);
                    $.each($("[data-id='product-qty-" + json.productId + "']"), function(){
                        if($(this).parents(".product-item-details").find(".swatch-option.selected").attr("option-id") == json.type){
                            $(this).val(json.qty);
                            $(this).attr("max", json.stock);
                        }
                        $(this).removeAttr("disabled");
                    });

                },

                /** @inheritdoc */
                error: function (res) {
                    console.error("error update - productCart.js");
                }
            });
        }
    });
}


function updateProductCart() {
    require(["jquery"], function ($) {
        if ($("#addresses").length > 0) {
            var j = {
                quoteId: parseInt($("#quoteId").text()),
                addressId: $("#addresses").val(),
            };
            $("#minicart-content-wrapper").css("display", "none");
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
                    var output = "";
                    $.each(itemsOutput.data, (index, item) => {
                        output += getItemTemplate(item);
                    })
                    var itemPrice = itemsOutput.TotalData.addressCost != null ? itemsOutput.TotalData.addressCost : 0;
                    var itemCount = itemsOutput.TotalData.addressQty != null ? itemsOutput.TotalData.addressQty : 0;
                    $("#mini-cart").append(output);
                    $("#itemCount").html(itemCount);
                    $("#sidebarItemCount").html(`${itemCount} items`);
                    $("#itemPrice").html(`£${itemPrice}`);
                    $("#sidebaritemCost").html(`£${itemPrice}`);


                    $("#minicart-content-wrapper").attr("data-change", "false");
                    addRemoveListener();
                    addListenerPlusMinusProduct();
                },

                /** @inheritdoc */
                error: function (res) {
                    $("#minicart-content-wrapper").css("display", "block");
                    console.error("error add - productCart.js");
                    console.log(res);
                }
            });
        }
    });

}



function getItemTemplate(item) {
    item.price = parseFloat(item.price).toFixed(2);

    var typeString = "";
    switch (item.type) {
        case "21":
            typeString = "BOX"
            break;
        case "22":
            typeString = "Palette"
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
                    <input type="number" max="${item.stock}" value="${item.qty}" size="4" class="item-qty cart-item-qty" product-id="${item.productId}" id="cart-item-${item.crontab_id}-qty" product-type="${item.type}" data-cart-crontab-id="${item.crontab_id}" data-cart-item="${item.productId}" data-item-qty="${item.qty}" data-cart-item-id="${item.name}">
                    <button class="update-cart-item" style="display: none" id="update-cart-item-${item.productId}" data-cart-item="${item.productId}" title="Update">
                        <span>Update</span>
                    </button>
                    <div class="buttonMinicartQty" onclick="jQuery(this).parent().find('.cart-item-qty').val(parseInt(jQuery(this).parent().find('.cart-item-qty').val())+1)" id="update-cart-item-${item.productId}" data-cart-item="${item.productId}" data-cart-item-crontab="${item.crontab_id}">+</div>
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

function addRemoveListener() {
    jQuery("#mini-cart a.action.delete").click((e) => {
        var obj = e.target;
        var $input = jQuery(jQuery(obj).parents(".product-item-details")).find("input")
        var type = $input.attr("product-type");
        var id = $input.attr("product-id");
        jQuery("#minicart-content-wrapper").attr("data-change", "true");
        addToCartProduct(id, type, 0);
    })
}

function addListenerPlusMinusProduct() {
    jQuery("#minicart-content-wrapper").css("display", "block");
    jQuery("#mini-cart .buttonMinicartQty").on("click", function (e) {
        jQuery("#minicart-content-wrapper").attr("data-change", "true");
    })
}

//multishipping
function updateMultiShippingCart() {
    require(["jquery"], function ($) {
        var j = {
            quoteId: parseInt($("#quoteId").text()),
        };
        j = JSON.stringify(j);
        $.ajax({
            url: $("#homePath").text() + "/rest/V1/blmCart/getCart/",
            data: j,
            type: 'POST',
            dataType: 'json',
            cache: false,
            contentType: 'application/json',
            processData: false,
            /** @inheritdoc */
            success: function (res) {
                $("#container-items").html('');
                $("#multiShippingSummary").html('');
                console.log(JSON.parse(res));
                var itemsOutput = JSON.parse(res);
                var output = "";

                $.each(itemsOutput.data, (index, item) => {
                    var selectAddresses = multiShippingCreateSelect(itemsOutput.addresses, item.address);
                    output += getMultiShippingTemplate(item, selectAddresses);
                });

                $("#container-items").append(output);
                $("#multiShippingSummary").append(getMultiShippingSummary(itemsOutput.TotalData));
                addListenerUpdateMultiShippingCart();
            },

            /** @inheritdoc */
            error: function (res) {

                console.error("error add - multishipping.js");
                console.log(res);
            }
        });
    });
}


function getMultiShippingTemplate(item, selectAddresses) {
    item.price = parseFloat(item.price).toFixed(2);
    return `<div class="basket-item" id="product_multishipping_${item.productId}" product_id="${item.productId}">
<div class="img" >
    <img style="max-width: 25%; max-height: 25%;" src="${item.image}" alt="">
</div>
<div class="text">
    <p class="name">${item.name}</p>
</div>
<div class="price">
    £${item.price}
    <div class="switch">
        <input type="checkbox" ${item.type == 21 ? ' checked="checked"' : ' ""'}>
        <span class="switch-body"></span>
        <span class="icons">
            <span class="left">
                <img src="./assets/img/icons/view_1.png" alt="">
            </span>
            <span class="right">
                <img src="./assets/img/icons/view_2.png" alt="">
            </span>
        </span>
    </div>
</div>
<div class="quantity">
    <div class="custom-input-number">
        <input type="number" max="${item.stock}" value="${item.qty}">
        <span class="increment">+</span>
        <span class="decrement">-</span>
    </div>
</div>
<div>
    <div class="field address">
        <div class="control">
        ${selectAddresses}
        </div>
    </div>
</div>
<div class="subtotal">£${item.price * parseFloat(item.qty)}</div>
<span class="btn-remove"></span>
</div>`;
}
function getMultiShippingSummary(total) {
    var output = `<p class="heading">Summary</p>
<div class="borders">
<p>
    <span>Subtotal</span>
    <span>£${total.totalCost==null?0:total.totalCost}</span>
</p>
</div>
    <p class="total">
        <span>Order Total</span>
        <span>£${total.totalCost ==null?0: total.totalCost}</span>
    </p>
<button class="btn btn-green" type="submit">
    Proceed to checkout
</button>`;
    return output;

}

function multiShippingCreateSelect(addresses, toSelect) {
    var output = '<select title="">';
    jQuery.each(addresses, (index, item) => {
        output += `<option ${toSelect == item.entity_id ? selected = "selected" : ""} value="${item.entity_id}">${item.city}, ${item.company}, ${item.firstname} ${item.lastname} ,${item.postcode}, ${item.country_id}</option>`;
    });

    output += "</select>";

    return output;
}


function multiShippingAddItem(obj, mode) {//mode=1- add, mode=2- remove
    var $input = jQuery(obj).parent().find("input");
    var value = $input.val();
    var outputValue = parseInt(value);
    (mode == 1) ? outputValue++ : outputValue--;
    $input.val(outputValue < 0 ? 0 : outputValue);

};


function addListenerUpdateMultiShippingCart() {
    jQuery("#checkout_multishipping_form .basket-list .switch input").change((e) => {
        console.log("Zmieniam typ");
        getValuesMultiShipping(e.target, 1);

    });
    jQuery("#checkout_multishipping_form .basket-list .btn-remove").click((e) => {
        console.log("usuwam");
        var productId = getProductIdMultiShipping(e.target);
        var type = getTypedMultiShipping(e.target);
        var addressId = getAddressIdMultiShipping(e.target);
        ajaxUpdateShippingCart(2, productId, type, addressId, 0);
    })
    jQuery("#checkout_multishipping_form .field.address select").change((e) => {
        console.log("Zmieniam adres");
        getValuesMultiShipping(e.target, 3);
    });
    jQuery("#checkout_multishipping_form .quantity .increment").click((e) => {
        console.log("Zwiększam");
        multiShippingAddItem(e.target, 1);
        var avaValue= parseInt(jQuery(e.target).parent().find("input").attr("max"));
        var currValue = parseInt(jQuery(e.target).parent().find("input").val());
        if(currValue<=avaValue)
            getValuesMultiShipping(e.target, 2);
    });
    jQuery("#checkout_multishipping_form .quantity .decrement").click((e) => {
        console.log("Zmniejszam");
        
        multiShippingAddItem(e.target, 2);
        var avaValue= parseInt(jQuery(e.target).parent().find("input").attr("max"));
        var currValue = parseInt(jQuery(e.target).parent().find("input").val());
        console.log(avaValue);
        console.log(currValue);
        if(currValue<=avaValue)
            getValuesMultiShipping(e.target, 2);
    });

    jQuery("#checkout_multishipping_form .custom-input-number input").change((e) => {
        console.log("Zmieniam ilość");
        console.log(e.target);
        console.log(jQuery(e.target).attr("max"))

        var avaValue= parseInt(jQuery(e.target).attr("max"));
        var currValue = parseInt(jQuery(e.target).val());
        if(currValue<=avaValue)
            getValuesMultiShipping(e.target, 2);
    });
    

}

function getValuesMultiShipping(target, mode) {//1- change type, 2- change qty, 3- change adres
    var productId = getProductIdMultiShipping(target);
    var type = getTypedMultiShipping(target);
    var qty = getQtyMultiShipping(target);
    var addressId = getAddressIdMultiShipping(target);
    ajaxUpdateShippingCart(mode, productId, type, addressId, qty);
}

function getProductIdMultiShipping(obj) {
    return jQuery(obj).parents(".basket-item").attr("product_id");
}

function getTypedMultiShipping(obj) {
    return jQuery(obj).parents(".basket-item").find(".price input").prop("checked");
}

function getAddressIdMultiShipping(obj) {
    return jQuery(obj).parents(".basket-item").find(".field.address select").val();
}

function getQtyMultiShipping(obj) {
    return jQuery(obj).parents(".basket-item").find(".quantity input").val();
}


function ajaxUpdateShippingCart(mode, productId, type, addressId, qty) {//1- change type, 2- change qty, 3- change adres
    var arrayMode = [
        {
            mode: 1,
            value: "type"
        },
        {
            mode: 2,
            value: "qty"
        },
        {
            mode: 3,
            value: "address"
        },
    ];


    var j = {
        quoteId: parseInt(jQuery("#quoteId").text()),
        productId: productId,
        type: type ? "21" : "22",
        addressId: addressId,
        qty: qty,
        flag: arrayMode.filter(f => f.mode == mode)[0].value
    };

    j = JSON.stringify(j);
    console.log(j);
    jQuery.ajax({
        url: jQuery("#homePath").text() + "/rest/V1/blmCart/editCart/",
        data: j,
        type: 'POST',
        dataType: 'json',
        cache: false,
        contentType: 'application/json',
        processData: false,
        /** @inheritdoc */
        success: function (res) {
            updateMultiShippingCart();
        },

        /** @inheritdoc */
        error: function (res) {

            console.info("error update - updateMultiShipping.js");
        }
    });

}
