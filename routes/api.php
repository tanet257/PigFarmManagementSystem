<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfitController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//------------------- route API profits ----------------------//
Route::middleware('auth:sanctum')->prefix('profits')->group(function () {
    Route::get('/farm/{farmId}/summary', [ProfitController::class, 'getFarmProfitSummary'])->name('api.profits.farm_summary');
    Route::get('/batch/{batchId}/details', [ProfitController::class, 'getBatchProfitDetails'])->name('api.profits.batch_details');
});

