<?php

namespace A7XSLX;

class FileList {

    public static function render() {
        // Handle delete action via AJAX or redirect
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            global $wpdb;
            $table_files = $wpdb->prefix . 'a7xslx_files';
            $id = intval($_GET['id']);
            $file = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_files WHERE id = %d", $id));
            if ($file) {
                if (file_exists($file->filepath)) {
                    unlink($file->filepath);
                }
                $wpdb->delete($table_files, array('id' => $id));
                echo '<div class="notice notice-success"><p>File deleted successfully.</p></div>';
            }
        }

        ?>
        <div class="wrap">
            <h1>A7XSLX Excel File Manager</h1>

            <a href="?page=a7xslx-app&action=upload" class="button button-primary">Upload New File</a>

            <progress id="file-list-loading" style="display: none; width: 100%;"></progress>
            <div id="file-list-table"></div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var table = new Tabulator("#file-list-table", {
                ajaxURL: a7xslx_ajax.allfiles_url,
                layout: "fitColumns",
                ajaxRequesting: function(url, params) {
                    $('#file-list-loading').show();
                },
                ajaxResponse: function(url, params, response) {
                    $('#file-list-loading').hide();
                },
                columns: [
                    {title: "ID", field: "id", width: 50},
                    {title: "Filename", field: "filename"},
                    {title: "Uploaded At", field: "uploaded_at"},
                    {title: "Actions", field: "id", formatter: function(cell, formatterParams, onRendered) {
                        var id = cell.getValue();
                        return '<a href="?page=a7xslx-app&action=view&id=' + id + '" class="button">View</a> ' +
                               '<a href="?page=a7xslx-app&action=edit&id=' + id + '" class="button">Edit</a> ' +
                               '<a href="?page=a7xslx-app&action=download&id=' + id + '" class="button">Download</a> ' +
                               '<a href="?page=a7xslx-app&action=delete&id=' + id + '" class="button" onclick="return confirm(\'Are you sure?\')">Delete</a>';
                    }}
                ],
            });
        });
        </script>
        <?php
    }
}