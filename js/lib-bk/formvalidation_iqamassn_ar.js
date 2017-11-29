jQuery.noConflict();
jQuery( "#iqamassn-form" ).validate({
    rules: {
        iqamassn_new:
        {
            required: true,
            digits:true,
            maxlength: 10,
            minlength: 10
        },
        confirm_iqamassn_new:{
            required: true,
            number: true,
            maxlength: 10,
            minlength: 10
        },
        comment_iqamassn: {
            required: true,
        },
    },
});

jQuery( "#iqamassn-submit" ).click(function()
{
    if( (jQuery("#iqamassn_new").val() !="") && (jQuery("#iqamassn_registered").val() !=""))
    {
        if (jQuery("#iqamassn_new").val() == jQuery("#iqamassn_registered").val())
        {
            jQuery("#iqamassn_new").removeClass("valid");
            jQuery("#iqamassn_new").addClass("error");
            jQuery("#iqamassn_new-error").remove();
            jQuery("#iqamassn_new").after('<label id="iqamassn_new-error" class="error" for="iqamassn_new">رقم الهوية أو الإقامة غير متطابق</label>');
            return false;
        }
    }
});

jQuery.extend(jQuery.validator.messages, {
    required: "نرجو تعبئة هذه الخانة",
    digits: "Please enter only numbers",
    maxlength: "رقم الهوية أو الإقامة غير صحيح",
    minlength: "رقم الهوية أو الإقامة غير صحيح",
    equalTo: "رقم الهوية أو الإقامة غير متطابق",
    /*remote: "Please fix this field.",
     email: "Please enter a valid email address123.",
     url: "Please enter a valid URL.",
     date: "Please enter a valid date.",
     dateISO: "Please enter a valid date (ISO).",
     number: "Please enter a valid number.",
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