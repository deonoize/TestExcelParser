<?php

use App\Http\Controllers\Api\RowController;
use App\Http\Middleware\AuthenticateOnceWithBasicAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('/rows', RowController::class)
     ->only('index');
Route::post('/rows/upload', [RowController::class, 'upload'])
     ->name('rows.upload')
     ->middleware(AuthenticateOnceWithBasicAuth::class);
Route::post('/rows/test', [RowController::class, 'test'])
     ->name('rows.test');
