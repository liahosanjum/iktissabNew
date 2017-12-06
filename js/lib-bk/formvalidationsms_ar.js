jQuery.noConflict();
jQuery( "#sms-form" ).validate({
    rules: {
        smsverify: {
            required: true,
            number: true
        },
    },
    // Specify the validation error messages
    messages: {
        //firstname: "Please enter your first name",
        //password: {
            //required: "Please provide a password",
            //minlength: "Your password must be at least 5 characters long"
        //},
        smsverify: "نرجو كتابة البريد الإلكتروني الصحيح",

        ///confirmnewemail:  "نرجو كتابة نفس البريد الالكتروني",
    },
    submitHandler: function(form) {
        form.submit();
    }
});




jQuery.extend(jQuery.validator.messages, {
    required: "نرجو تعبئة هذه الخانة",
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