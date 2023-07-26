<?php

namespace App\Http\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class RowsNew extends Component
{
    public array $rows = [];
    protected $listeners = ['echo:rows,RowCreated' => 'newRow'];

    public function newRow($event): void
    {
        $this->rows[] = $event['row'];
    }

    public function render(): View
    {
        return view('livewire.rows-new');
    }
}
