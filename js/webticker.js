$(document).ready(function () {

    $('.slickitem').slick({


        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: false,
        easing:'linear',
        autoplaySpeed:500,
        nextArrow: '<i class="fa fa-angle-right slick-prev   hidden-xs"></i>',
        prevArrow: '<i class="fa fa-angle-left   slick-next hidden-xs"></i>',
    });

    $('.slick-item').slick({

        speed: 300,
        slidesToShow: 5,
        slidesToScroll: 2,
        autoplay: false,
        nextArrow: '<i class="fa fa-angle-right slick-prev   hidden-xs"></i>',
        prevArrow: '<i class="fa fa-angle-left   slick-next hidden-xs"></i>',

        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    //autoplay: true,
                    autoplaySpeed:3000,
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    //autoplay: true,
                    autoplaySpeed:3000,
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    //autoplay: true,
                    autoplaySpeed:3000,
                }
            }
        ]

    });

    $('.slick-item-brand').slick({

        speed: 300,
        slidesToShow:6,
        slidesToScroll: 6,
        slidesToScroll: 2,
        autoplay: true,
        autoplaySpeed:3000,
        nextArrow: '<i class="fa fa-angle-right slick-prev   hidden-xs">--</i>',
        prevArrow: '<i class="fa fa-angle-left   slick-next hidden-xs">--</i>',

        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ]

    });
    // var $mq = jQuery('.marquee_en').marquee();

    var $mq = jQuery('.marquee_en').marquee({
        //speed in milliseconds of the marquee
        duration: 20000,
        //gap in pixels between the tickers
        gap: 50,
        //time in milliseconds before the marquee will start animating
        delayBeforeStart: 0,
        //'left' or 'right'
        direction: 'left',
        //true or false - should the marquee be duplicated to show an effect of continues flow
        duplicated: true
    });


    jQuery('.marquee_en').mouseover(function(){

        $mq.marquee('pause');
    });

    jQuery('.marquee_en').mouseout(function(){

        $mq.marquee('resume');
    });


    var $mq_ar = jQuery('.marquee_ar').marquee({
        //speed in milliseconds of the marquee
        duration: 30000,
        //gap in pixels between the tickers
        gap: 50,
        //time in milliseconds before the marquee will start animating
        delayBeforeStart: 0,
        //'left' or 'right'
        direction: 'right',
        //true or false - should the marquee be duplicated to show an effect of continues flow
        duplicated: true
    });

    jQuery('.marquee_ar').mouseover(function(){
        $mq_ar.marquee('pause');
    });

    jQuery('.marquee_ar').mouseout(function(){
        $mq_ar.marquee('resume');
    });



});