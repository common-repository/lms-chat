<?php

/**
 * Plugin Name: LMS Chat
 * Version: 1.2.2
 * Description: LMS Chat allow user to communicate with each other and with course teacher. Admin / teacher can capable to active chat application for each course or can enable globally for each courch. We use modern tachnology like scss & reactjs for UI for batter user experience. 
 * Author: Omar Faruque
 * Author URI: https://www.linkedin.com/in/omarfaruque2020/
 * Requires at least: 4.4.0
 * Tested up to: 6.2.2
 * Text Domain: lms-conversation
 * Learndash LMS tested up to: 3.6.0
 */

define('LMSC_TOKEN', 'lms_conversition');
define('LMSC_VERSION', '1.2.0');
define('LMSC_FILE', __FILE__);
define('LMSC_PLUGIN_NAME', 'LMS Chat');
if(!defined('LMSC_USER_LIMIT')) define('LMSC_USER_LIMIT', 10);

// Init.
add_action('plugins_loaded', 'lmsc_init');
if (!function_exists('lmsc_init')) {
    /**
     * Load plugin text domain
     *
     * @return  void
     */
    function lmsc_init()
    {
        $plugin_rel_path = basename(dirname(__FILE__)) . '/languages'; /* Relative to WP_PLUGIN_DIR */
        load_plugin_textdomain('lms-conversation', false, $plugin_rel_path);
    }
}

// Loading Classes.
if (!function_exists('LMSC_autoloader')) {

    function LMSC_autoloader($class_name)
    {
        if (0 === strpos($class_name, 'LMSC')) {
            $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
            $class_file = 'class-' . str_replace('_', '-', strtolower($class_name)) . '.php';
            require_once $classes_dir . $class_file;
        }
    }
}
spl_autoload_register('LMSC_autoloader');

// Backend UI.
if (!function_exists('LMSC_Backend')) {
    function LMSC_Backend()
    {
        return LMSC_Backend::instance(__FILE__);
    }
}


if (!function_exists('LMSC_Public')) {
    function LMSC_Public()
    {
        return LMSC_Public::instance(__FILE__);
    }
}


if (!function_exists('LMSC_Helper')) {
    function LMSC_Helper()
    {
        return LMSC_Helper::instance(__FILE__);
    }
}

// Front end.
LMSC_Public();

if (is_admin()) {
    LMSC_Backend();
}

new LMSC_Api();
