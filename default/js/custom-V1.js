jQuery(document).ready(function($){



    $('.menu-toggle-btn').click(function () {
        $('.responsive_menu-items').stop(true, true).slideToggle();
    });




    $("#owl-main-slider").owlCarousel({

        autoPlay: 5000,
        stopOnHover: true,
        navigation: true,
        pagination: true,
        singleItem: true,
        addClassActive: true,
        paginationNumbers: false,
        pagination: false,
        responsive: true,
        dots: false,
        navigationText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"],

    });



    $("#owl-members-logo").owlCarousel({

        itemsCustom: [
          [0, 2],
          [450, 2],
          [600, 4],
          [700, 5],
          [1000, 5],
          [1200, 6],
          [1400, 6],
          [1600, 6]
        ],
        navigation: false,
        paginationNumbers: false,
        pagination: false,
        autoPlay: 5000,
        responsive: true,
        dots: false

    });


    /*------------------------------------------------------------------------*/
    /*	1.	Plugins Init
     /*------------------------------------------------------------------------*/


    ///************** SuperFish Menu *********************/

    //function initSuperFish(){

    //    $(".sf-menu").superfish({
    //        delay:  50,
    //        autoArrows: true,
    //        animation:   {opacity:'show'}
    //        //cssArrows: true
    //    });

    //    // Replace SuperFish CSS Arrows to Font Awesome Icons
    //    $('nav > ul.sf-menu > li').each(function(){
    //        $(this).find('.sf-with-ul').append('<i class="fa fa-angle-down"></i>');
    //    });
    //}

    //initSuperFish();

    ///*------------------------------------------------------------------------*/
    ///*	2.	Site Specific Functions
    // /*------------------------------------------------------------------------*/

    //$('.sub-menu').addClass('animated fadeInRight');

    new WOW().init();







});


function showPassword() {

    var key_attr = $('#password').attr('type');

    if (key_attr != 'text') {

        $('.checkbox').addClass('show');
        $('#password').attr('type', 'text');

    } else {

        $('.checkbox').removeClass('show');
        $('#password').attr('type', 'password');

    }

}
