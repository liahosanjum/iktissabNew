jQuery.noConflict();
jQuery( "#fullname-form" ).validate({
    rules: {
        full_name:{
            required: true

        },
        comment_fullname:
        {
            required: true
        },
    },
    // Specify the validation error messages
    messages: {
        //firstname: "Please enter your first name",
        //password: {
        //required: "Please provide a password",
        //minlength: "Your password must be at least 5 characters long"
        //},
        full_name: {required: "Please enter your full name" },
        comment_fullname: {required: "please enter comments"}
    },
});


jQuery( document ).ready(function()
{
    jQuery( "#full_name" ).blur(function()
    {
        if( (jQuery("#full_name").val() != ""))
        {
            var str = jQuery("#full_name").val();
            var str_new = jQuery.trim(str);
            var res = str_new.split(" ");
            if(res.length < 2){
                jQuery("#full_name").removeClass( "valid" );
                jQuery("#full_name").addClass( "error" );
                jQuery("#full_name-error").remove();
                jQuery("#full_name").after( '<label id="full_name-error" class="error" for="full_name">Name must be in two parts</label>' );
                return false;
            }
        }
    });
});

jQuery("#fullname-submit").click(function()
{
    if( (jQuery("#full_name").val() != ""))
    {
        var str = jQuery("#full_name").val();
        var str_new = jQuery.trim(str);
        var res = str_new.split(" ");
        if(res.length < 2){
            jQuery("#full_name").removeClass( "valid" );
            jQuery("#full_name").addClass( "error" );
            jQuery("#full_name-error").remove();
            jQuery("#full_name").after( '<label id="full_name-error" class="error" for="full_name">Name must be in two parts</label>' );
            return false;

        }

        /*if ((/^[a-zA-Z0-9- ]*$/.test(jQuery("#full_name").val())) == false)
         {
         jQuery("#full_name").removeClass( "valid" );
         jQuery("#full_name").addClass( "error" );
         jQuery("#full_name-error").remove();
         jQuery("#full_name").after( '<label id="full_name-error" class="error" for="full_name">Only alpha numeric characters are allowed</label>' );
         return false;
         }*/
    }
});




jQuery.extend(jQuery.validator.messages, {
    required: "This field is required",
    /*remote: "Please fix this field.",
     email: "Please enter a valid email address123.",
     url: "Please enter a valid URL.",
     date: "Please enter a valid date.",
     dateISO: "Please enter a valid date (ISO).",
     number: "Please enter a valid number.",
     digits: "Please enter only digits.",
     creditcard: "Please enter a valid credit card number.",
     equalTo: "Please enter the same value again.",
     accept: "Please enter a value with a valid extension.",
     maxlength: jQuery.validator.format("Please enter no more than {0} characters."),
     minlength: jQuery.validator.format("Please enter at least {0} characters."),
     rangelength: jQuery.validator.format("Please enter a value between {0} and {1} characters long."),
     range: jQuery.validator.format("Please enter a value between {0} and {1}."),
     max: jQuery.validator.format("Please enter a value less than or equal to {0}."),
     min: jQuery.validator.format("Please enter a value greater than or equal to {0}.")*/
});