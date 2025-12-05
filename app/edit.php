<?php
// A7XSLX Micro Application - Edit File

require_once(__DIR__ . '/../../../wp-load.php');

if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

require_once __DIR__ . '/vendor/autoload.php';

\A7XSLX\App::run('edit');