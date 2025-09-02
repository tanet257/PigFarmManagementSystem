<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;

//-------------------route home/admin-------------------------//

Route::get('/',[HomeController::class,'my_home']);

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/admin_index', [AdminController::class, 'admin_index'])->name('admin_index');

//-------------------route batch------------------------------//

Route::get('/add_batch', [AdminController::class, 'add_batch']);

Route::post('/upload_batch', [AdminController::class, 'upload_batch']);

Route::get('/view_batch', [AdminController::class, 'view_batch']);

//------------------- route farm ------------------------------//

Route::get('/add_farm', [AdminController::class, 'add_farm']);

Route::post('/upload_farm', [AdminController::class, 'upload_farm']);

Route::get('/view_farm', [AdminController::class, 'view_farm']);

//------------------- route barn -----------------------------//

Route::get('/add_barn', [AdminController::class, 'add_barn']);

Route::post('/upload_barn', [AdminController::class, 'upload_barn']);

Route::get('/view_barn', [AdminController::class, 'view_barn']);

//------------------- route pen ------------------------------//

Route::get('/add_pen', [AdminController::class, 'add_pen']);

Route::post('/upload_pen', [AdminController::class, 'upload_pen']);

Route::get('/view_pen', [AdminController::class, 'view_pen']);

//------------------- route batch treatment ------------------//

Route::get('/add_batch_treatment', [AdminController::class, 'add_batch_treatment']);

Route::post('/upload_batch_treatment', [AdminController::class, 'upload_batch_treatment']);

Route::get('/view_batch_treatment', [AdminController::class, 'view_batch_treatment']);

//------------------- route cost -----------------------------//

Route::get('/add_cost', [AdminController::class, 'add_cost']);

Route::post('/upload_cost', [AdminController::class, 'upload_cost']);

Route::get('/view_cost', [AdminController::class, 'view_cost']);

//------------------- route pig sell -------------------------//

Route::get('/add_pig_sell', [AdminController::class, 'add_pig_sell']);

Route::post('/upload_pig_sell', [AdminController::class, 'upload_pig_sell']);

Route::get('/view_pig_sell', [AdminController::class, 'view_pig_sell']);

//------------------- route feeding --------------------------//

Route::get('/add_feeding', [AdminController::class, 'add_feeding']);

Route::post('/upload_feeding', [AdminController::class, 'upload_feeding']);

Route::get('/view_feeding', [AdminController::class, 'view_feeding']);

//------------------- route pig death ------------------------//

Route::get('/add_pig_death', [AdminController::class, 'add_pig_death']);

Route::post('/upload_pig_death', [AdminController::class, 'upload_pig_death']);

Route::get('/view_pig_death', [AdminController::class, 'view_pig_death']);





Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
