<?php
/*
Plugin Name: A7XSLX Excel File Manager
Description: WordPress plugin wrapper for a standalone PHP micro application to handle MS Excel xlsx files using PhpSpreadsheet.
Version: 1.0.0
Author: Kilo Code
License: GPL v2 or later

This is wordpress plugin as wrapper for standalone php micro application to handle Excel and CSV files (.xlsx, .xlsm, .xls, .csv).

Application has it's own composer file and vendor directory with phpoffice/phpspreadsheet.

Application has configured working directory to store excel files, 
wordpress plugin set this directory to be in wp-content/uploads/a7xslx directory.

Wordpress plugin register button on top admin toolbar as link to application.


Application has database tables:
- a7xslx_files (id int auto_inc, filename varchar(255), filepath varchar(255), uploaded_at datetime)
- a7xslx_

Application has screen with list of excel files.
Application has button to uploade new file.
Application has screen to edit (change name) and delete file
Application has screen to view excel file as table (top 50 rows).
Application has button to download files.

Application register a GET endpoint to get file info as json, query string parameters are:
- file_id - id of file
returns:
- sheets - array of objects (sheet name, rows count)

Application register a GET endpoint to get data from file as json, query string parameters are:
- file_id - id of file
- sheet - sheet name
- columns - comma separated list of columns to return (optional)
- limit - limit of rows to return (optional)
- offset - offset of rows to return (0 based, optional)



*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('A7XSLX_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('A7XSLX_PLUGIN_URL', plugin_dir_url(__FILE__));
define('A7XSLX_UPLOAD_DIR', WP_CONTENT_DIR . '/uploads/a7xslx/');
define('A7XSLX_APP_DIR', A7XSLX_PLUGIN_DIR . 'app/');

error_reporting(error_reporting() ^ E_DEPRECATED);

// Includes
require_once A7XSLX_APP_DIR . 'vendor/autoload.php';

// Activation hook
register_activation_hook(__FILE__, 'a7xslx_activate');

function a7xslx_activate() {
    // Create database tables
    a7xslx_create_tables();

    // Create upload directory
    if (!file_exists(A7XSLX_UPLOAD_DIR)) {
        wp_mkdir_p(A7XSLX_UPLOAD_DIR);
    }

    // Flush rewrite rules if needed
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'a7xslx_deactivate');

function a7xslx_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Create database tables
function a7xslx_create_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $table_files = $wpdb->prefix . 'a7xslx_files';

    $sql_files = "CREATE TABLE $table_files (
        id int(11) NOT NULL AUTO_INCREMENT,
        filename varchar(255) NOT NULL,
        filepath varchar(255) NOT NULL,
        uploaded_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_files);
}

// Add admin toolbar button
add_action('admin_bar_menu', 'a7xslx_add_toolbar_button', 100);

function a7xslx_add_toolbar_button($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }

    $wp_admin_bar->add_node(array(
        'id'    => 'a7xslx-app',
        'title' => 'A7XSLX App',
        'href'  => admin_url('admin.php?page=a7xslx-app'),
        'meta'  => array(
            'target' => '_blank'
        )
    ));
}

add_action('init', 'a7xslx_init');

function a7xslx_init() {
    // Add rewrite rule for API
    
    $action = $_GET['action'] ?? 'list';
    if (in_array($action, ['api'])) {
        \A7XSLX\App::run($action);
        die;
    }
}

// Add admin menu page for the app
add_action('admin_menu', 'a7xslx_add_admin_menu');

function a7xslx_add_admin_menu() {
    add_menu_page(
        'A7XSLX Excel Manager',
        'A7XSLX',
        'manage_options',
        'a7xslx-app',
        'a7xslx_app_page',
        'dashicons-media-spreadsheet',
        30
    );
}

function a7xslx_app_page() {
    // Route to appropriate action
    $action = $_GET['action'] ?? 'list';

    // Run the action via facade
    \A7XSLX\App::run($action);
}

// Enqueue scripts and styles if needed
add_action('admin_enqueue_scripts', 'a7xslx_enqueue_scripts');

function a7xslx_enqueue_scripts($hook) {
    if ($hook !== 'toplevel_page_a7xslx-app') {
        return;
    }
    // Enqueue Tabulator
    wp_enqueue_script('tabulator', 'https://unpkg.com/tabulator-tables@6.3.1/dist/js/tabulator.min.js', array('jquery'), '6.3.1', true);
    wp_enqueue_style('tabulator', 'https://unpkg.com/tabulator-tables@6.3.1/dist/css/tabulator.min.css');

    // Localize script for AJAX URLs
    wp_localize_script('tabulator', 'a7xslx_ajax', array(
        'allfiles_url' => admin_url('admin.php?page=a7xslx-app&action=api&endpoint=allfiles'),
        'filedata_url' => admin_url('admin.php?page=a7xslx-app&action=api&endpoint=data'),
    ));
}

