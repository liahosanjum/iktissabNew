$(document).ready(function () {

    $('.slickitem').slick({


        slidesToShow: 1,
        slidesToScroll: 1,
        //autoplay: true,
        //easing:'linear',
        autoplaySpeed:1500,
        nextArrow: '<i class="fa fa-angle-right slick-prev   hidden-xs">--</i>',
        prevArrow: '<i class="fa fa-angle-left   slick-next hidden-xs">--</i>',
    });

    $('.slick-item').slick({

        speed: 300,
        slidesToShow: 5,
        slidesToScroll: 2,
        autoplay: false,
        nextArrow: '<i class="fa fa-angle-right slick-prev   hidden-xs">--</i>',
        prevArrow: '<i class="fa fa-angle-left   slick-next hidden-xs">--</i>',

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

    $('#webTicker-ar').webTicker({
        direction: 'right'
    });
    $('#webTicker-en').webTicker({
        direction: 'left'
    });
});