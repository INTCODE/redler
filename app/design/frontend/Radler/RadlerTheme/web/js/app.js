//BASKET OPEN
var basketDropdownTrigger = document.querySelector('header #basket .top');
var basketDropdown = document.querySelector('header #basket');
basketDropdownTrigger.addEventListener('click',function(){
    if(basketDropdown.classList.contains('active')){
        basketDropdown.classList.remove('active')
    }else{
        basketDropdown.classList.add('active')
    }
})

// DIFFERENT CARD
var differentCardTrigger = document.querySelector('#different_card_trigger input');
var differentCardBody = document.querySelector('#different_card_body');
var allCards = document.querySelectorAll('.card-choose-radio');

function toggledifferentCardBody(){
    if(differentCardTrigger.checked === true){
        differentCardBody.classList.add('active');
    }else{
        differentCardBody.classList.remove('active');
    }
}

if(differentCardBody !== null){
    toggledifferentCardBody();
    for(var i = 0; i<allCards.length; i++){
        allCards[i].addEventListener('change',function(){
            toggledifferentCardBody();
        })
    }
}

// FILTERS DROPDOWN
var allFiltersDropdown = document.querySelectorAll('.filters-item .top');
for(var i = 0; i<allFiltersDropdown.length; i++){
    allFiltersDropdown[i].addEventListener('click', function(){
        if(this.classList.contains('active')){
            this.classList.remove('active');
        }else{
            this.classList.add('active');
        }
    })
}

$('.carousel').slick({
    dots:true,
    arrows:false,
    autoplay:true,
    autoplaySpeed:5000,

});

$('.center-mode-slider').slick({
    arrows:false,
    autoplay:false,
    swipeToSlide:true,
    autoplaySpeed:5000,
    centerMode:true,
    focusOnSelect: true,
    slidesToShow: 5,
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

$('.gallery .big').slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: false,
    fade: true,
    asNavFor: '.gallery .gallery-list'
  });
  $('.gallery .gallery-list').slick({
    slidesToShow: 3,
    slidesToScroll: 1,
    asNavFor: '.gallery .big',
    focusOnSelect: true,
    customPaging: 0,
  });

function customInputNumberInit(){
    var inputsNumber = document.querySelectorAll('.custom-input-number');
    inputsNumber.forEach(function(item){
        var target = item.querySelector('input[type="number"]');
        target.value = 1;
        var increment = item.querySelector('.increment');
        var decrement = item.querySelector('.decrement');
        var max = target.max;
        if(max === ''){
            max = 9999;
        }
        var min = target.min;
        if(min === ''){
            min = 1;
        }
        increment.addEventListener('click', function(){
            if(parseInt(target.value) < max){
                target.value = parseInt(target.value) + 1;
            }
        });
        decrement.addEventListener('click', function(){
            if(parseInt(target.value) > min){
                target.value = parseInt(target.value) - 1;
            }
        });
    })
}
customInputNumberInit();