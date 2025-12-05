<?php
// A7XSLX Micro Application - API Endpoints

require_once(__DIR__ . '/../../../wp-load.php');

if (!current_user_can('manage_options')) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

\A7XSLX\App::run('api');