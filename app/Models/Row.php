<?php

namespace App\Models;

use App\Events\RowCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Row extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'date',
    ];
    protected $casts = [
        'date' => 'date:Y-m-d',
    ];
    protected $dispatchesEvents = [
        'created' => RowCreated::class
    ];
}
