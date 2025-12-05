<?php

namespace A7XSLX;

use PhpOffice\PhpSpreadsheet\IOFactory;

class FileView {

    public static function render() {
        global $wpdb;
        $table_files = $wpdb->prefix . 'a7xslx_files';

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $file = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_files WHERE id = %d", $id));

        if (!$file || !file_exists($file->filepath)) {
            wp_die('File not found.');
        }

        ?>
        <div class="wrap">
            <h1>View File: <?php echo esc_html($file->filename); ?></h1>
            <p><a href="?page=a7xslx-app" class="button">Back to List</a></p>

            <p id="sheet-links"></p>

            <progress id="file-view-loading" style="display: none; width: 100%;"></progress>
            <div id="file-view-table"></div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var currentSheet = '<?php echo isset($_GET['sheet']) ? esc_js($_GET['sheet']) : ''; ?>';
            var table = new Tabulator("#file-view-table", {
                layout: "fitDataTable",
                autoColumns: true,
            });

            function loadData(sheet) {
                currentSheet = sheet;
                $('#file-view-loading').show();
                fetch(a7xslx_ajax.filedata_url + '&file_id=<?php echo $id; ?>&limit=50&sheet=' + encodeURIComponent(sheet))
                    .then(response => response.json())
                    .then(response => {
                        table.setData(response.data);
                        // Build sheet links
                        var links = '';
                        for (var i = 0; i < response.availableSheets.length; i++) {
                            if (i > 0) links += ' | ';
                            if (response.availableSheets[i] === response.sheetName) {
                                links += '<strong>' + response.availableSheets[i] + '</strong>';
                            } else {
                                links += '<a href="#" onclick="changeSheet(\'' + response.availableSheets[i] + '\')">' + response.availableSheets[i] + '</a>';
                            }
                        }
                        $('#sheet-links').html(links);
                        $('#file-view-loading').hide();
                    })
                    .catch(error => {
                        console.error('Error loading data:', error);
                        $('#file-view-loading').hide();
                    });
            }

            window.changeSheet = function(sheet) {
                loadData(sheet);
            };

            // Load initial data
            loadData(currentSheet || '');
        });
        </script>
        <?php
    }
}