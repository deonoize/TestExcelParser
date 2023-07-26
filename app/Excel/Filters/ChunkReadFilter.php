<?php

namespace App\Excel\Filters;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

final class ChunkReadFilter implements IReadFilter
{
    private int $startRow;
    private int $endRow;

    public function __construct(int $startRow, int $chunkSize)
    {
        $this->startRow = $startRow;
        $this->endRow = $startRow + $chunkSize;
    }

    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        return $row >= $this->startRow && $row < $this->endRow;
    }
}
