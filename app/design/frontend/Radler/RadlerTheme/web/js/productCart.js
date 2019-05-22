require(["jquery"], function($) {
    $('.increaseQty, .decreaseQty').on("click", function() {
        switch ($(this).attr("data-action")) {
            case "-":
                if ($("[data-id=" + $(this).attr("data-target") + "]").val() > 0) $("[data-id=" + $(this).attr("data-target") + "]").val(parseInt($("[data-id=" + $(this).attr("data-target") + "]").val()) - 1);
                break;
            case "+":
                $("[data-id=" + $(this).attr("data-target") + "]").val(parseInt($("[data-id=" + $(this).attr("data-target")).val() + "]") + 1);
                break;
        }
        $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "true");
    });

    $(".inputProductQty").on("change", function() {
        $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "true");
    });
    $(".inputProductQty").on("blur", function() {
        if (parseInt($(this).val()) < 0) {
            $(this).val(0);
            $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "true");
        }
        if ($("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed") == "true") {
            $("[data-id=addToCart_" + $(this).attr("data-target") + "]").click();
        }
        $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "false");
    });
    $('.increaseQty, .decreaseQty').on("mouseleave", function() {
        if ($("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed") == "true") {
            $("[data-id=addToCart_" + $(this).attr("data-target") + "]").click();
        }
        $("[data-id=" + $(this).attr("data-target") + "]").attr("data-changed", "false");
    });



});