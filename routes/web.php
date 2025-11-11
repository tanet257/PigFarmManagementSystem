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
use App\Http\Controllers\PigSaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\PaymentApprovalController;
use App\Http\Controllers\CostPaymentApprovalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfitController;
use App\Http\Controllers\BatchEntryController;
use App\Http\Controllers\BatchTreatmentController;
use App\Http\Controllers\DailyTreatmentLogController;
use App\Http\Controllers\TreatmentController;

//------------------- route home/admin -------------------------//
// Dashboard is now the main home page
Route::get('/', [ProfitController::class, 'index'])->middleware(['auth', 'prevent.cache'])->name('dashboard');
Route::get('/home', [HomeController::class, 'index'])->middleware(['prevent.cache'])->name('home.legacy');

// Route สำหรับตรวจสอบ session status
Route::get('/check-session', function () {
    return response()->json(['authenticated' => auth()->check()]);
});

//------------------- ส่วนที่ต้องเข้าสู่ระบบ (Protected Routes) -----//
Route::middleware(['auth', 'prevent.cache'])->group(function () {
    Route::get('/admin_index', [AdminController::class, 'admin_index'])->name('admin.index');

    //------------------- route batch ------------------------------//
    Route::get('/add_batch', [AdminController::class, 'add_batch'])->name('batch.add');
    Route::post('/upload_batch', [AdminController::class, 'upload_batch'])->name('batch.upload');
    Route::get('/view_batch', [AdminController::class, 'view_batch'])->name('batch.view');

    //------------------- NEW: route batch entry (combined) --------//
    Route::prefix('batch')->group(function () {
        Route::get('/create_entry', [BatchEntryController::class, 'createWithEntry'])->name('batch_entry.create');
        Route::post('/store_entry', [BatchEntryController::class, 'storeWithEntry'])->name('batch_entry.store');
        Route::get('/', [BatchEntryController::class, 'index'])->name('batch.index');
        Route::get('/archived', [BatchEntryController::class, 'archived'])->name('batch.archived');
        Route::get('/{batch}', [BatchEntryController::class, 'show'])->name('batch.show');
        Route::get('/{batch}/edit_entry', [BatchEntryController::class, 'editWithEntry'])->name('batch_entry.edit');
        Route::post('/{id}/payment', [BatchEntryController::class, 'update_payment'])->name('batch.update_payment');
        Route::post('/{id}/update-status', [BatchEntryController::class, 'updateStatus'])->name('batch.update_status');
        Route::delete('/{id}', [BatchEntryController::class, 'destroy'])->name('batch.destroy');
        Route::patch('/{id}/restore', [BatchEntryController::class, 'restore'])->name('batch.restore');
        Route::get('/export/csv', [BatchEntryController::class, 'exportCsv'])->name('batch.export.csv');
    });

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
    Route::get('/get-barn-capacity/{farmId}', [PigEntryController::class, 'getBarnAvailableCapacity']);
    Route::get('/get-available-barns/{farmId}', [PigEntryController::class, 'getAvailableBarnsByFarm']);

    //------------------- route crud pig_entry_record -----------------------//
    Route::prefix('pigentryrecord')->group(function () {
        Route::get('/', [PigEntryController::class, 'indexPigEntryRecord'])->name('pig_entry_records.index');
        Route::post('/create', [PigEntryController::class, 'createPigentryrecord'])->name('pig_entry_records.create');
        Route::get('/{id}/edit', [PigEntryController::class, 'editPigentryrecord'])->name('pig_entry_records.edit');
        Route::put('/{id}', [PigEntryController::class, 'updatePigentryrecord'])->name('pig_entry_records.update');
        Route::post('/{id}/payment', [PigEntryController::class, 'update_payment'])->name('pig_entry_records.update_payment');
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
    Route::prefix('dairy_records')->group(function () {
        Route::get('/', [DairyController::class, 'indexDairy'])->name('dairy_records.index');
        Route::post('/create', [DairyController::class, 'createDairy'])->name('dairy_records.create');
        Route::get('/{id}/edit', [DairyController::class, 'editDairy'])->name('dairy_records.edit');
        Route::put('/{id}', [DairyController::class, 'updateDairy'])->name('dairy_records.update');
        Route::delete('/{id}', [DairyController::class, 'deleteDairy'])->name('dairy_records.delete');
    });

    //------------------- route batch treatment (ยา/วัคซีนในการบันทึกประจำวัน) ------//
    Route::prefix('batch-treatments')->group(function () {
        Route::post('/dairy-records/{dairy_record}', [BatchTreatmentController::class, 'store'])->name('batch_treatment.store');
        Route::patch('/{batch_treatment}', [BatchTreatmentController::class, 'update'])->name('batch_treatment.update');
        Route::patch('/{batch_treatment}/status', [BatchTreatmentController::class, 'updateStatus'])->name('batch_treatment.updateStatus');
        Route::post('/{batch_treatment}/start', [BatchTreatmentController::class, 'start'])->name('batch_treatment.start');
        Route::post('/{batch_treatment}/complete', [BatchTreatmentController::class, 'complete'])->name('batch_treatment.complete');
        Route::post('/{batch_treatment}/stop', [BatchTreatmentController::class, 'stop'])->name('batch_treatment.stop');
        Route::delete('/{batch_treatment}', [BatchTreatmentController::class, 'destroy'])->name('batch_treatment.destroy');
        Route::get('/summary/{batch}', [BatchTreatmentController::class, 'summary'])->name('batch_treatment.summary');
        // Daily logs endpoints
        Route::get('/{batchTreatment}/daily-logs', [DailyTreatmentLogController::class, 'getByTreatment'])->name('batch_treatment.daily_logs');
        Route::get('/{batchTreatment}/daily-logs/summary', [DailyTreatmentLogController::class, 'getSummary'])->name('batch_treatment.daily_logs.summary');
    });

    //------------------- route daily treatment logs -------------------------//
    Route::prefix('daily-treatment-logs')->group(function () {
        Route::post('/', [DailyTreatmentLogController::class, 'store'])->name('daily_treatment_logs.store');
        Route::patch('/{dailyTreatmentLog}', [DailyTreatmentLogController::class, 'update'])->name('daily_treatment_logs.update');
        Route::delete('/{dailyTreatmentLog}', [DailyTreatmentLogController::class, 'destroy'])->name('daily_treatment_logs.destroy');
    });

    //------------------- route treatments ---------------------//
    Route::prefix('treatments')->group(function () {
        Route::get('/', [TreatmentController::class, 'index'])->name('treatments.index');
        Route::get('/export/csv', [TreatmentController::class, 'exportCsv'])->name('treatments.export.csv');
        Route::get('/{id}', [TreatmentController::class, 'show'])->name('treatments.show');
        Route::put('/{id}', [TreatmentController::class, 'update'])->name('treatments.update');
        Route::delete('/{id}', [TreatmentController::class, 'destroy'])->name('treatments.destroy');
    });

    //------------------- route export batch ---------------------//
    Route::get('/export/csv', [DairyController::class, 'exportCsv'])->name('dairy_records.export.csv');
    Route::get('/export/pdf', [DairyController::class, 'exportPdf'])->name('dairy_records.export.pdf');

    //------------------- เพิ่ม route สำหรับ update feed/medicine/pigDeath ----------------//
    Route::put('/{dairyId}/{useId}/update-feed/{type}', [DairyController::class, 'updateFeed'])->name('dairy_records.update_feed');
    Route::put('/{dairyId}/{btId}/update-medicine/{type}', [DairyController::class, 'updateMedicine'])->name('dairy_records.update_medicine');
    Route::put('/pig-death/{id}', [DairyController::class, 'updatePigDeath'])->name('dairy_records.update_pigdeath');


    //------------------- route storehouse -----------------------//
    Route::get('/viewStoreHouseRecord', [StoreHouseController::class, 'viewStoreHouseRecord'])->name('storehouse_records.record');
    Route::post('/uploadStoreHouseRecord', [StoreHouseController::class, 'uploadStoreHouseRecord'])->name('storehouse_records.upload');

    Route::prefix('storehouse_records')->group(function () {
        Route::get('/', [StoreHouseController::class, 'indexStoreHouse'])->name('storehouse_records.index');
        Route::post('/create', [StoreHouseController::class, 'createStoreHouse'])->name('storehouse_records.create');
        Route::get('/{id}/edit', [StoreHouseController::class, 'editStoreHouse'])->name('storehouse_records.edit');
        Route::put('/{id}', [StoreHouseController::class, 'updateStoreHouse'])->name('storehouse_records.update');
        Route::delete('/{id}', [StoreHouseController::class, 'deleteStoreHouse'])->name('storehouse_records.delete');
        //------------------- route export batch ---------------------//
        Route::get('/export/csv', [StoreHouseController::class, 'exportCsv'])->name('storehouse_records.export.csv');
        Route::get('/export/pdf', [StoreHouseController::class, 'exportPdf'])->name('storehouse_records.export.pdf');
    });

    //------------------- route r inventory movement -----------------------//
    Route::prefix('inventory_movements')->group(function () {
        Route::get('/', [InventoryMovementController::class, 'index'])->name('inventory_movements.index');
        //------------------- route export batch ---------------------//
        Route::get('/export/csv', [InventoryMovementController::class, 'exportCsv'])->name('inventory_movements.export.csv');
        Route::get('/export/pdf', [InventoryMovementController::class, 'exportPdf'])->name('inventory_movements.export.pdf');
    });


}); // End of auth middleware group

//------------------- route registration pending ---------------//
Route::get('/registration_pending', function () {
    return view('auth.registration_pending');
})->name('registration.pending');
//------------------- route registration pending ---------------//
Route::get('/registration_pending', function () {
    return view('auth.registration_pending');
})->name('registration.pending');

//------------------- route pig sales (New System) -----------//
Route::prefix('pig_sales')->middleware(['auth', 'prevent.cache'])->group(function () {
    Route::get('/', [PigSaleController::class, 'index'])->name('pig_sales.index');
    Route::get('/create', [PigSaleController::class, 'create'])->name('pig_sales.create');
    Route::post('/', [PigSaleController::class, 'store'])->name('pig_sales.store');
    Route::get('/{id}', [PigSaleController::class, 'show'])->name('pig_sales.show');
    Route::get('/{id}/edit', [PigSaleController::class, 'edit'])->name('pig_sales.edit');
    Route::put('/{id}', [PigSaleController::class, 'update'])->name('pig_sales.update');
    Route::delete('/{id}', [PigSaleController::class, 'destroy'])->name('pig_sales.cancel');
    Route::patch('/{id}/confirm-cancel', [PigSaleController::class, 'confirmCancel'])->name('pig_sales.confirm_cancel');
    Route::post('/{id}/upload_receipt', [PigSaleController::class, 'uploadReceipt'])->name('pig_sales.upload_receipt');

    //------------------- route AJAX helpers ---------------------//
    Route::get('/pens-by-farm/{farmId}', [PigSaleController::class, 'getPensByFarm'])->name('pig_sales.pens_by_farm');
    Route::get('/pens-by-batch/{batchId}', [PigSaleController::class, 'getPensByBatch'])->name('pig_sales.pens_by_batch');
    Route::get('/batches-by-farm/{farmId}', [PigSaleController::class, 'getBatchesByFarm'])->name('pig_sales.batches_by_farm');
    Route::get('/barns-by-farm/{farmId}', [PigSaleController::class, 'getBarnsByFarm'])->name('pig_sales.barns_by_farm');
    Route::get('/barns-by-farm-for-allocation/{farmId}', [PigSaleController::class, 'getBarnsByFarmForAllocation'])->name('pig_sales.barns_by_farm_for_allocation');
    Route::get('/pens-by-barn/{barnId}', [PigSaleController::class, 'getPensByBarn'])->name('pig_sales.pens_by_barn');
    Route::post('/get-status-batch', [PigSaleController::class, 'getStatusBatch'])->name('pig_sales.get_status_batch'); // ✅ Auto-refresh status

    //------------------- route export batch ---------------------//
    Route::get('/export/csv', [PigSaleController::class, 'exportCsv'])->name('pig_sales.export.csv');
    Route::get('/export/pdf', [PigSaleController::class, 'exportPdf'])->name('pig_sales.export.pdf');
});

//------------------- route payments -------//
Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store')->middleware('auth');
Route::patch('/payments/{id}/approve', [PaymentController::class, 'approve'])->name('payments.approve')->middleware('auth');
Route::patch('/payments/{id}/reject', [PaymentController::class, 'reject'])->name('payments.reject')->middleware('auth');

//------------------- route notifications --------------------//
Route::prefix('notifications')->middleware(['auth', 'prevent.cache'])->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/recent', [NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::get('/unread_count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread_count');
    Route::post('/{id}/mark_as_read', [NotificationController::class, 'markAsRead'])->name('notifications.mark_as_read');
    Route::post('/{id}/mark_as_read_only', [NotificationController::class, 'markAsReadOnly'])->name('notifications.mark_as_read_only');
    Route::post('/{id}/mark_as_read_and_navigate_to_notifications', [NotificationController::class, 'markAsReadAndNavigateToNotifications'])->name('notifications.mark_as_read_and_navigate_to_notifications');
    Route::post('/mark_all_read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark_all_as_read');
    Route::post('/clear_read', [NotificationController::class, 'clearRead'])->name('notifications.clear_read');
    Route::get('/{id}/mark_and_navigate', [NotificationController::class, 'markAndNavigate'])->name('notifications.mark_and_navigate');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

//------------------- route user management ------------------//
Route::prefix('user_management')->middleware(['auth', 'prevent.cache', 'permission:manage_users'])->group(function () {
    Route::get('/', [UserManagementController::class, 'index'])->name('user_management.index');
    Route::get('/pending', [UserManagementController::class, 'pending'])->name('user_management.pending');
    Route::post('/{id}/approve', [UserManagementController::class, 'approve'])->name('user_management.approve');
    Route::post('/{id}/reject', [UserManagementController::class, 'reject'])->name('user_management.reject');
    Route::post('/{id}/assign_role', [UserManagementController::class, 'assignRole'])->name('user_management.assign_role');
    Route::post('/{id}/update_roles', [UserManagementController::class, 'updateRoles'])->name('user_management.update_roles');
    Route::delete('/{id}', [UserManagementController::class, 'destroy'])->name('user_management.destroy');
    Route::post('/{id}/request_cancel', [UserManagementController::class, 'requestCancelRegistration'])->name('user_management.request_cancel');
    Route::patch('/{id}/approve_cancel', [UserManagementController::class, 'approveCancelRegistration'])->name('user_management.approve_cancel');
    Route::patch('/{id}/reject_cancel', [UserManagementController::class, 'rejectCancelRegistration'])->name('user_management.reject_cancel');
    Route::get('/api/user_type_options', [UserManagementController::class, 'getUserTypeOptions'])->name('user_management.user_type_options');
    Route::get('/api/user_roles/{id}', [UserManagementController::class, 'getUserRoles'])->name('user_management.user_roles');
});

//------------------- route payment approvals ----------------//
Route::prefix('payment_approvals')->middleware(['auth', 'prevent.cache'])->group(function () {
    Route::get('/', [PaymentApprovalController::class, 'index'])->name('payment_approvals.index');
    Route::get('/{notificationId}/detail', [PaymentApprovalController::class, 'detail'])->name('payment_approvals.detail');

    // Payment table (pig sale payments)
    Route::patch('/{paymentId}/approve-payment', [PaymentApprovalController::class, 'approvePayment'])->name('payment_approvals.approve_payment');
    Route::patch('/{paymentId}/reject-payment', [PaymentApprovalController::class, 'rejectPayment'])->name('payment_approvals.reject_payment');

    // ✅ NEW: PigSale approval (for หมูตาย and หมูปกติ)
    Route::patch('/{pigSaleId}/approve-pig-sale', [PaymentApprovalController::class, 'approvePigSale'])->name('payment_approvals.approve_pig_sale');
    Route::patch('/{pigSaleId}/reject-pig-sale', [PaymentApprovalController::class, 'rejectPigSale'])->name('payment_approvals.reject_pig_sale');

    // Cancel sale approval
    Route::patch('/{pigSaleId}/approve-cancel-sale', [PaymentApprovalController::class, 'approveCancelSale'])->name('payment_approvals.approve_cancel_sale');
    Route::patch('/{pigSaleId}/reject-cancel-sale', [PaymentApprovalController::class, 'rejectCancelSale'])->name('payment_approvals.reject_cancel_sale');

    // Export routes
    Route::get('/export/csv', [PaymentApprovalController::class, 'exportCsv'])->name('payment_approvals.export.csv');
});

//------------------- route cost payment approvals -----------//
Route::prefix('cost_payment_approvals')->middleware(['auth', 'prevent.cache'])->group(function () {
    Route::get('/', [CostPaymentApprovalController::class, 'index'])->name('cost_payment_approvals.index');
    Route::get('/{id}', [CostPaymentApprovalController::class, 'show'])->name('cost_payment_approvals.show');
    Route::post('/{id}/approve', [CostPaymentApprovalController::class, 'approve'])->name('cost_payment_approvals.approve');
    Route::post('/{id}/reject', [CostPaymentApprovalController::class, 'reject'])->name('cost_payment_approvals.reject');

    // Export routes
    Route::get('/export/csv', [CostPaymentApprovalController::class, 'exportCsv'])->name('cost_payment_approvals.export.csv');
});

//------------------- route dashboard -------------------------//
Route::get('/showProjectedProfitsDashboard', [ProfitController::class, 'showProjectedProfits'])->name('dashboard.projected.list');
Route::prefix('dashboard')->middleware(['auth', 'prevent.cache'])->group(function () {
    Route::get('/', [ProfitController::class, 'index'])->name('dashboard.index');
    Route::get('/{id}', [ProfitController::class, 'show'])->name('dashboard.show');
    Route::post('/{batchId}/recalculate', [ProfitController::class, 'recalculateBatchProfit'])->name('dashboard.recalculate');
    Route::get('/export/pdf', [ProfitController::class, 'exportPdf'])->name('dashboard.export.pdf');
    Route::get('/export/csv', [ProfitController::class, 'exportCsv'])->name('dashboard.export.csv');

});

// ✅ API endpoints สำหรับ AJAX chart refresh (outside prefix)
Route::middleware('auth')->group(function () {
    Route::get('/api/dashboard/chart-data', [ProfitController::class, 'getChartData'])->name('api.dashboard.chart_data');
    Route::get('/api/dashboard/monthly-cost-profit', [ProfitController::class, 'getMonthlyCostProfitData'])->name('api.dashboard.monthly_cost_profit');
    Route::get('/api/dashboard/fcg-performance', [ProfitController::class, 'getFcgPerformanceData'])->name('api.dashboard.fcg_performance');
    Route::get('/api/dashboard/projected-profits', [ProfitController::class, 'getProjectedProfits'])->name('api.dashboard.projected_profits');
});

