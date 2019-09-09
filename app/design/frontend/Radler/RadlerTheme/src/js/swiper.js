require([
    'jquery',
    'slick'
], function ($) {
    $(document).ready(function () {
        $('#maincontent .widget-product-grid').slick({
             
            autoplay:false, 
            autoplaySpeed:5000,
            centerMode: true,
            dots: true,
            initialSlide: 3,
            focusOnSelect: true,
            slidesToShow: 5, 
            arrows:true,
            centerPadding:'0px',
            responsive:[{
                breakpoint: 991,
                settings: {
                  slidesToShow: 3,
                  initialSlide: 2,
                }
            },
            {
                breakpoint:767,
                settings:{
                    slidesToShow: 1,
                    initialSlide: 1,
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
    'jquery'
], function ($) {
    $('.collapse-btn').click(function(){
        var target = $(this).closest('.question');
        target.toggleClass('collapsed')
    })
});
