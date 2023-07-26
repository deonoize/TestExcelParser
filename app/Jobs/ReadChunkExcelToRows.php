<?php

namespace App\Jobs;

use App\Excel\Filters\ChunkReadFilter;
use App\Models\Row;
use Carbon\Carbon;
use File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Storage;

final class ReadChunkExcelToRows implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $path,
        public int $startRow,
        public int $chunkSize,
    ) {
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $fullPath = Storage::path($this->path);
        $startRow = $this->startRow;
        $fileName = File::name($this->path);

        $chunkFilter = new ChunkReadFilter($this->startRow, $this->chunkSize);
        $reader = self::createReader($fullPath, $chunkFilter);

        $spreadsheet = $reader->load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $range = 'A'.$startRow.':'.$sheet->getHighestColumn().$sheet->getHighestRow();
        $data = $sheet->rangeToArray($range);
        foreach ($data as $row) {
            self::validateAndCreateRow(self::prepareRow($row));
            self::setLastRow($fileName, ++$startRow);
        }
    }

    private static function prepareRow(array $row): array
    {
        $row = array_slice($row, 0, 3);
        $row[2] = (isset($row[2]) && is_numeric($row[2])) ?
            Date::excelToDateTimeObject((float)$row[2])->format('d.m.Y') :
            null;

        return $row;
    }

    private static function validateAndCreateRow(array $row): ?Row
    {
        $validator = Validator::make($row, [
            '0' => 'required|integer',
            '1' => 'required|string',
            '2' => 'required|date_format:d.m.Y',
        ]);
        if ($validator->fails()) {
            return null;
        }
        return self::createRow($validator->validated());
    }

    private static function createRow(array $row): Row
    {
        return Row::updateOrCreate(
            [
                'id' => $row[0],
            ],
            [
                'name' => $row[1],
                'date' => Carbon::createFromFormat('d.m.Y', $row[2]),
            ]
        );
    }

    /**
     * @throws Exception
     */
    private static function createReader(string $path, ChunkReadFilter $chunkFilter): IReader
    {
        return IOFactory::createReaderForFile($path)
                        ->setReadDataOnly(true)
                        ->setReadEmptyCells(false)
                        ->setReadFilter($chunkFilter);
    }

    private static function setLastRow(string $fileName, int $row): bool
    {
        return Redis::set("parse_excel_row_$fileName", $row);
    }
}
