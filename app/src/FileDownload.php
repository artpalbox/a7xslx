<?php

namespace A7XSLX;

class FileDownload {

    public static function handle() {
        global $wpdb;
        $table_files = $wpdb->prefix . 'a7xslx_files';

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $file = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_files WHERE id = %d", $id));

        if (!$file || !file_exists($file->filepath)) {
            wp_die('File not found.');
        }

        // Force download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . basename($file->filename) . '"');
        header('Content-Length: ' . filesize($file->filepath));
        readfile($file->filepath);
        exit;
    }
}