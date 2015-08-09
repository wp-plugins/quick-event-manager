jQuery(document).ready(function ($) {
    var custom_uploader;
    $('#upload_media_button').click(function (e) {
        e.preventDefault();
        if (custom_uploader) {custom_uploader.open(); return; }
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Select Background Image', button: {text: 'Insert Image'}, multiple: false});
        custom_uploader.on('select', function () {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#upload_image').val(attachment.url);
        });
        custom_uploader.open();
    });
    $('#upload_submit_button').click(function (e) {
        e.preventDefault();
        if (custom_uploader) {custom_uploader.open(); return; }
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Select Submit Button Image', button: {text: 'Insert Image'}, multiple: false });
        custom_uploader.on('select', function () {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#submit_image').val(attachment.url);
        });
        custom_uploader.open();
    });
    $('#upload_event_image').click(function (e) {
        e.preventDefault();
        if (custom_uploader) {custom_uploader.open(); return; }
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Select Event Image', button: {text: 'Insert Image'}, multiple: false});
        custom_uploader.on('select', function () {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#event_image').val(attachment.url);
        });
        custom_uploader.open();
    });
    $('.qem-color').wpColorPicker();
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