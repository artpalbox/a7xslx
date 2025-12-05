<?php
// A7XSLX Micro Application - File List

// Include WordPress for DB access
require_once(__DIR__ . '/../../../wp-load.php');

// Check permissions
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

require_once __DIR__ . '/vendor/autoload.php';

\A7XSLX\App::run('list');