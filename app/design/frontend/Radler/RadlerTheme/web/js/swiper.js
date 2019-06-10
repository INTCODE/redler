require([
    'jquery',
    'slick'
], function ($) {
    $(document).ready(function () {
        $('#maincontent .widget-product-grid').slick({
            dots:true,
            autoplay:false,
            swipeToSlide:true,
            autoplaySpeed:5000,
            centerMode:true,
            focusOnSelect: true,
            slidesToShow: 5,
            infinite: true,
            arrows:false,
            centerPadding:'0px',
            responsive:[{
                breakpoint: 991,
                settings: {
                  slidesToShow: 3,
                }
            },
            {
                breakpoint:767,
                settings:{
                    slidesToShow: 1,
                }
            }
        ]
        });
        $('#aslider-').slick({
            slidesToShow:1,
        })
    });
});

require([
    'jquery',
    'slick'
], function ($) {
    $('.collapse-btn').click(function(){
        var target = $(this).closest('.question');
        target.toggleClass('collapsed')
    })
});
