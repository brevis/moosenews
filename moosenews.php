<?php

/**
* Moose News
*
* @wordpress-plugin
* Plugin Name:       Moose News
* Description:       [Обсудить] page for <a href="http://rcmp.me">RCMP.me</a>
* Version:           1.0.0
* Author:            brevis
* Author URI:        https://twitter.com/brevis_ua
* License:           MIT
* License URI:       http://opensource.org/licenses/MIT
* Text Domain:       moosenews
*/

// If this file is called directly, abort.
if (!defined('WPINC')) die;

/**
 * The code that runs during plugin activation.
 */
register_activation_hook(__FILE__, function() {
    global $wpdb;

    // Create DB tables
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $table_name = $wpdb->prefix . MooseNews::NEWS_TABLE;
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
              id int(11) NOT NULL AUTO_INCREMENT,
              user_id int(11) NOT NULL,
              content text COLLATE utf8_unicode_ci NOT NULL,
              postdate DATETIME,
              rating int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (id)
            ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . MooseNews::VOTES_TABLE;
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
              user_id int(11) NOT NULL,
              news_id int(11) NOT NULL,
              vote_type ENUM('up', 'down')
            )";
        dbDelta($sql);
    }

    // add options
    add_option('moosenews_form_position', 'down');
    add_option('moosenews_message_maxlength', 5000);
    add_option('moosenews_message_minlength', 10);

    return true;
});

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/moosenews.php';

/**
 * Begins execution of the plugin.
 */
function run_moosenews() {
    $plugin = new MooseNews();
    $plugin->run();
}
run_moosenews();
