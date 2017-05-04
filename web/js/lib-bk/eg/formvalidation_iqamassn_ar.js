jQuery.noConflict();
jQuery( "#iqamassn-form" ).validate({
    rules: {
        iqamassn_new:
        {
            required: true,
            maxlength: 14,
            minlength: 14
        },
        confirm_iqamassn_new:{
            required: true,
            maxlength: 14,
            minlength: 14
        },
        comment_iqamassn: {
            required: true,
        },
    },
    // Specify the validation error messages
    messages:
    {
        //firstname: "Please enter your first name",
        //password: {
        //required: "Please provide a password",
        //minlength: "Your password must be at least 5 characters long"
        //},
        iqamassn_new: 'Please enter a valid 14 digits Iqama Id/SSN for Egypt ar',
        confirm_iqamassn_new:  'Please enter a valid 14 digits Iqama Id/SSN for Egypt ar',
        comment_iqamassn: "please enter comments ar"
    }
});

jQuery("#iqamassn-submit").click(function()
{
    if( (jQuery("#iqamassn_new").val() !="") && (jQuery("#iqamassn_registered").val() !=""))
    {
        if (jQuery("#iqamassn_new").val() == jQuery("#iqamassn_registered").val()) {
            jQuery("#iqamassn_new").removeClass("valid");
            jQuery("#iqamassn_new").addClass("error");
            jQuery("#iqamassn_new-error").remove();
            jQuery("#iqamassn_new").after('<label id="iqamassn_new-error" class="error" for="iqamassn_new">Your new Iqama Id/SSN and old Iqama Id/SSN must not be same ar</label>');
            return false;
        }
    }
    if((jQuery("#iqamassn_new").val() !="") &&  (jQuery("#confirm_iqamassn_new").val() !=""))
    {
        if (jQuery("#iqamassn_new").val() != jQuery("#confirm_iqamassn_new").val()) {
            jQuery("#confirm_iqamassn_new").removeClass("valid");
            jQuery("#confirm_iqamassn_new").addClass("error");
            jQuery("#confirm_iqamassn_new-error").remove();
            jQuery("#confirm_iqamassn_new").after('<label id="confirm_iqamassn_new-error" class="error" for="confirm_iqamassn_new">Your new Iqama Id/SSN and confirm new Iqama Id/SSN must be same ar</label>');
            return false;
        }
    }

});

jQuery(document).ready(function() {
    jQuery( "#iqamassn_new" ).blur(function() {
        if( (jQuery("#iqamassn_new").val() !="") &&  (jQuery("#iqamassn_registered").val() !=""))
        {
            if (jQuery("#iqamassn_new").val() == jQuery("#iqamassn_registered").val()) {
                jQuery("#iqamassn_new").removeClass("valid");
                jQuery("#iqamassn_new").addClass("error");
                jQuery("#iqamassn_new-error").remove();
                jQuery("#iqamassn_new").after('<label id="iqamassn_new-error" class="error" for="iqamassn_new">Your new Iqama Id/SSN and confirm new Iqama Id/SSN must not be same ar</label>');
                return false;
            }
        }

    });

    jQuery( "#confirm_iqamassn_new" ).blur(function() {
        if( (jQuery("#iqamassn_new").val() !="") &&  (jQuery("#confirm_iqamassn_new").val() !=""))
        {
            if (jQuery("#iqamassn_new").val() != jQuery("#confirm_iqamassn_new").val()) {
                jQuery("#confirm_iqamassn_new").removeClass("valid");
                jQuery("#confirm_iqamassn_new").addClass("error");
                jQuery("#confirm_iqamassn_new-error").remove();
                jQuery("#confirm_iqamassn_new").after('<label id="confirm_iqamassn_new-error" class="error" for="confirm_iqamassn_new">Your new Iqama Id/SSN and confirm new Iqama Id/SSN must be same ar</label>');
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