jQuery.noConflict();



jQuery( "#mobile-form" ).validate({
    rules: {
        iqamaid_mobile: {required: true, digits: true, minlength:14, maxlength:14 },
        mobile:
        {
            required: true,
            digits: true,
            maxlength: 10,
            minlength: 10
        },
        comment_mobile: {
            required: true,
        },
    },
    // Specify the validation error messages
    messages: {
        //firstname: "Please enter your first name",
        //password: {
        //required: "Please provide a password",
        //minlength: "Your password must be at least 5 characters long"
        //},
        iqamaid_mobile: {required: 'Please enter a valid 14 digits Iqama Id/SSN for Egypt', digits: "Please enter digits only"},
        mobile: {required: "Please enter a valid  10 digits mobile number for Egypt" , digits: "Please enter digits only" },
        comment_mobile: "please enter comments"

    },
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