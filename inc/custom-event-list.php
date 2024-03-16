
<?php


function get_all_events_list($timeline){
    $cache_key = 'custom_events_calendar_get_event_list'. $timeline;
    $cached_result = wp_cache_get($cache_key);

    if (empty($cached_result)) {
        // Execute your database query
        $cached_result = query_all_events_list($timeline);
        // Cache the query result for 1 hour
        wp_cache_set($cache_key, $cached_result, 'custom', 16800);
    } 

    return $cached_result;
}
function query_all_events_list($timeline)
{

    global $wpdb;

    $sql= "
        SELECT p.*
        FROM $wpdb->posts AS p
        INNER JOIN $wpdb->postmeta AS pm ON p.ID = pm.post_id
        WHERE pm.meta_key = '_wolf_event_start_date'
        AND p.post_status = 'publish'";

    $date_format = '%' . 'd-%' . 'm-%' . 'Y';
    // // Add meta query condition based on the timeline parameter
    if ($timeline == 'future') {
        // Retrieve posts with dates in the future
        $sql .= "
            AND STR_TO_DATE(pm.meta_value, '%d-%m-%Y') >= CURDATE()
            order by STR_TO_DATE(pm.meta_value, '%d-%m-%Y')
    ";
    } elseif ($timeline == 'past') {
        // Retrieve posts with dates in the past
        $sql .= "
        AND STR_TO_DATE(pm.meta_value, '%d-%m-%Y') < CURDATE()
        order by STR_TO_DATE(pm.meta_value, '%d-%m-%Y') desc
    ";
    }

    return $wpdb->get_results($sql);
}

function custom_event_list_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'timeline' => 'future', // Default to future
        ),
        $atts
    );
    // Extract month and year from attributes
    $timeline = $atts['timeline'];

    $events_list = get_all_events_list($timeline);

    $html = '<div class="v-events-section">';
    if (!empty($events_list)) {
        foreach ($events_list as $e) {
	    $event_title = esc_attr(get_the_title($e->ID));
            $event_date = get_post_meta($e->ID, '_wolf_event_start_date', true);

            // Create a DateTime object from the date string
            $date = DateTime::createFromFormat('d-m-Y', $event_date);

	    if ($date) {
                 // Format the date as "Y M d"
                $event_date = $date->format('Y-m-d');
            } 

            $event_link = get_permalink($e->ID);
            $event_location = get_post_meta($e->ID, '_wolf_event_location', true);
            $event_venue = get_post_meta($e->ID, '_wolf_event_venue', true);

	    $html .= '<div class="v-event-row">';
            $html .= '<div class="v-event-date">' . $event_date . '</div>';
            $html .= '<div class="v-event-field v-event-title"><a class="v-event-link" href="'  . $event_link . '">' . $event_title . '</div>';
            $html .= '<div class="v-event-field v-event-location">' . $event_location . '</div>';
            $html .= '<div class="v-event-field v-event-venue">' . $event_venue . '</div>';

            $html .= '</div>';   // v-event-row
        }
    } else {
        $html .= '<div>No events found.</div>';
    }
    $html .= '</div>';

    return $html;
}
add_shortcode('custom_event_list', 'custom_event_list_shortcode');
