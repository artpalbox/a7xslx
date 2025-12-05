<?php

namespace A7XSLX;

use PhpOffice\PhpSpreadsheet\IOFactory;

class API {

    public static function handle() {
        header('Content-Type: application/json');

        $endpoint = $_GET['endpoint'] ?? '';

        switch ($endpoint) {
            case 'info':
                self::getInfo();
                break;
            case 'data':
                self::getData();
                break;
            case 'allfiles':
                self::getAllFiles();
                break;
            default:
                echo json_encode(['error' => 'Invalid endpoint']);
                break;
        }
    }

    private static function getInfo() {
        global $wpdb;
        $table_files = $wpdb->prefix . 'a7xslx_files';

        $file_id = intval($_GET['file_id'] ?? 0);
        $file = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_files WHERE id = %d", $file_id));

        if (!$file || !file_exists($file->filepath)) {
            echo json_encode(['error' => 'File not found']);
            return;
        }

        try {
            $ext = strtolower(pathinfo($file->filepath, PATHINFO_EXTENSION));
            if (in_array($ext, ['xlsm'])) {
                $reader = \Box\Spout\Reader\Common\Creator\ReaderEntityFactory::createXLSXReader();
                $reader->open($file->filepath);
            } else {
                $reader = \Box\Spout\Reader\Common\Creator\ReaderFactory::createFromFile($file->filepath);
                $reader->open();
            }

            $sheets = [];
            foreach ($reader->getSheetIterator() as $sheet) {
                $sheetName = $sheet->getName();
                // Count rows by reading all
                $rowCount = 0;
                foreach ($sheet->getRowIterator() as $row) {
                    $rowCount++;
                }
                $sheets[] = [
                    'name' => $sheetName,
                    'rows' => $rowCount
                ];
            }

            $reader->close();
            echo json_encode(['sheets' => $sheets]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error reading file']);
        }
    }

    private static function getData() {
        global $wpdb;
        $table_files = $wpdb->prefix . 'a7xslx_files';

        $file_id = intval($_GET['file_id'] ?? 0);
        $sheet_name = $_GET['sheet'] ?? '';
        $columns = isset($_GET['columns']) ? explode(',', $_GET['columns']) : null;
        $limit = intval($_GET['limit'] ?? 0);
        $offset = intval($_GET['offset'] ?? 0);

        $file = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_files WHERE id = %d", $file_id));

        if (!$file || !file_exists($file->filepath)) {
            echo json_encode(['error' => 'File not found']);
            return;
        }

        try {            
            // Use Spout for all, with custom reader for xlsm
            $ext = strtolower(pathinfo($file->filepath, PATHINFO_EXTENSION));
            if ($ext === 'xlsm') {
                $reader = \Box\Spout\Reader\Common\Creator\ReaderEntityFactory::createXLSXReader();
                $reader->open($file->filepath);
            } else {
                $reader = \Box\Spout\Reader\Common\Creator\ReaderFactory::createFromFile($file->filepath);
                $reader->open();
            }

            $availableSheets = [];
            foreach ($reader->getSheetIterator() as $sheet) {
                $availableSheets[] = $sheet->getName();
            }

            if (!$sheet_name) {
                $sheet_name = $availableSheets[0] ?? '';
            }

            $data = [];
            $rowCount = 0;
            $startRow = $offset + 1;
            $maxRows = $limit ?: PHP_INT_MAX;

            foreach ($reader->getSheetIterator() as $sheet) {
                if ($sheet->getName() !== $sheet_name) {
                    continue;
                }

                foreach ($sheet->getRowIterator() as $row) {
                    $rowCount++;
                    if ($rowCount < $startRow) {
                        continue;
                    }
                    if (count($data) >= $maxRows) {
                        break 2;
                    }

                    $cells = $row->getCells();
                    $rowData = [];

                    if ($columns) {
                        foreach ($columns as $col) {
                            $colIndex = self::getColumnIndex($col);
                            $rowData[$col] = isset($cells[$colIndex]) ? $cells[$colIndex]->getValue() : '';
                        }
                    } else {
                        foreach ($cells as $index => $cell) {
                            $colLetter = self::getColumnLetter($index + 1);
                            $rowData[$colLetter] = $cell->getValue();
                        }
                    }

                    $data[] = $rowData;
                }
            }

            $reader->close();
            echo json_encode([
                'sheetName' => $sheet_name,
                'data' => $data,
                'availableSheets' => $availableSheets
            ]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Error reading file']);
        }
    }
    private static function getAllFiles() {
        global $wpdb;
        $table_files = $wpdb->prefix . 'a7xslx_files';

        $files = $wpdb->get_results("SELECT id, filename, uploaded_at FROM $table_files ORDER BY uploaded_at DESC");
        echo json_encode(['data' => $files]);
    }

    private static function getColumnIndex($letter) {
        $letter = strtoupper($letter);
        $index = 0;
        for ($i = 0; $i < strlen($letter); $i++) {
            $index = $index * 26 + (ord($letter[$i]) - ord('A') + 1);
        }
        return $index - 1; // 0-based
    }

    private static function getColumnLetter($index) {
        $letter = '';
        while ($index >= 0) {
            $letter = chr($index % 26 + ord('A')) . $letter;
            $index = intval($index / 26) - 1;
        }
        return $letter;
    }
}