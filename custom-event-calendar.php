<?php
/*
Plugin Name: Custom Event Calendar
Description: Display events on a calendar based on the _wolf_event_start_date post meta.
*/

// Include the admin settings file
require_once plugin_dir_path(__FILE__) . 'inc/custom-event-calendar-settings.php';
require_once plugin_dir_path(__FILE__) . 'inc/custom-event-list.php';
require_once plugin_dir_path(__FILE__) . 'inc/custom-event-data.php';

// Define the function to create the custom events table
function custom_event_calendar_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_events';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        date DATE NOT NULL,
        time TIME,
        title varchar(255) NOT NULL,
        url_link varchar(255),
        last_update_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        icon varchar(50),
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Hook into plugin activation to create the custom events table
register_activation_hook(__FILE__, 'custom_event_calendar_create_table');


$month_zh = array(
    '1' => '一月',
    '2' => '二月',
    '3' => '三月',
    '4' => '四月',
    '5' => '五月',
    '6' => '六月',
    '7' => '七月',
    '8' => '八月',
    '9' => '九月',
    '10' => '十月',
    '11' => '十一月',
    '12' => '十二月',
);

// Enqueue necessary scripts
function custom_event_calendar_scripts()
{
    wp_enqueue_script('custom-event-calendar', plugin_dir_url(__FILE__) . 'custom-event-calendar.js', array('jquery'), '1.0', true);
    wp_localize_script('custom-event-calendar', 'custom_event_calendar_ajax', array('ajaxurl' => admin_url('admin-ajax.php')));
    // Enqueue CSS file
    wp_enqueue_style('custom-event-calendar-style', plugin_dir_url(__FILE__) . 'custom-event-calendar.css');
}
add_action('wp_enqueue_scripts', 'custom_event_calendar_scripts');

// Add inline CSS for background image
function custom_event_calendar_inline_styles()
{
    $background_image = get_option('custom_event_calendar_background_image');
    $background_color = get_option('custom_event_calendar_background_color');
    $text_color = get_option('custom_event_calendar_text_color');

    echo generate_inline_styles($background_color, $background_image, $text_color);
}
add_action('wp_head', 'custom_event_calendar_inline_styles');

function generate_inline_styles($background_color, $background_image, $text_color)
{
    $inline_style = '';
    if ($background_image || $background_color || $text_color) {
        $inline_style .= '<style type="text/css">';
        $inline_style .= '.custom-event-calendar {';
        if ($background_color) {
            $inline_style .= '  background-color: ' . esc_attr($background_color) . ';';
        }
        if ($background_image) {
            $inline_style .= '  background-image: url("' . esc_url($background_image) . '");';
        }

        $inline_style .= '  background-size: cover;'; // This property ensures the background image covers the entire element
        $inline_style .= '  background-position: center;'; // This property centers the background image within the element
        $inline_style .= '}';
        if ($text_color) {
            $inline_style .= '.header-day, .day, .calendar th {color: ' . $text_color . '}';
        }
        echo '</style>';
        $inline_style .= '</style>';
    }
    return $inline_style;
}

// Shortcode for displaying the calendar
function custom_event_calendar_shortcode($atts)
{
    $calendar_layout = get_option('custom_event_calendar_layout');
    $atts = shortcode_atts(
        array(
            'month' => date('m'), // Default to current month
            'year' => date('Y'), // Default to current year
        ),
        $atts
    );
    // Extract month and year from attributes
    $month = $atts['month'];
    $year = $atts['year'];

    return generate_custom_event_calendar($month, $year, $calendar_layout);
}
add_shortcode('custom_event_calendar', 'custom_event_calendar_shortcode');

function generate_custom_event_calendar($month, $year, $calendar_layout)
{
    global $month_zh;
    $calendar_html = '<div class="custom-event-calendar">';
    // Get the current month name based on the locale
    $current_month = $month_zh[intval($month)] . ' ' . date('F', mktime(0, 0, 0, $month, 1, $year));
    $calendar_html .= '<h2 class="calendar-title">' . $current_month . ' ' . $year . '</h2>'; // Add title with month and year
    $calendar_html .= generateEventCalendarControl($month, $year);
    $calendar_html .= generateEventsCalendar($month, $year);
    $calendar_html .= '</div>';

    return $calendar_html;
}

function generateEventCalendarControl($month, $year)
{
    $calendar_html = '';
    $locale = get_locale();
    global $month_zh;

    // English strings
    $strings_en = array(
        'prev_month' => 'Previous Month',
        'next_month' => 'Next Month',
        // Add more English strings as needed
    );

    // Chinese strings
    $strings_zh = array(
        'prev_month' => '上个月',
        'next_month' => '下个月',
        // Add more Chinese strings as needed
    );

    $strings = ($locale === 'zh_CN') ? $strings_zh : $strings_en;
    $calendar_html .= '<div class="calendar-controls">';

    /*
    $calendar_html .= '<a href="#" class="prev-month" data-month="' . ($month == 1 ? 12 : $month - 1) . '" data-year="' . ($month == 1 ? $year - 1 : $year) . '">' . $strings['prev_month'] . '</a>';
    $calendar_html .= ' | ';
    $calendar_html .= '<a href="#" class="next-month" data-month="' . ($month == 12 ? 1 : $month + 1) . '" data-year="' . ($month == 12 ? $year + 1 : $year) . '">' . $strings['next_month'] . '</a>';
     */

    // Add month selection dropdown
    $calendar_html .= '<div class="select-row">';
    $calendar_html .= '<div class="select-col">';
    $calendar_html .= '<select class="month-select">';

    for ($i = 1; $i <= 12; $i++) {
        $month_txt = $month_zh[$i] . ' ' . date('F', mktime(0, 0, 0, $i, 1, $year));
        $calendar_html .= '<option value="' . $i . '"' . ($month == $i ? ' selected' : '') . '>' . $month_txt . '</option>';
    }

    $calendar_html .= '</select>';
    $calendar_html .= '</div>';
    $calendar_html .= '<div class="select-col">';

    // Add year selection dropdown
    $calendar_html .= '<select class="year-select">';

    for ($i = date('Y'); $i >= 2019; $i--) {
        $calendar_html .= '<option value="' . $i . '"' . ($year == $i ? ' selected' : '') . '>' . $i . '</option>';
    }

    $calendar_html .= '</select>';
    $calendar_html .= '</div>';

    $calendar_html .= '</div></div>';

    return $calendar_html;
}

function generateEventsCalendar($month, $year)
{
    // Generate calendar container
    $events_data = get_events_data();
    $calendar_html = '<div class="calendar-container">';
    $calendar_html .= '<div class="happy-birthday-animation" id="happy-birthday-animation"><h2 style="color:red;">Happy Birthday Boss!!!</div>';
    // Generate calendar header
    $calendar_html .= '<div class="calendar-header">';
    $calendar_html .= '<div class="header-day">周日<br/>Sun</div>';
    $calendar_html .= '<div class="header-day">周一<br/>Mon</div>';
    $calendar_html .= '<div class="header-day">周二<br/>Tue</div>';
    $calendar_html .= '<div class="header-day">周三<br/>Wed</div>';
    $calendar_html .= '<div class="header-day">周四<br/>Thu</div>';
    $calendar_html .= '<div class="header-day">周五<br/>Fri</div>';
    $calendar_html .= '<div class="header-day">周六<br/>Sat</div>';
    $calendar_html .= '</div>'; // close calendar-header

    // Generate calendar body
    $calendar_html .= '<div class="calendar-body">';

    // Get the timestamp of the first day of the month
    $first_day_timestamp = mktime(0, 0, 0, $month, 1, $year);

    // Get the number of days in the month
    $days_in_month = date('t', $first_day_timestamp);

    // Get the day of the week of the first day of the month
    $first_day_of_week = date('w', $first_day_timestamp);

    // Start the first week
    $calendar_html .= '<div class="calendar-row">';

    // Add empty cells for the days before the first day of the month
    for ($i = 0; $i < $first_day_of_week; $i++) {
        $calendar_html .= '<div class="empty-cell"></div>';
    }

    // Loop through each day of the month
    for ($day = 1; $day <= $days_in_month; $day++) {
        $day_timestamp = mktime(0, 0, 0, $month, $day, $year);

        $month = sprintf('%02d', $month);
        $day = sprintf('%02d', $day);
        $date = sprintf('%d%s%s', $year, $month, $day);

        $day_events = isset($events_data[$date]) ? $events_data[$date] : array();
        $event_count = count($day_events);

        $special_class = '';
        $special_icon = '';
        //511 the special day!!!


        $bossBDayEvent = null;
        if ($month == 5 && $day == 11) {
            $bossBDayEvent = array();
            $bossBDayEvent['title'] = 'Happy Birday Boss!';
            $bossBDayEvent['icon'] = 'fa-birthday-cake';
            $special_class .= ' special_day';
            $event_count++;
        }

         // Determine the color for the day based on the number of events
         $color_class = 'color-' . $event_count;
        // Add the day cell with events
        $calendar_html .= '<div class="calendar-cell event ' . $color_class . '">';
        $calendar_html .= '<span class=day ' . $special_class . '">' . $day . '</span>';

        // Display events for the day
        if ( $event_count > 0){
	        $calendar_html .= '<div class="event-group">';

            if ($bossBDayEvent){
                $calendar_html .= '<div class="event has-event" title="' . $bossBDayEvent["title"] . '"><a href="javascript:startBirthdayAnimation();" >';
                $calendar_html .= '<i class="fas fa-birthday-cake"></i></a></div>';
            }

            foreach ($day_events as $event) {
                $calendar_html .= '<div class="event has-event" title="' . $event["title"] . '">';
                $event_icon = isset($event["icon"]) ? $event["icon"] : "fa-calendar";
                if(isset($event["url"])){
                    $calendar_html .= '<a href="' . $event["url"] . '">';
                    $calendar_html .= '<i class="fas ' . $event_icon . '"></i></a></div>';
                } else {
                    $calendar_html .= '<i class="fas ' . $event_icon . '"></i></div>';
                }                        
            }
            $calendar_html .= '</div>'; //event-group
        }             
        $calendar_html .= '</div>'; //calendar-cell

        // If the current day is the last day of the week or the last day of the month, close the row and start a new one
        if (date('w', $day_timestamp) == 6) {
            $calendar_html .= '</div>'; // Close the current row
            if ($day < $days_in_month) {
                $calendar_html .= '<div class="calendar-row">'; // Start a new row
            }
        }
        $dayInWeek = date('w', $day_timestamp);
        if ($day == $days_in_month && $dayInWeek < 6) {
            for ($i = $dayInWeek + 1; $i <= 6; $i++) {
                $calendar_html .= '<div class="empty-cell"></div>';
            }
            $calendar_html .= '</div>';   // close last row
        }
    }

    $calendar_html .= '</div>'; // Close calendar body
    $calendar_html .= '</div>'; // Close calendar container

    return $calendar_html;
}

// Enqueue necessary scripts and localize AJAX URL
// Hook function to handle AJAX request for logged-in users

function add_ajax_cache_control_headers() {
    header("Cache-Control: public, max-age=3600"); // Cache for 1 hour (3600 seconds)
}
add_action('wp_ajax_custom_event_calendar_load_shortcode', 'add_ajax_cache_control_headers');
add_action('wp_ajax_nopriv_custom_event_calendar_load_shortcode', 'add_ajax_cache_control_headers');

add_action('wp_ajax_custom_event_calendar_load_shortcode', 'custom_event_calendar_ajax_handler');
add_action('wp_ajax_nopriv_custom_event_calendar_load_shortcode', 'custom_event_calendar_ajax_handler');

// AJAX handler function
function custom_event_calendar_ajax_handler()
{
    $shortcode = isset($_GET['shortcode']) ? $_GET['shortcode'] : ''; // Get shortcode from AJAX request
    $shortcode = stripslashes($shortcode);
    $content = do_shortcode($shortcode); // Process shortcode
    echo $content; // Output shortcode content
    exit(); // Always exit after AJAX request handling

}

// Handle AJAX request to update calendar preview
add_action('wp_ajax_update_calendar_preview', 'update_calendar_preview');
function update_calendar_preview()
{
    // Get the values sent from the client-side
    $background_image = isset($_POST['background_image']) ? sanitize_text_field($_POST['background_image']) : '';
    $background_color = isset($_POST['background_color']) ? sanitize_text_field($_POST['background_color']) : '';
    $layout_type = isset($_POST['layout_type']) ? sanitize_text_field($_POST['layout_type']) : '';
    $text_color = isset($_POST['text_color']) ? sanitize_text_field($_POST['text_color']) : '';

    // Generate the updated calendar content based on the values
    $updated_calendar_content = generate_updated_calendar_content($background_image, $background_color, $text_color, $layout_type);

    // Output the updated calendar content
    echo $updated_calendar_content;

    // Make sure to exit
    exit();
}

// Function to generate updated calendar content
function generate_updated_calendar_content($background_image, $background_color, $text_color, $layout_type)
{
    // Generate the calendar content based on the values
    // You need to implement this function based on how your calendar content is generated
    $calendar_content = ''; // Placeholder

    $calendar_content .= generate_inline_styles($background_color, $background_image, $text_color);
    $calendar_content .= generate_custom_event_calendar(date('m'), date('Y'), $layout_type);
    return $calendar_content;
}
