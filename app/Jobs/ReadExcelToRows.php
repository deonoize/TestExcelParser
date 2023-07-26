<?php

namespace App\Jobs;

use File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Reader\IReader;

final class ReadExcelToRows implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $path, public int $chunkSize)
    {
    }

    public function handle(): void
    {
        $path = $this->path;
        $fullPath = Storage::path($path);
        $chunkSize = $this->chunkSize;

        $reader = self::createReader($fullPath);
        if ($reader === null) {
            return;
        }
        $totalRows = self::getTotalRows($reader, $fullPath);

        $fileName = File::name($path);
        self::setTotalRows($fileName, $totalRows);

        $jobs = collect();
        for ($currentRow = 1; $currentRow <= $totalRows; $currentRow += $chunkSize) {
            $jobs->push(
                new ReadChunkExcelToRows(
                    $path,
                    $currentRow,
                    $chunkSize
                )
            );
        }
        $jobs->push(new DeleteFile($path));
        Bus::chain($jobs)->dispatch();
    }

    private static function createReader(string $path): ?IReader
    {
        try {
            return IOFactory::createReaderForFile($path);
        } catch (Exception) {
            return null;
        }
    }

    private static function getTotalRows(IReader $reader, string $path): int
    {
        $info = $reader->listWorksheetInfo($path);
        return (int)$info[0]['totalRows'];
    }

    private static function setTotalRows(string $fileName, int $row): bool
    {
        return Redis::set("parse_excel_total_rows_$fileName", $row);
    }
}
