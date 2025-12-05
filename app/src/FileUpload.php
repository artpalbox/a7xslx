<?php

namespace A7XSLX;

class FileUpload {

    public static function render() {
        global $wpdb;
        $table_files = $wpdb->prefix . 'a7xslx_files';

        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
            $file = $_FILES['excel_file'];

            // Check for errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $message = 'Upload error.';
            } elseif (!in_array($file['type'], [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                'application/vnd.ms-excel.sheet.macroEnabled.12', // .xlsm
                'application/vnd.ms-excel', // .xls
                'text/csv', // .csv
                'application/csv' // .csv alternative
            ])) {
                $message = 'Only Excel (.xlsx, .xlsm, .xls) and CSV (.csv) files are allowed.';
            } else {
                // Generate unique filename
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid() . '.' . $ext;
                $filepath = A7XSLX_UPLOAD_DIR . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Insert to DB
                    $wpdb->insert($table_files, array(
                        'filename' => sanitize_text_field($file['name']),
                        'filepath' => $filepath
                    ));

                    $message = 'File uploaded successfully.';
                } else {
                    $message = 'Failed to save file.';
                }
            }
        }

        ?>
        <div class="wrap">
            <h1>Upload Excel File</h1>

            <?php if ($message): ?>
                <div class="notice notice-<?php echo strpos($message, 'success') !== false ? 'success' : 'error'; ?>">
                    <p><?php echo esc_html($message); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="excel_file">Select Excel/CSV File (.xlsx, .xlsm, .xls, .csv)</label></th>
                        <td><input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xlsm,.xls,.csv" required /></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Upload File" />
                    <a href="?page=a7xslx-app" class="button">Back to List</a>
                </p>
            </form>
        </div>
        <?php
    }
}