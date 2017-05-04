$(document).ready(function () {
   
    $('.slickitem').slick({

        speed: 300,
        slidesToShow: 1,
        slidesToScroll: 1,
        nextArrow: '<i class="fa fa-angle-right slick-prev   hidden-xs">--</i>',
        prevArrow: '<i class="fa fa-angle-left   slick-next hidden-xs">--</i>',
    });

    $('.slick-item').slick({

        speed: 300,
        slidesToShow: 5,
        slidesToScroll: 5,
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

    $('.slick-item-brand').slick({

        speed: 300,
        slidesToShow:6,
        slidesToScroll: 6,
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
        direction: 'left'
    });
    $('#webTicker-en').webTicker({
        direction: 'right'
    });
});