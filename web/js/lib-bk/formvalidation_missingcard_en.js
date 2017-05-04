jQuery.noConflict();
jQuery( "#missingcard-form" ).validate({
    rules: {
        new_iktissab_id:{required: true, digits:true, minlength: 8, maxlength: 8,check_iktSA: true},
        confirm_iktissab_id:{ required: true, digits: true, minlength:8, maxlength:8,equalTo:'#new_iktissab_id'},
        comment_missingcard:{ required: true },
    },
    // Specify the validation error messages

});
jQuery.extend(jQuery.validator.messages, {
    required: "This field is required",
    digits: "Please enter only numbers",
    maxlength: "Please enter valid IKTISSAB ID",
    minlength: "Please enter valid IKTISSAB ID",
    equalTo: "Please enter same IKTISSAB ID again",

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
jQuery.validator.addMethod("check_iktSA", function(new_iktissab_id, element) {
    new_iktissab_id = new_iktissab_id.replace(/\s+/g, "");
    return this.optional(element) || new_iktissab_id.match(/^9\d{7}$/);
}, "Please enter valid IKTISSAB ID");

