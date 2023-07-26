<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ReadChunkExcelToRows;
use App\Jobs\ReadExcelToRows;
use App\Models\Row;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReadChunkExcelToRowsTest extends TestCase
{
    use RefreshDatabase;

    public function testReadChunk()
    {

        $path = __DIR__.'/../../Other/Files/rows_import.xlsx';
        $path = Storage::putFileAs('test', new File($path), 'file.xlsx');
        ReadChunkExcelToRows::dispatchSync($path, 1, 6);
        Storage::deleteDirectory('test');

        $this->assertDatabaseCount('rows', 1);

        $row = Row::first();
        $this->assertEquals(1, $row->id);
        $this->assertEquals('Denim', $row->name);
        $this->assertEquals('2020-10-13', $row->date->toDateString());
    }
}
