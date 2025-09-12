<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;

//------------------- route home/admin -------------------------//
Route::get('/', [HomeController::class, 'my_home'])->name('home.my_home');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/admin_index', [AdminController::class, 'admin_index'])->name('admin.index');

//------------------- route batch ------------------------------//
Route::get('/add_batch', [AdminController::class, 'add_batch'])->name('batch.add');
Route::post('/upload_batch', [AdminController::class, 'upload_batch'])->name('batch.upload');
Route::get('/view_batch', [AdminController::class, 'view_batch'])->name('batch.view');

//------------------- route farm ------------------------------//
Route::get('/add_farm', [AdminController::class, 'add_farm'])->name('farm.add');
Route::post('/upload_farm', [AdminController::class, 'upload_farm'])->name('farm.upload');
Route::get('/view_farm', [AdminController::class, 'view_farm'])->name('farm.view');

//------------------- route barn -----------------------------//
Route::get('/add_barn', [AdminController::class, 'add_barn'])->name('barn.add');
Route::post('/upload_barn', [AdminController::class, 'upload_barn'])->name('barn.upload');
Route::get('/view_barn', [AdminController::class, 'view_barn'])->name('barn.view');

//------------------- route pen ------------------------------//
Route::get('/add_pen', [AdminController::class, 'add_pen'])->name('pen.add');
Route::post('/upload_pen', [AdminController::class, 'upload_pen'])->name('pen.upload');
Route::get('/view_pen', [AdminController::class, 'view_pen'])->name('pen.view');

//------------------- route batch treatment ------------------//
Route::get('/add_batch_treatment', [AdminController::class, 'add_batch_treatment'])->name('batch_treatment.add');
Route::post('/upload_batch_treatment', [AdminController::class, 'upload_batch_treatment'])->name('batch_treatment.upload');
Route::get('/view_batch_treatment', [AdminController::class, 'view_batch_treatment'])->name('batch_treatment.view');

//------------------- route cost -----------------------------//
Route::get('/add_cost', [AdminController::class, 'add_cost'])->name('cost.add');
Route::post('/upload_cost', [AdminController::class, 'upload_cost'])->name('cost.upload');
Route::get('/view_cost', [AdminController::class, 'view_cost'])->name('cost.view');

//------------------- route pig sell -------------------------//
Route::get('/add_pig_sell_record', [AdminController::class, 'add_pig_sell'])->name('pig_sell.add');
Route::post('/upload_pig_sell_record', [AdminController::class, 'upload_pig_sell'])->name('pig_sell.upload');
Route::get('/view_pig_sell_record', [AdminController::class, 'view_pig_sell'])->name('pig_sell.view');

//------------------- route feeding --------------------------//
Route::get('/add_feed', [AdminController::class, 'add_feed'])->name('feeding.add');
Route::post('/upload_feed', [AdminController::class, 'upload_feed'])->name('feeding.upload');
Route::get('/view_feed', [AdminController::class, 'view_feed'])->name('feeding.view');

//------------------- route pig death ------------------------//
Route::get('/add_pig_death', [AdminController::class, 'add_pig_death'])->name('pig_death.add');
Route::post('/upload_pig_death', [AdminController::class, 'upload_pig_death'])->name('pig_death.upload');
Route::get('/view_pig_death', [AdminController::class, 'view_pig_death'])->name('pig_death.view');

//------------------- route pig entry record -----------------//
Route::get('/pig_entry_record', [AdminController::class, 'pig_entry_record'])->name('pig_entry_record.add');
Route::post('/upload_pig_entry_record', [AdminController::class, 'upload_pig_entry_record'])->name('pig_entry_record.upload');
Route::get('/view_pig_entry_record', [AdminController::class, 'view_pig_entry_record'])->name('pig_entry_record.view');

//------------------- route crud batch -----------------------//
Route::get('/batches', [AdminController::class, 'indexBatch'])->name('batches.index');
Route::get('/batches/{id}/edit', [AdminController::class, 'editBatch'])->name('batches.edit');
Route::put('/batches/{id}', [AdminController::class, 'updateBatch'])->name('batches.update');
Route::delete('/batches/{id}', [AdminController::class, 'deleteBatch'])->name('batches.delete');

//------------------- route export batch ---------------------//
Route::get('/batches/export/csv', [AdminController::class, 'exportCsv'])->name('batches.export.csv');
Route::get('/batches/export/pdf', [AdminController::class, 'exportPdf'])->name('batches.export.pdf');

//------------------- route dashboard ------------------------//
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
