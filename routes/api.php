<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ProfitController;
use App\Models\Farm;
use App\Models\Batch;
use App\Services\BarnPenSelectionService;

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

//========== API: FARMS & BATCHES ==========
/**
 * GET /api/farms/{farmId}/batches
 * ดึงรายการรุ่นจากฟาร์ม
 *
 * @param farmId ฟาร์ม ID
 * @return JSON array of batches
 */
Route::get('/farms/{farmId}/batches', function ($farmId) {
    try {
        \Illuminate\Support\Facades\Log::info('📋 [API] Fetching batches for farm: ' . $farmId);

        $batches = Batch::where('farm_id', $farmId)->get(['id', 'batch_code', 'farm_id']);

        \Illuminate\Support\Facades\Log::info('✅ [API] Found ' . $batches->count() . ' batches');

        return response()->json($batches);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('❌ [API] Error fetching batches: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

//========== API: BARN-PEN SELECTION ==========
/**
 * GET /api/barn-pen/selection
 * ดึงรายการเล้า-คอกสำหรับเลือกในการสร้าง checkbox table
 *
 * @param farm_id ฟาร์ม ID (required)
 * @param batch_id รุ่น ID (required)
 * @return JSON object with format: { success, data, message }
 *
 * ✅ Used by:
 * - Treatments: เลือกเล้า-คอกสำหรับการรักษา
 * - Dairy Records: เลือกเล้า-คอกสำหรับบันทึกนม
 * - Pig Sales: เลือกเล้า-คอกสำหรับการขายหมู
 */
Route::get('/barn-pen/selection', function (Request $request) {
    try {
        $farmId = $request->query('farm_id');
        $batchId = $request->query('batch_id');

        \Illuminate\Support\Facades\Log::info('🔍 [API] barn-pen/selection - farm: ' . $farmId . ', batch: ' . $batchId);

        $result = BarnPenSelectionService::getPensByFarmAndBatch($farmId, $batchId, false);

        \Illuminate\Support\Facades\Log::info('✅ [API] barn-pen/selection complete - ' . count($result['data']) . ' items');

        return response()->json($result);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('❌ [API] barn-pen/selection error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'data' => [],
            'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
        ], 500);
    }
});

//========== API: MEDICINES FROM STOREHOUSE ==========
/**
 * GET /api/medicines
 * ดึงรายการยา/วัคซีนจาก storehouse ของฟาร์ม
 *
 * @param farm_id ฟาร์ม ID (required)
 * @return JSON array with format: { id, item_code, item_name, stock, unit, status }
 *
 * ✅ Used by:
 * - Treatments: เลือกยา/วัคซีนสำหรับการรักษา (filter: item_type='medicine')
 */
Route::get('/medicines', function (Request $request) {
    try {
        $farmId = $request->query('farm_id');

        \Illuminate\Support\Facades\Log::info('💊 [API] /api/medicines - farm_id: ' . $farmId);

        if (!$farmId) {
            \Illuminate\Support\Facades\Log::warning('⚠️ [API] /api/medicines - farm_id not provided');
            return response()->json(['error' => 'farm_id required'], 400);
        }

        // ดึงยา/วัคซีนจาก storehouse ของฟาร์ม (item_type = 'medicine')
        $medicines = \App\Models\StoreHouse::where('farm_id', $farmId)
            ->where('item_type', 'medicine')
            ->where('status', '!=', 'cancelled')
            ->select('id', 'item_code', 'item_name', 'stock', 'unit', 'status')
            ->orderBy('item_name')
            ->get();

        \Illuminate\Support\Facades\Log::info('✅ [API] Found ' . $medicines->count() . ' medicines for farm ' . $farmId);

        return response()->json($medicines);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('❌ [API] /api/medicines error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

//========== API: TREATMENTS (CREATE/UPDATE VIA MODAL) ==========
/**
 * POST /api/treatments
 * Create a new treatment from modal form
 *
 * @param Request $request with FormData fields:
 *   - batch_id, treatment_level, farm_id
 *   - medicine_name, quantity, status, note
 *   - planned_start_date, actual_start_date, planned_duration
 *   - actual_end_date (auto-set if status is completed/stopped)
 *   - pen_ids (array of selected pen IDs)
 *
 * @return Response with success, data, message fields
 */
Route::post('/treatments', function (Request $request) {
    try {
        \Illuminate\Support\Facades\Log::info('💾 [API] POST /api/treatments - Creating new treatment');

        $batchId = $request->input('batch_id');
        if (!$batchId) {
            return response()->json(['success' => false, 'message' => 'batch_id required'], 400);
        }

        // Create treatment record for each selected pen
        $penIds = $request->input('pen_ids', []);
        if (empty($penIds)) {
            return response()->json(['success' => false, 'message' => 'At least one pen must be selected'], 400);
        }

        $status = $request->input('treatment_status', 'pending');
        $actualEndDate = null;

        // Auto-set actual_end_date if status is completed or stopped
        if (in_array($status, ['completed', 'stopped'])) {
            $actualEndDate = now()->format('Y-m-d');
            \Illuminate\Support\Facades\Log::info('📅 [API] Auto-setting actual_end_date to: ' . $actualEndDate);
        }

        $createdTreatments = [];
        foreach ($penIds as $penId) {
            $treatment = \App\Models\BatchTreatment::create([
                'batch_id' => $batchId,
                'pen_id' => $penId,
                'treatment_level' => $request->input('treatment_level', 'pen'),
                'farm_id' => $request->input('farm_id'),
                'medicine_name' => $request->input('medicine_name'),
                'disease_name' => $request->input('disease_name'),
                'dosage' => $request->input('dosage', 0),
                'frequency' => $request->input('frequency'),
                'treatment_status' => $status,
                'note' => $request->input('note'),
                'planned_start_date' => $request->input('planned_start_date'),
                'planned_duration' => $request->input('planned_duration'),
                'actual_end_date' => $actualEndDate,
                'effective_date' => $request->input('effective_date', now())
            ]);
            $createdTreatments[] = $treatment;
        }

        \Illuminate\Support\Facades\Log::info('✅ [API] Created ' . count($createdTreatments) . ' treatment records');

        return response()->json([
            'success' => true,
            'data' => $createdTreatments,
            'message' => 'Treatment created successfully'
        ], 201);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('❌ [API] POST /api/treatments error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
});

/**
 * PUT /api/treatments/{id}
 * Update an existing treatment from modal form
 *
 * @param int $id Treatment ID
 * @param Request $request with FormData fields (same as POST)
 *
 * @return Response with success, data, message fields
 */
Route::put('/treatments/{id}', function (Request $request, $id) {
    try {
        \Illuminate\Support\Facades\Log::info('📝 [API] PUT /api/treatments/' . $id . ' - Updating treatment');

        $treatment = \App\Models\BatchTreatment::findOrFail($id);

        $status = $request->input('treatment_status', $treatment->treatment_status);
        $actualEndDate = $treatment->actual_end_date; // Keep existing value

        // Auto-set actual_end_date if status is completed or stopped
        if (in_array($status, ['completed', 'stopped'])) {
            if (!$actualEndDate) { // Only set if not already set
                $actualEndDate = now()->format('Y-m-d');
                \Illuminate\Support\Facades\Log::info('📅 [API] Auto-setting actual_end_date to: ' . $actualEndDate);
            }
        }

        $treatment->update([
            'medicine_name' => $request->input('medicine_name', $treatment->medicine_name),
            'disease_name' => $request->input('disease_name', $treatment->disease_name),
            'dosage' => $request->input('dosage', $treatment->dosage),
            'frequency' => $request->input('frequency', $treatment->frequency),
            'treatment_status' => $status,
            'note' => $request->input('note', $treatment->note),
            'actual_start_date' => $request->input('actual_start_date', $treatment->actual_start_date),
            'planned_duration' => $request->input('planned_duration', $treatment->planned_duration),
            'actual_end_date' => $actualEndDate,
            'effective_date' => $request->input('effective_date', now())
        ]);

        \Illuminate\Support\Facades\Log::info('✅ [API] Treatment ' . $id . ' updated successfully');

        return response()->json([
            'success' => true,
            'data' => $treatment->fresh(),
            'message' => 'Treatment updated successfully'
        ]);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('❌ [API] PUT /api/treatments/' . $id . ' error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
});

//========== API: OLD TREATMENTS/PENS (DEPRECATED) ==========
// ⚠️ NOTE: This endpoint is deprecated and should not be used anymore
// Use /api/barn-pen/selection instead
Route::get('/treatments/pens', function (Request $request) {
    try {
        $farmId = $request->query('farm_id');
        $batchId = $request->query('batch_id');

        \Illuminate\Support\Facades\Log::warning('⚠️ [API] DEPRECATED endpoint /api/treatments/pens called - use /api/barn-pen/selection instead');

        if (!$farmId || !$batchId) {
            return response()->json(['error' => 'farm_id and batch_id required'], 400);
        }

        $pens = DB::table('pens')
            ->join('barns', 'pens.barn_id', '=', 'barns.id')
            ->join('batch_pen_allocations', 'pens.id', '=', 'batch_pen_allocations.pen_id')
            ->where('barns.farm_id', $farmId)
            ->where('batch_pen_allocations.batch_id', $batchId)
            ->select(
                'pens.id',
                'pens.pen_number',
                'barns.id as barn_id',
                'barns.barn_name',
                DB::raw('COALESCE(pens.current_pig_count, 0) as pig_count')
            )
            ->get();

        return response()->json($pens);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('❌ [API] Error in deprecated /api/treatments/pens: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

//------------------- route API profits ----------------------//
Route::middleware('auth:sanctum')->prefix('profits')->group(function () {
    Route::get('/farm/{farmId}/summary', [ProfitController::class, 'getFarmProfitSummary'])->name('api.profits.farm_summary');
    Route::get('/batch/{batchId}/details', [ProfitController::class, 'getBatchProfitDetails'])->name('api.profits.batch_details');
});

