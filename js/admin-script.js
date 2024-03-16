jQuery(document).ready(function($) {
    // Color picker
    $('.color-picker').wpColorPicker();

    // Media uploader
    $('.upload-button').click(function(e) {
        e.preventDefault();

        var $button = $(this);
        var file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Choose or Upload Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        file_frame.on('select', function() {
            var attachment = file_frame.state().get('selection').first().toJSON();
            $button.prev('input[type="text"]').val(attachment.url);
        });

        file_frame.open();
    });

    $('#preview-button').click(function() {
        // Get the values from the input fields
        var background_image = $('[name="custom_event_calendar_background_image"]').val();
        var background_color = $('[name="background_color"]').val();
        var text_color = $('[name="text_color"]').val();
        var layout_type = $('[name="custom_event_calendar_layout"]').val();

        // Use AJAX to send the values to the server and update the calendar preview
        $.ajax({
            url: custom_event_calendar_ajax.ajaxurl, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'update_calendar_preview',
                background_image: background_image,
                background_color: background_color,
                text_color: text_color,
                layout_type: layout_type
            },
            success: function(response) {
                // Replace the calendar preview with the updated content
                $('#event-calendar-preview').html(response);
            }
        });
    });
});
