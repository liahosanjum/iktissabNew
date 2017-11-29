jQuery.noConflict();
jQuery( "#email-form" ).validate({
    rules: {
        newemail: {
            required: true,
            email: true
        },
        confirmnewemail: {
            required: true,
            email: true
        },
    },
    // Specify the validation error messages
    messages: {
        //firstname: "Please enter your first name",
        //password: {
        //required: "Please provide a password",
        //minlength: "Your password must be at least 5 characters long"
        //},
        newemail: "Please enter a valid email address",
        confirmnewemail:  "Please enter a valid confirm email address",
    },
});

jQuery( document ).ready(function() {
    jQuery( "#confirmnewemail" ).blur(function() {
        if ((/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(jQuery("#confirmnewemail").val())))
        {
            if(jQuery("#confirmnewemail").val() != jQuery("#newemail").val())
            {
                jQuery("#confirmnewemail").removeClass( "valid" );
                jQuery("#confirmnewemail").addClass( "error" );
                jQuery("#confirmnewemail-error").remove();
                jQuery("#confirmnewemail").after( '<label id="confirmnewemail-error" class="error" for="confirmnewemail">Your new email and confirm new email must be same</label>' );
                return false;
            }
        }
    });

    jQuery( "#email-submit" ).click(function() {
        if ((/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(jQuery("#confirmnewemail").val())))
        {
            if(jQuery("#confirmnewemail").val() != jQuery("#newemail").val())
            {
                jQuery("#confirmnewemail").removeClass( "valid" );
                jQuery("#confirmnewemail").addClass( "error" );
                jQuery("#confirmnewemail-error").remove();
                jQuery("#confirmnewemail").after( '<label id="confirmnewemail-error" class="error" for="confirmnewemail"> Your new email and confirm new email must be same </label>' );
                return false;
            }
        }
    });
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

/*
 jQuery( document ).ready(function() {

 jQuery( "#currentemail" ).blur(function() {
 if (!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test($( this ).val())))
 {

 }
 });

 });*/
