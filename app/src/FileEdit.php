<?php

namespace A7XSLX;

class FileEdit {

    public static function render() {
        global $wpdb;
        $table_files = $wpdb->prefix . 'a7xslx_files';

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $file = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_files WHERE id = %d", $id));

        if (!$file) {
            wp_die('File not found.');
        }

        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['update'])) {
                $new_filename = sanitize_text_field($_POST['filename']);
                $wpdb->update($table_files, array('filename' => $new_filename), array('id' => $id));
                $message = 'Filename updated successfully.';
                $file->filename = $new_filename;
            } elseif (isset($_POST['delete'])) {
                // Delete file
                if (file_exists($file->filepath)) {
                    unlink($file->filepath);
                }
                $wpdb->delete($table_files, array('id' => $id));
                echo '<div class="notice notice-success"><p>File deleted successfully.</p></div>';
                echo '<a href="?page=a7xslx-app" class="button">Back to List</a>';
                exit;
            }
        }

        ?>
        <div class="wrap">
            <h1>Edit File: <?php echo esc_html($file->filename); ?></h1>

            <?php if ($message): ?>
                <div class="notice notice-success">
                    <p><?php echo esc_html($message); ?></p>
                </div>
            <?php endif; ?>

            <form method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="filename">Filename</label></th>
                        <td><input type="text" name="filename" id="filename" value="<?php echo esc_attr($file->filename); ?>" required /></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="update" class="button button-primary" value="Update Filename" />
                    <input type="submit" name="delete" class="button" value="Delete File" onclick="return confirm('Are you sure you want to delete this file?')" />
                    <a href="?page=a7xslx-app" class="button">Back to List</a>
                </p>
            </form>
        </div>
        <?php
    }
}