<?php

$all_event_data = array(); // Initialize as an empty array

// Function to get events data
function get_events_data()
{
    global $all_event_data;

    $cache_key = 'custom_event_calendar_event_data';
    $cache_group = 'custom_event';

    $all_event_data = wp_cache_get($cache_key, $cache_group);
    if (empty($all_event_data)) {
        // Execute your database query
        load_custom_events_data();
        load_events_data();
        load_releases_data();

        wp_cache_set($cache_key, $all_event_data, $cache_group, 18000);
    } else {
        //error_log('events from cache is not empty' . count($all_event_data));
    }

    return $all_event_data; // Return the complete events data
}

// Function to load custom events data
function load_custom_events_data()
{
    global $all_event_data;
    global $wpdb;

    $table_name = $wpdb->prefix . 'custom_events';
    $events = $wpdb->get_results("SELECT * FROM $table_name");

    // Check if there are any events
    if ($events) {
        // Iterate through each event
        foreach ($events as $event) {
            // Get the date of the event (assuming 'date_column' is the column name)
            $event_date = date('Ymd', strtotime($event->date));

            // Store the event data in $all_event_data array
            $all_event_data[$event_date][] = array(
                'title' => $event->title,
                'url' => $event->url_link,
                'posts_per_page' => -1, 
                'type' => 'custom',
                'icon' => $event->icon,
            );
        }
    }
    //$numOfEvents = count($all_event_data);
    //echo $numOfEvents;
}

function load_events_data()
{
    global $all_event_data;
    global $wpdb;
   // $events = $wpdb->get_results("SELECT id FROM rYf_posts p join rYf_postmeta pm on p.id = pm.post_id where p.post_type='event' and post_status = 'publish' and pm.meta_key =  '_wolf_event_start_date' and pm.meta_value is not null");
    
    $args = array(
        'post_type'      => 'event',
        'post_status'    => 'publish',
        'posts_per_page' => -1, 
        'meta_query'     => array(
            array(
                'key'          => '_wolf_event_start_date',
                'compare'      => 'EXISTS',
            ),
        ),
        'fields'         => 'ids', // Retrieve only post IDs to reduce memory usage
    );

    $query = new WP_Query( $args );

    // Check for errors in the query
    if ( $query->have_posts() ) {
        $count = 0;
        while ( $query->have_posts() ) {
            $count ++;
            $query->the_post();
            $event_id = get_the_ID();
            $event_date = get_post_meta($event_id, '_wolf_event_start_date', true);

            $event_date = DateTime::createFromFormat('d-m-Y', $event_date )->format('Ymd');
            $event_title = get_the_title($event_id);
            $event_url = get_permalink($event_id);
            $event_type = get_post_type($event_id);

            // Store only necessary fields in the events_data array
            $all_event_data[$event_date][] = array(
                'title' => $event_title,
                'url'   => $event_url,
                'type'  => $event_type,
                'icon'  => 'fa-users',
            );
        }
        //echo $count;
        wp_reset_postdata(); // Restore global post data
    } else {
        // Handle query errors
        // For simplicity, you can leave this empty for now
    }
    
    //$numOfEvents = count($all_event_data);
    //echo $numOfEvents;

}

function load_releases_data()
{
    global $all_event_data;

    $args = array(
        'post_type'      => 'release',
        'post_status'    => 'publish',
        'posts_per_page' => -1, 
        'meta_query'     => array(
            array(
                'key'          => '_wolf_release_date',
                'compare'      => 'EXISTS',
            ),
        ),
        'fields'         => 'ids', // Retrieve only post IDs to reduce memory usage
    );

    $query = new WP_Query( $args );

    // Check for errors in the query
    if ( $query->have_posts() ) {
        $count = 0;
        while ( $query->have_posts() ) {
            $count ++;
            $query->the_post();
            $event_id = get_the_ID();
            $event_date = get_post_meta($event_id, '_wolf_release_date', true);
            $event_date = date('Ymd', strtotime($event_date));
            $event_title = get_the_title($event_id);
            $event_url = get_permalink($event_id);
            $event_type = get_post_type($event_id);

            // Store only necessary fields in the events_data array
            $all_event_data[$event_date][] = array(
                'title' => $event_title,
                'url'   => $event_url,
                'type'  => $event_type,
                'icon'  => 'fa-music',
            );
        }
        //echo $count;
        wp_reset_postdata(); // Restore global post data
    } else {
        // Handle query errors
        // For simplicity, you can leave this empty for now
    }
   
    //$numOfEvents = count($all_event_data);
    //echo $numOfEvents;

}
