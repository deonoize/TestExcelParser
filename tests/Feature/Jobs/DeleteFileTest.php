<?php

namespace Tests\Feature\Jobs;

use App\Jobs\DeleteFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteFileTest extends TestCase
{
    public function testDeleteFile()
    {
        Storage::fake();

        Storage::put('file.txt', 'content');
        DeleteFile::dispatchSync('file.txt');

        Storage::disk()->assertMissing('file.txt');
    }
}
