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

});