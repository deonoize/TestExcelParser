<?php

namespace App\Console\Commands;

use App\Models\Row;
use Illuminate\Console\Command;

class CreateRow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-row';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для теста отправки события создания Row';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Row::factory()->create();
    }
}
