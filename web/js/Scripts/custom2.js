
$(document).ready(function () {


     $('#Preloader-wrapper').fadeOut('slow', function () { $(this).remove();});


    // var viewport = $(window).width();
    // if (viewport >= 992) { addhoverTomenu(); };
     

    // $(window).on('resize', function () {
    //     alert("resize");
    //     var viewport = $(window).width();
    //     if (viewport >= 992) {addhoverTomenu();};

    //});



    // function addhoverTomenu() {
    //     alert("dv");

    //         $(".dropdown").hover(
    //              function () {
    //                  $('.dropdown-menu', this).stop(true, true).fadeIn("fast");
    //                  $(this).toggleClass('open');
    //              },
    //              function () {
    //                  $('.dropdown-menu', this).stop(true, true).fadeOut("fast");
    //                  $(this).toggleClass('open');
    //              });
    //    };



    
 //$('#myCarousel').carousel({ interval: 5000})
     
     var owl = $('.owl-carousel');
     owl.owlCarousel({
         //                animateOut: 'fadein',
         items: 1,
         loop: true,
         autoplay: true,
         autoplayTimeout: 5000,
         autoplaySpeed: 1500,
         navSpeed: 1000,
         dotsSpeed: 1000,
         autoplayHoverPause: false,
         rtl: true,
         autoHeight: false,
         touchDrag: true,
         nav: false,
         //navText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"],
         dots: true,

     });


     var owlmembers = $('.owl-members');
     owlmembers.owlCarousel({
         rtl: true,
         autoplay: true,
         autoplayTimeout: 4000,
         autoplaySpeed: 700,
         loop: true,
         margin: 30,
         dots: false,
         nav: false,
         responsive: {
             0: {
                 items: 2
             },
             600: {
                 items: 4
             },
             1000: {
                 items: 6
             }
         }

     });

     /* code for tabs pages about us and tracks page  */
     jQuery('#myTab').tabCollapse();
    
});

