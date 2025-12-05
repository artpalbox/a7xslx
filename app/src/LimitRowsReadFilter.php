<?php

namespace A7XSLX;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class LimitRowsReadFilter implements IReadFilter {
    private $maxRows;

    public function __construct($maxRows = 50) {
        $this->maxRows = $maxRows;
    }

    public function readCell($columnAddress, $row, $worksheetName = ''): bool {
        return $row <= $this->maxRows;
    }
}