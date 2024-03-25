<?php
// Register custom WPBakery element
function register_custom_calendar_vc_element() {
    vc_map(array(
        'name' => __('Custom Event Calendar', 'text-domain'),
        'base' => 'custom_calendar_vc_element',
        'category' => __('Content', 'text-domain'),
        'params' => array(
            array(
                'type' => 'colorpicker',
                'heading' => __('Background Color', 'text-domain'),
                'param_name' => 'background_color',
                'description' => __('Choose a background color for the calendar', 'text-domain'),
            ),
            array(
                'type' => 'attach_image',
                'heading' => __('Background Image', 'text-domain'),
                'param_name' => 'background_image',
                'description' => __('Upload or select a background image for the calendar', 'text-domain'),
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Title Color', 'text-domain'),
                'param_name' => 'title_color',
                'description' => __('Enter title color for the calendar, RGBA format (e.g., rgba(255, 0, 0, 0.5) ', 'text-domain'),
            ),
            array(
                'type' => 'colorpicker',
                'heading' => __('Text Color', 'text-domain'),
                'param_name' => 'text_color',
                'description' => __('Choose text color for the calendar', 'text-domain'),
            ),
            array(
                'type' => 'textfield',
                'heading' => __('Event Background Color', 'text-domain'),
                'param_name' => 'event_background_color',
                'description' => __('Choose a background color for event in RGBA format (e.g., rgba(255, 0, 0, 0.5) for red with 50% transparency).', 'text-domain'),
            ),
            array(
                'type' => 'dropdown',
                'param_name' => 'event_display',
                'heading' => __('Event display Style', 'text-domain'),
                'description' => __('Choose between two styles.', 'text-domain'),
                'value' => array(
                    __('Show as an Icon', 'text-domain') => 'icon',
                    __('show as text', 'text-domain') => 'text',
                ),
                'std' => 'icon', // Default to Icon
            ),

        ),
    ));
}
add_action('vc_before_init', 'register_custom_calendar_vc_element');

// Render method for custom WPBakery element
function render_custom_calendar_vc_element($atts, $content = null) {
    // Extract shortcode attributes
    /*
    extract(shortcode_atts(array(
        'background_color' =>'',
        'background_image' =>'',
    ), $atts));*/
    
    return generate_custom_event_calendar($atts);
}
add_shortcode('custom_calendar_vc_element', 'render_custom_calendar_vc_element');

