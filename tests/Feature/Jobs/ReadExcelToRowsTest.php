<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ReadChunkExcelToRows;
use App\Jobs\ReadExcelToRows;
use App\Jobs\DeleteFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReadExcelToRowsTest extends TestCase
{
    public static function testReadExcel()
    {
        Queue::fake([
            ReadChunkExcelToRows::class,
            DeleteFile::class,
        ]);

        $path = __DIR__.'/../../Other/Files/rows_import.xlsx';
        $path = Storage::putFileAs('test', new File($path), 'file.xlsx');
        ReadExcelToRows::dispatchSync($path, 3);
        Storage::deleteDirectory('test');

        Queue::assertPushedWithChain(
            ReadChunkExcelToRows::class,
            [
                ReadChunkExcelToRows::class,
                DeleteFile::class,
            ]
        );
    }

    public static function testReadNotExcel()
    {
        Queue::fake([
            ReadChunkExcelToRows::class,
            DeleteFile::class,
        ]);

        $path = __DIR__.'/../../Other/Files/rows_webp_import.xlsx';
        $path = Storage::putFileAs('test', new File($path), 'file.xlsx');
        ReadExcelToRows::dispatchSync($path, 3);
        Storage::deleteDirectory('test');

        Queue::assertNothingPushed();
    }
}
