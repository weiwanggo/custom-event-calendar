<?php
/*
Plugin Name: Custom Event Calendar
Description: Customizes the event calendar settings.
Version: 1.0
Author: Your Name
*/
function custom_event_calendar_add_menu() {
    add_menu_page(
        'Custom Event Calendar Settings', // Page title
        'Event Calendar', // Menu title
        'manage_options', // Capability required to access the menu page
        'custom_event_calendar_event_editor', // Menu slug
        'custom_event_calendar_event_editor_page' // Callback function to render the submenu page
    );
}
add_action('admin_menu', 'custom_event_calendar_add_menu');

// Enqueue scripts and styles for color picker and media uploader
/*
function custom_event_calendar_admin_scripts() {
    wp_enqueue_script('custom-event-calendar-admin-script', plugin_dir_url(__FILE__) . '../js/admin-script.js', array('jquery', 'wp-color-picker'), '1.0', true);
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'custom_event_calendar_admin_scripts');
*/
function custom_event_calendar_event_editor_page() {
    // Handle form submissions
    if (isset($_POST['submit'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_events';

        // Add event
        if ($_POST['action'] == 'add') {
            $icon = $_POST['icon'];
            $date = $_POST['date'];
            $time = $_POST['hours'] . ':' . $_POST['minutes'];
            $title = sanitize_text_field($_POST['title']);
            $url_link = esc_url($_POST['url_link']);
            $wpdb->insert($table_name, array('icon' => $icon, 'date' => $date, 'time' => $time, 'title' => $title, 'url_link' => $url_link));
        }

        // Delete event
        elseif ($_POST['action'] == 'delete') {
            $event_id = $_POST['event_id'];
            $wpdb->delete($table_name, array('id' => $event_id));
        }
    }

    // Display form for adding custom events
    ?>
    <div class="wrap">
        <h1>Custom Events</h1>
        <form method="post" action="">
            <h2>Add Custom Event</h2>
            <label>Date: </label><input type="date" name="date" required><br>
            <label>Time: </label>
            <select name="hours">
                <?php
                for ($hour = 0; $hour <= 23; $hour++) {
                    echo '<option value="' . sprintf("%02d", $hour) . '">' . sprintf("%02d", $hour) . '</option>';
                }
                ?>
            </select>
            :
            <select name="minutes">
                <?php
                for ($minute = 0; $minute <= 59; $minute++) {
                    echo '<option value="' . sprintf("%02d", $minute) . '">' . sprintf("%02d", $minute) . '</option>';
                }
                ?>
            </select><br>
            <label>Title 标题: </label><input type="text" name="title" required><br>
            <label>URL Link 链接: </label><input type="url" name="url_link"><br>
            <label>Pick an icon图标: 
            <select name="icon" id="icon-select">
                <option value="fa-calendar">日历</option>
                <option value="fa-music">音乐</option>
                <option value="fa-film">影片</option>
                <option value="fa-birthday-cake">蛋糕</option>
                <option value="fa-basketball-ball">篮球</option>
                <option value="fa-guitar">吉他</option>
                <option value="fa-user">单人</option>
                <option value="fa-users">多人</option>
                <option value="fa-ticket">开票</option>
                <option value="fa-sailboat">帆船</option>
             <!-- Add more options as needed -->
            </select><br>
            <input type="hidden" name="action" value="add">
            <input type="submit" name="submit" value="Add Event">
        </form>

        <!-- Display existing custom events -->
        <h2>Existing Custom Events</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ICON</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Title</th>
                    <th>URL Link</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                global $wpdb;
                $events = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}custom_events");
                foreach ($events as $event) {
                    echo "<tr>";
                    echo "<td>{$event->id}</td>";
		    echo "<td><i class='fas {$event->icon}'></i></td>";
                    echo "<td>{$event->date}</td>";
                    echo "<td>{$event->time}</td>";
                    echo "<td>{$event->title}</td>";
                    echo "<td>{$event->url_link}</td>";
                    echo "<td><form method='post' action=''><input type='hidden' name='event_id' value='{$event->id}'><input type='hidden' name='action' value='delete'><input type='submit' name='submit' value='Delete'></form></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

