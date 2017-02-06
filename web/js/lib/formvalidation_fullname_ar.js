jQuery.noConflict();
jQuery( "#fullname-form" ).validate({
    rules: {full_name:{required: true},
        comment_fullname:
        {
            required: true
        },
    },
    // Specify the validation error messages
    messages: {full_name: "Please enter your full name ar", comment_fullname: "please enter comments ar"},
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
                jQuery("#full_name").after( '<label id="full_name-error" class="error" for="full_name">Name must be in two parts ar</label>' );
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
            jQuery("#full_name").after( '<label id="full_name-error" class="error" for="full_name">Name must be in two parts ar</label>' );
            return false;

        }
    }
});

