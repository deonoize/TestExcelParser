<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RowUploadRequest;
use App\Http\Resources\RowCollection;
use App\Jobs\ReadExcelToRows;
use App\Models\Row;

class RowController extends Controller
{
    public function index()
    {
        return RowCollection::make(
            Row::get()->groupBy(fn(Row $row) => $row->date->format('Y-m-d'))
        );
    }

    public function upload(RowUploadRequest $request)
    {
        $path = $request->file('file')->store('upload/excel');
        ReadExcelToRows::dispatch($path, 1000);
        return response()->json(['result' => true]);
    }
}
