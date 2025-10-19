<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StoreHouseController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\PigEntryController;
use App\Http\Controllers\DairyController;
use App\Http\Controllers\BatchPenAllocationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PigSaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserManagementController;

//------------------- route home/admin -------------------------//
Route::get('/', [HomeController::class, 'my_home'])->name('home.my_home');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/admin_index', [AdminController::class, 'admin_index'])->name('admin.index');

//------------------- route registration pending ---------------//
Route::get('/registration_pending', function () {
    return view('auth.registration_pending');
})->name('registration.pending');

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

//------------------- route feeding --------------------------//
Route::get('/add_feed', [AdminController::class, 'add_feed'])->name('feeding.add');
Route::post('/upload_feed', [AdminController::class, 'upload_feed'])->name('feeding.upload');
Route::get('/view_feed', [AdminController::class, 'view_feed'])->name('feeding.view');

//------------------- route pig death ------------------------//
Route::get('/add_pig_death', [AdminController::class, 'add_pig_death'])->name('pig_death.add');
Route::post('/upload_pig_death', [AdminController::class, 'upload_pig_death'])->name('pig_death.upload');
Route::get('/view_pig_death', [AdminController::class, 'view_pig_death'])->name('pig_death.view');

//------------------- route pig entry record -----------------//
Route::get('/pig_entry_record', [PigEntryController::class, 'pig_entry_record'])->name('pig_entry_records.record');
Route::post('/upload_pig_entry_record', [PigEntryController::class, 'upload_pig_entry_record'])->name('pig_entry_records.upload');
//------------------- route pig entry helper -----------------//
Route::get('/get-batches/{farmId}', [PigEntryController::class, 'getBatchesByFarm']);
Route::get('/get-barns/{farmId}', [PigEntryController::class, 'getBarnsByFarm']);
Route::get('/get-available-barns/{farmId}', [PigEntryController::class, 'getAvailableBarnsByFarm']);

//------------------- route crud pig_entry_record -----------------------//
Route::prefix('pigentryrecord')->group(function () {
    Route::get('/', [PigEntryController::class, 'indexPigEntryRecord'])->name('pig_entry_records.index');
    Route::post('/create', [PigEntryController::class, 'createPigentryrecord'])->name('pig_entry_records.create');
    Route::get('/{id}/edit', [PigEntryController::class, 'editPigentryrecord'])->name('pig_entry_records.edit');
    Route::put('/{id}', [PigEntryController::class, 'updatePigentryrecord'])->name('pig_entry_records.update');
    Route::delete('/{id}', [PigEntryController::class, 'deletePigentryrecord'])->name('pig_entry_records.delete');
    //------------------- route export batch ---------------------//
    Route::get('/export/csv', [PigEntryController::class, 'exportCsv'])->name('pig_entry_records.export.csv');
    Route::get('/export/pdf', [PigEntryController::class, 'exportPdf'])->name('pig_entry_records.export.pdf');
});

//------------------- route r batch pen allocation -----------------------//
Route::prefix('batch_pen_allocations')->group(function () {
    Route::get('/', [BatchPenAllocationController::class, 'index'])->name('batch_pen_allocations.index');
    //------------------- route export batch ---------------------//
    Route::get('/export/csv', [BatchPenAllocationController::class, 'exportCsv'])->name('batch_pen_allocations.export.csv');
    Route::get('/export/pdf', [BatchPenAllocationController::class, 'exportPdf'])->name('batch_pen_allocations.export.pdf');
});


//------------------- route dairy record ---------------------//

Route::get('/viewDairy', [DairyController::class, 'viewDairy'])->name('dairy_records.record');
Route::post('/uploadDairy', [DairyController::class, 'uploadDairy'])->name('dairy_records.upload');

//------------------- route crud dairy_record -----------------------//

Route::prefix('dairy_record')->group(function () {
    Route::get('/', [DairyController::class, 'indexDairy'])->name('dairy_records.index');
    // Edit Feed
    Route::put('/{dairyId}/{useId}/{type}/edit-feed', [DairyController::class, 'updateFeed'])->name('dairy_records.update_feed');
    // Edit Medicine
    Route::put('/{dairyId}/{btId}/{type}/edit-medicine', [DairyController::class, 'updateMedicine'])->name('dairy_records.update_medicine');
    // Edit Pig Death
    Route::put('/pig-deaths/{id}/{type}/edit', [DairyController::class, 'updatePigDeath'])->name('pig_deaths.update');

    Route::delete('/dairy_storehouse_uses/{id}', [DairyController::class, 'destroyFeed'])->name('dairy_storehouse_uses.destroy');
    Route::delete('/batch_treatments/{id}', [DairyController::class, 'destroyMedicine'])->name('batch_treatments.destroy');
    Route::delete('/pig_deaths/{id}', [DairyController::class, 'destroyPigDeath'])->name('pig_deaths.destroy');

    //------------------- route export batch ---------------------//
    Route::get('/export/csv', [DairyController::class, 'exportCsv'])->name('dairy_records.export.csv');
    Route::get('/export/pdf', [DairyController::class, 'exportPdf'])->name('dairy_records.export.pdf');
});

//------------------- route storehouse record ---------------------//
Route::get('/store_house_record', [StoreHouseController::class, 'store_house_record'])->name('store_house_record.recordview');
Route::post('/upload_store_house_record', [StoreHouseController::class, 'upload_store_house_record'])->name('store_house_record.upload');

//------------------- route crud storehouse -----------------------//
Route::prefix('storehouses')->group(function () {
    Route::get('/', [StoreHouseController::class, 'indexStorehouse'])->name('storehouses.index');
    Route::post('/create', [StoreHouseController::class, 'createItem'])->name('storehouses.create');
    Route::get('/{id}/edit', [StoreHouseController::class, 'editStorehouse'])->name('storehouses.edit');
    Route::put('/{id}', [StoreHouseController::class, 'updateStorehouse'])->name('storehouses.update');
    Route::delete('/{id}', [StoreHouseController::class, 'deleteStorehouse'])->name('storehouses.delete');
    //------------------- route export batch ---------------------//
    Route::get('/export/csv', [StoreHouseController::class, 'exportCsv'])->name('storehouses.export.csv');
    Route::get('/export/pdf', [StoreHouseController::class, 'exportPdf'])->name('storehouses.export.pdf');
});

//------------------- route r inventory movement -----------------------//
Route::prefix('inventory_movements')->group(function () {
    Route::get('/', [InventoryMovementController::class, 'index'])->name('inventory_movements.index');
    //------------------- route export batch ---------------------//
    Route::get('/export/csv', [InventoryMovementController::class, 'exportCsv'])->name('inventory_movements.export.csv');
    Route::get('/export/pdf', [InventoryMovementController::class, 'exportPdf'])->name('inventory_movements.export.pdf');
});

//------------------- route crud batch -----------------------//
Route::prefix('batches')->group(function () {
    Route::get('/', [BatchController::class, 'indexBatch'])->name('batches.index');
    Route::post('/create', [BatchController::class, 'createBatch'])->name('batches.create');
    Route::get('/{id}/edit', [BatchController::class, 'editBatch'])->name('batches.edit');
    Route::put('/{id}', [BatchController::class, 'updateBatch'])->name('batches.update');
    Route::delete('/{id}', [BatchController::class, 'deleteBatch'])->name('batches.delete');
    //------------------- route export batch ---------------------//
    Route::get('/export/csv', [BatchController::class, 'exportCsv'])->name('batches.export.csv');
    Route::get('/export/pdf', [BatchController::class, 'exportPdf'])->name('batches.export.pdf');
});


Route::get('/dash', [DashboardController::class, 'dashboard'])->name('dashboard.dashboard');


//------------------- route pig sales (New System) -----------//
Route::prefix('pig_sales')->middleware(['auth'])->group(function () {
    Route::get('/', [PigSaleController::class, 'index'])->name('pig_sales.index');
    Route::get('/create', [PigSaleController::class, 'create'])->name('pig_sales.create');
    Route::post('/', [PigSaleController::class, 'store'])->name('pig_sales.store');
    Route::get('/{id}', [PigSaleController::class, 'show'])->name('pig_sales.show');
    Route::get('/{id}/edit', [PigSaleController::class, 'edit'])->name('pig_sales.edit');
    Route::put('/{id}', [PigSaleController::class, 'update'])->name('pig_sales.update');
    Route::delete('/{id}', [PigSaleController::class, 'destroy'])->name('pig_sales.cancel');
    Route::post('/{id}/approve', [PigSaleController::class, 'approve'])->name('pig_sales.approve')->middleware('permission:approve_sales');
    Route::post('/{id}/reject', [PigSaleController::class, 'reject'])->name('pig_sales.reject')->middleware('permission:approve_sales');
    Route::post('/{id}/upload_receipt', [PigSaleController::class, 'uploadReceipt'])->name('pig_sales.upload_receipt');

    //------------------- route AJAX helpers ---------------------//
    Route::get('/pens-by-farm/{farmId}', [PigSaleController::class, 'getPensByFarm'])->name('pig_sales.pens_by_farm');
    Route::get('/pens-by-batch/{batchId}', [PigSaleController::class, 'getPensByBatch'])->name('pig_sales.pens_by_batch');
    Route::get('/batches-by-farm/{farmId}', [PigSaleController::class, 'getBatchesByFarm'])->name('pig_sales.batches_by_farm');
    Route::get('/barns-by-farm/{farmId}', [PigSaleController::class, 'getBarnsByFarm'])->name('pig_sales.barns_by_farm');
    Route::get('/pens-by-barn/{barnId}', [PigSaleController::class, 'getPensByBarn'])->name('pig_sales.pens_by_barn');

    //------------------- route export batch ---------------------//
    Route::get('/export/csv', [PigSaleController::class, 'exportCsv'])->name('pig_sales.export.csv');
    Route::get('/export/pdf', [PigSaleController::class, 'exportPdf'])->name('pig_sales.export.pdf');
});

//------------------- route notifications --------------------//
Route::prefix('notifications')->middleware(['auth'])->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/recent', [NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::get('/unread_count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread_count');
    Route::post('/{id}/mark_as_read', [NotificationController::class, 'markAsRead'])->name('notifications.mark_read');
    Route::post('/mark_all_read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark_all_as_read');
    Route::post('/clear_read', [NotificationController::class, 'clearRead'])->name('notifications.clear_read');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

//------------------- route user management ------------------//
Route::prefix('user_management')->middleware(['auth', 'permission:manage_users'])->group(function () {
    Route::get('/', [UserManagementController::class, 'index'])->name('user_management.index');
    Route::get('/pending', [UserManagementController::class, 'pending'])->name('user_management.pending');
    Route::post('/{id}/approve', [UserManagementController::class, 'approve'])->name('user_management.approve');
    Route::post('/{id}/reject', [UserManagementController::class, 'reject'])->name('user_management.reject');
    Route::post('/{id}/assign_role', [UserManagementController::class, 'assignRole'])->name('user_management.assign_role');
    Route::post('/{id}/update_roles', [UserManagementController::class, 'updateRoles'])->name('user_management.update_roles');
    Route::delete('/{id}', [UserManagementController::class, 'destroy'])->name('user_management.destroy');
});

//------------------- route user approval --------------------//
use App\Http\Controllers\UserApprovalController;

Route::prefix('admin/user_approval')->middleware(['auth', 'permission:manage_users'])->group(function () {
    Route::get('/', [UserApprovalController::class, 'index'])->name('admin.user_approval.index');
    Route::post('/{user}/approve', [UserApprovalController::class, 'approve'])->name('admin.user_approval.approve');
    Route::post('/{user}/reject', [UserApprovalController::class, 'reject'])->name('admin.user_approval.reject');
    Route::post('/{user}/update_roles', [UserApprovalController::class, 'updateRoles'])->name('admin.user_approval.update_roles');
    Route::post('/{user}/reopen', [UserApprovalController::class, 'reopen'])->name('admin.user_approval.reopen');
    Route::post('/{user}/suspend', [UserApprovalController::class, 'suspend'])->name('admin.user_approval.suspend');
});

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
