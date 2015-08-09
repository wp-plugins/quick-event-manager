jQuery(document).ready(function ($) {
    $("#yourplaces").keyup(function () {
        var model= document.getElementById('yourplaces');
        var number = $('#yourplaces').val()
        if (number == 1)
                $("#morenames").hide();
            else {
                $("#morenames").show();
            }
    });

});
