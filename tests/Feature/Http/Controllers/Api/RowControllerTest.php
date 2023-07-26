<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Jobs\ReadExcelToRows;
use App\Models\Row;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Storage;
use Tests\TestCase;

class RowControllerTest extends TestCase
{
    use RefreshDatabase;

    public static function excelTypesProvider(): array
    {
        return [
            'xlsx' => ['xlsx'],
            'xls'  => ['xls'],
        ];
    }

    public function testIndex()
    {
        $date1 = now();
        $date2 = now()->subDay();

        Row::factory(3)
           ->sequence(
               ['date' => $date1],
               ['date' => $date1],
               ['date' => $date2]
           )
           ->create();

        $this->json('get', route('api.rows.index'))
             ->assertOk()
             ->assertJsonStructure([
                 'data' => [
                     '*' => [
                         '*' => [
                             'id',
                             'name',
                             'date',
                         ],
                     ],
                 ],
             ])
             ->assertJsonCount(2, "data.{$date1->format('Y-m-d')}")
             ->assertJsonCount(1, "data.{$date2->format('Y-m-d')}");
    }

    public function testUploadUnauthorized()
    {
        $this->json('post', route('api.rows.upload'))
             ->assertUnauthorized();
    }

    public function testUploadWithoutFile()
    {
        $user = User::factory()->create(['password' => '123']);

        $this->withBasicAuth($user->email, '123')
             ->json('post', route('api.rows.upload'))
             ->assertJsonValidationErrorFor('file');
    }

    public function testUploadWrongType()
    {
        $user = User::factory()->create(['password' => '123']);
        $file = UploadedFile::fake()->image('random.jpg')->size(1);

        $this->withBasicAuth($user->email, '123')
             ->json('post', route('api.rows.upload'), compact('file'))
             ->assertJsonValidationErrorFor('file');
    }

    /**
     * @dataProvider excelTypesProvider
     */
    public function testUploadLargeSize(string $type)
    {
        $user = User::factory()->create(['password' => '123']);
        $file = UploadedFile::fake()->create("file.$type", 1024 * 50 + 1);

        $this->withBasicAuth($user->email, '123')
             ->json('post', route('api.rows.upload'), compact('file'))
             ->assertJsonValidationErrorFor('file');
    }

    /**
     * @dataProvider excelTypesProvider
     */
    public function testUpload(string $type)
    {
        Storage::fake();
        Queue::fake([
            ReadExcelToRows::class,
        ]);

        $user = User::factory()->create(['password' => '123']);
        $file = UploadedFile::fake()->create("file.$type", 1);

        $this->withBasicAuth($user->email, '123')
             ->json('post', route('api.rows.upload'), compact('file'))
             ->assertOk()
             ->assertJson(['result' => true]);

        Storage::disk()->assertExists("upload/excel/{$file->hashName()}");
        Queue::assertPushed(function (ReadExcelToRows $job) use ($file) {
            return $job->path === 'upload/excel/'.$file->hashName();
        });
    }
}
