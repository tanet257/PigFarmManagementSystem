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

//========== API: BATCHES ==========
/**
 * GET /api/farms/{farm_id}/batches
 * Get all batches for a farm
 */
Route::get('/farms/{farm_id}/batches', function ($farmId) {
    try {
        \Illuminate\Support\Facades\Log::info('🔍 [API] GET /api/farms/'.$farmId.'/batches');

        $batches = Batch::where('farm_id', $farmId)
            ->select('id', 'batch_code as code', 'farm_id')
            ->get();

        \Illuminate\Support\Facades\Log::info('✅ [API] Batches loaded: ' . count($batches) . ' items');

        return response()->json([
            'success' => true,
            'data' => $batches
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('❌ [API] Batches error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
});

//========== API: MEDICINES ==========
/**
 * GET /api/medicines?farm_id=X
 * Get medicines available for a farm from storehouse
 */
Route::get('/medicines', function (Request $request) {
    try {
        $farmId = $request->query('farm_id');

        if (!$farmId) {
            return response()->json([
                'success' => false,
                'message' => 'farm_id is required'
            ], 400);
        }

        $medicines = \App\Models\StoreHouse::where('item_type', 'medicine')
            ->where(function ($query) use ($farmId) {
                $query->where('farm_id', $farmId)
                      ->orWhere('farm_id', 0); // 0 = available for all farms
            })
            ->where('status', '!=', 'cancelled')
            ->select('id', 'item_code as code', 'item_name as name', 'stock', 'unit', 'farm_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $medicines
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
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

//========== API: TREATMENTS (CREATE/UPDATE VIA MODAL) ==========
/**
 * POST /api/treatments
 * GET /api/treatments/{id}
 * Get treatment details with related batch_treatment_details
 */
Route::get('/treatments/{id}', function ($id) {
    try {
        \Illuminate\Support\Facades\Log::info('🔍 [API] GET /api/treatments/'.$id);

        $treatment = \App\Models\BatchTreatment::with([
            'batch:id,batch_code,farm_id',
            'details' => function($q) {
                $q->with([
                    'pen:id,pen_code,barn_id',
                    'barn:id,barn_code'
                ]);
            }
        ])->find($id);

        if (!$treatment) {
            return response()->json(['success' => false, 'message' => 'Treatment not found'], 404);
        }

        // ✅ Add current_quantity from batch_pen_allocations to each detail
        if ($treatment->details && $treatment->details->count() > 0) {
            foreach ($treatment->details as $detail) {
                $allocation = \App\Models\BatchPenAllocation::where('batch_id', $treatment->batch_id)
                    ->where('pen_id', $detail->pen_id)
                    ->first();

                $detail->current_quantity = $allocation ? $allocation->current_quantity : 0;
                \Illuminate\Support\Facades\Log::debug('📊 [API] Detail ' . $detail->id . ' - Pen: ' . $detail->pen_id . ', Current Qty: ' . $detail->current_quantity);
            }
        }

        \Illuminate\Support\Facades\Log::info('✅ [API] Treatment loaded: ID '.$id);

        return response()->json([
            'success' => true,
            'data' => $treatment
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('❌ [API] GET /api/treatments error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
});

/**
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

        // Get selected pens
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

        // ✅ Convert planned_start_date from d/m/Y to YYYY-MM-DD
        $plannedStartDate = $request->input('planned_start_date');
        if ($plannedStartDate) {
            // If format is d/m/Y, convert to YYYY-MM-DD
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $plannedStartDate)) {
                $parts = explode('/', $plannedStartDate);
                if (count($parts) === 3) {
                    $plannedStartDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0]; // Y-m-d
                    \Illuminate\Support\Facades\Log::info('📅 [API] Converted planned_start_date to: ' . $plannedStartDate);
                }
            }
        }

        // ✅ Get medicine_code from storehouse (ทำเสบียงหา medicine_code)
        $medicineName = $request->input('medicine_name');
        $medicineCode = $request->input('medicine_code');
        $farmId = $request->input('farm_id');

        $storehouse = null;
        if (!$medicineCode && $medicineName && $farmId) {
            // Find storehouse by item_name and farm_id
            $storehouse = \App\Models\StoreHouse::where('item_name', $medicineName)
                ->where('item_type', 'medicine')
                ->where(function($q) use ($farmId) {
                    $q->where('farm_id', $farmId)->orWhere('farm_id', 0);
                })
                ->first();

            if ($storehouse) {
                $medicineCode = $storehouse->item_code;
                \Illuminate\Support\Facades\Log::info('🔍 [API] Found storehouse: ' . $medicineCode);
            }
        }

        // ==================== CREATE 1 TREATMENT RECORD ====================
        $treatment = \App\Models\BatchTreatment::create([
            'batch_id' => $batchId,
            'pen_id' => null, // ✅ ไม่เก็บ pen_id ตรงนี้ เพราะจะเก็บใน details
            'treatment_level' => $request->input('treatment_level', 'pen'),
            'farm_id' => $farmId,
            'medicine_name' => $medicineName,
            'medicine_code' => $medicineCode,
            'disease_name' => $request->input('disease_name'),
            'dosage' => $request->input('dosage', 0),
            'frequency' => $request->input('frequency'),
            'treatment_status' => $status,
            'note' => $request->input('note'),
            'planned_start_date' => $plannedStartDate,
            'planned_duration' => $request->input('planned_duration'),
            'actual_end_date' => $actualEndDate,
            'effective_date' => $request->input('effective_date', now())
        ]);

        \Illuminate\Support\Facades\Log::info('✅ [API] Created treatment record ID: ' . $treatment->id);

        // ==================== CREATE DETAILS FOR EACH PEN ====================
        $dosage = floatval($request->input('dosage', 0));
        $detailRecords = [];
        foreach ($penIds as $penId) {
            $pen = \App\Models\Pen::find($penId);
            if ($pen) {
                // ✅ Get current_quantity from batch_pen_allocations
                $allocation = \App\Models\BatchPenAllocation::where('batch_id', $batchId)
                    ->where('pen_id', $penId)
                    ->first();

                $currentQuantity = $allocation ? $allocation->current_quantity : 0;

                // ✅ คำนวณจำนวนยาที่ใช้ = dosage × จำนวนหมูคงเหลือในคอก
                $quantityUsed = $dosage * $currentQuantity;

                $detail = \App\Models\BatchTreatmentDetail::create([
                    'batch_treatment_id' => $treatment->id,
                    'pen_id' => $penId,
                    'barn_id' => $pen->barn_id,
                    'treatment_date' => $plannedStartDate ?? now()->format('Y-m-d'),
                    'quantity_used' => $quantityUsed, // ✅ dosage × current_quantity
                    'unit' => $storehouse->unit ?? 'ml',
                    'note' => 'สร้างตามแผน',
                ]);
                $detailRecords[] = $detail;
                \Illuminate\Support\Facades\Log::info('📝 [API] Created detail for pen: ' . $penId . ' (qty: ' . $currentQuantity . ') - quantity_used: ' . $quantityUsed);
            }
        }

        // ==================== UPDATE STOREHOUSE INVENTORY ====================
        if ($storehouse) {
            $frequency = $request->input('frequency', 'once');
            $duration = $request->input('planned_duration', 1);

            // คำนวณจำนวนวันการให้ยา
            $frequencyPerDay = [
                'once' => 1,
                'daily' => 1,
                'twice_daily' => 2,
                'every_other_day' => 0.5,
                'weekly' => 0.14,
                'custom' => 1,
            ][$frequency] ?? 1;

            // ✅ รวม quantity_used จากทุก detail record
            $totalQuantityPerTreatment = collect($detailRecords)->sum('quantity_used');

            // ✅ คำนวณปริมาณยาทั้งหมดที่ต้องใช้ = รวมของแต่ละคอก × frequency × duration
            $totalQuantity = $totalQuantityPerTreatment * $frequencyPerDay * $duration;

            \Illuminate\Support\Facades\Log::info('💊 [API] Calculated total quantity: ' . $totalQuantity . ' ' . $storehouse->unit);

            // ลดสต็อก (ต้องเก็บ record ใน inventory_movements)
            $inventoryMovement = \App\Models\InventoryMovement::create([
                'storehouse_id' => $storehouse->id,
                'batch_id' => $batchId,
                'batch_treatment_id' => $treatment->id,
                'change_type' => 'out',
                'quantity' => $totalQuantity,
                'quantity_unit' => $storehouse->unit,
                'note' => 'ใช้ยา ' . $medicineName . ' สำหรับการรักษา ' . $request->input('disease_name') . ' (' . count($penIds) . ' คอก)',
                'date' => now(),
            ]);

            // ลดสต็อกใน storehouse
            $storehouse->decrement('stock', (int)$totalQuantity);

            \Illuminate\Support\Facades\Log::info('📦 [API] Updated storehouse stock: -' . $totalQuantity);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'treatment' => $treatment,
                'details' => $detailRecords,
                'pens_count' => count($penIds),
            ],
            'message' => 'Treatment created successfully with ' . count($penIds) . ' pens'
        ], 201);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('❌ [API] POST /api/treatments error: ' . $e->getMessage());
        \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
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

        // ✅ Convert planned_start_date from d/m/Y to YYYY-MM-DD if provided
        $plannedStartDate = $request->input('planned_start_date', $treatment->planned_start_date);
        if ($plannedStartDate) {
            // If format is d/m/Y, convert to YYYY-MM-DD
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $plannedStartDate)) {
                $parts = explode('/', $plannedStartDate);
                if (count($parts) === 3) {
                    $plannedStartDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0]; // Y-m-d
                    \Illuminate\Support\Facades\Log::info('📅 [API] Converted planned_start_date to: ' . $plannedStartDate);
                }
            }
        }

        $treatment->update([
            'medicine_name' => $request->input('medicine_name', $treatment->medicine_name),
            'medicine_code' => $request->input('medicine_code', $treatment->medicine_code),
            'disease_name' => $request->input('disease_name', $treatment->disease_name),
            'dosage' => $request->input('dosage', $treatment->dosage),
            'frequency' => $request->input('frequency', $treatment->frequency),
            'treatment_status' => $status,
            'note' => $request->input('note', $treatment->note),
            'actual_start_date' => $request->input('actual_start_date', $treatment->actual_start_date),
            'planned_start_date' => $plannedStartDate,
            'planned_duration' => $request->input('planned_duration', $treatment->planned_duration),
            'actual_end_date' => $actualEndDate,
            'effective_date' => $request->input('effective_date', now())
        ]);

        // ✅ Handle pen_ids if provided (UPDATE SELECTED PENS)
        $penIds = $request->input('pen_ids', []);
        if (!empty($penIds)) {
            \Illuminate\Support\Facades\Log::info('🔄 [API] Updating pens for treatment ' . $id . ': ' . json_encode($penIds));

            // Delete existing details
            \App\Models\BatchTreatmentDetail::where('batch_treatment_id', $id)->delete();

            // Create new details for selected pens
            $dosage = floatval($request->input('dosage', $treatment->dosage));
            $detailRecords = [];

            foreach ($penIds as $penId) {
                $pen = \App\Models\Pen::find($penId);
                if ($pen) {
                    // Get current_quantity from batch_pen_allocations
                    $allocation = \App\Models\BatchPenAllocation::where('batch_id', $treatment->batch_id)
                        ->where('pen_id', $penId)
                        ->first();

                    $currentQuantity = $allocation ? $allocation->current_quantity : 0;
                    $quantityUsed = $dosage * $currentQuantity;

                    $detail = \App\Models\BatchTreatmentDetail::create([
                        'batch_treatment_id' => $treatment->id,
                        'pen_id' => $penId,
                        'barn_id' => $pen->barn_id,
                        'treatment_date' => $plannedStartDate ?? now()->format('Y-m-d'),
                        'quantity_used' => $quantityUsed,
                        'unit' => $request->input('unit', 'ml'),
                        'note' => 'อัพเดทจากการแก้ไข',
                    ]);
                    $detailRecords[] = $detail;
                    \Illuminate\Support\Facades\Log::info('📝 [API] Created detail for pen: ' . $penId . ' - quantity_used: ' . $quantityUsed);
                }
            }
        } else {
            // If no pen_ids provided, just update quantity_used for existing details
            $dosage = floatval($request->input('dosage', $treatment->dosage));
            $detailRecords = $treatment->details()->get();
            $detailRecords->each(function($detail) use ($dosage, $treatment) {
                // Get current_quantity from batch_pen_allocations
                $allocation = \App\Models\BatchPenAllocation::where('batch_id', $treatment->batch_id)
                    ->where('pen_id', $detail->pen_id)
                    ->first();

                $currentQuantity = $allocation ? $allocation->current_quantity : 0;

                $detail->update([
                    'quantity_used' => $dosage * $currentQuantity,
                ]);
                \Illuminate\Support\Facades\Log::info('📝 [API] Updated detail for pen: ' . $detail->pen_id . ' (qty: ' . $currentQuantity . ') - quantity_used: ' . $detail->quantity_used);
            });
        }

        // ✅ Create/update inventory movement when status changes to completed or stopped
        if (in_array($status, ['completed', 'stopped']) && $treatment->actual_start_date && $actualEndDate) {
            // Check if inventory movement already exists
            $existingMovement = \App\Models\InventoryMovement::where('batch_treatment_id', $id)->first();

            if (!$existingMovement) {
                \Illuminate\Support\Facades\Log::info('📦 [API] Creating new inventory movement for treatment ' . $id);

                // Get medicine storehouse
                $storehouse = \App\Models\StoreHouse::where('item_code', $treatment->medicine_code)
                    ->where('item_type', 'medicine')
                    ->first();

                if ($storehouse) {
                    // Calculate total quantity from details
                    $totalQuantityUsed = collect($detailRecords)->sum('quantity_used');

                    // Calculate frequency multiplier
                    $frequencyPerDay = [
                        'once' => 1,
                        'daily' => 1,
                        'twice_daily' => 2,
                        'every_other_day' => 0.5,
                        'weekly' => 0.142857,
                        'custom' => 1,
                    ][$treatment->frequency] ?? 1;

                    $duration = $request->input('planned_duration', $treatment->planned_duration ?? 1);
                    $totalQuantity = $totalQuantityUsed * $frequencyPerDay * $duration;

                    // Create inventory movement
                    \App\Models\InventoryMovement::create([
                        'storehouse_id' => $storehouse->id,
                        'batch_id' => $treatment->batch_id,
                        'batch_treatment_id' => $id,
                        'change_type' => 'out',
                        'quantity' => $totalQuantity,
                        'quantity_unit' => $storehouse->unit ?? 'ml',
                        'note' => 'ใช้ยา ' . $treatment->medicine_name . ' สำหรับการรักษา ' . $treatment->disease_name,
                        'date' => now(),
                    ]);

                    // Reduce storehouse stock
                    $storehouse->decrement('stock', (int)$totalQuantity);

                    \Illuminate\Support\Facades\Log::info('📦 [API] Stock reduced by: ' . $totalQuantity . ' ' . $storehouse->unit);
                } else {
                    \Illuminate\Support\Facades\Log::warning('⚠️ [API] Storehouse not found for medicine: ' . $treatment->medicine_code);
                }
            } else {
                \Illuminate\Support\Facades\Log::info('ℹ️ [API] Inventory movement already exists for treatment ' . $id);
            }
        }

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

//========== API: CONVERSION RATE HELPER ==========
/**
 * GET /api/treatments/conversion-calculator
 * Helper endpoint เพื่อแสดงตัวอย่างการแปลงหน่วยยา
 *
 * @param float $quantity_ml จำนวนยาที่ใช้ (ml)
 * @param int $storehouse_id ID ของยา
 * @return Response with calculation details
 */
Route::get('/treatments/conversion-calculator', function (Request $request) {
    try {
        $quantityMl = $request->query('quantity_ml', 20); // ตัวอย่าง: 20 ml
        $storehouseId = $request->query('storehouse_id');

        if (!$storehouseId) {
            return response()->json([
                'success' => false,
                'message' => 'storehouse_id is required'
            ], 400);
        }

        $storehouse = \App\Models\StoreHouse::findOrFail($storehouseId);

        if (!$storehouse->conversion_rate || !$storehouse->base_unit) {
            return response()->json([
                'success' => false,
                'message' => 'Storehouse does not have conversion rate configured',
                'storehouse' => $storehouse
            ], 400);
        }

        // คำนวณ
        $quantityInStockUnit = $quantityMl / $storehouse->conversion_rate;
        $quantityRoundUp = ceil($quantityInStockUnit); // ปัดขึ้น

        return response()->json([
            'success' => true,
            'calculation' => [
                'medicine_name' => $storehouse->item_name,
                'medicine_code' => $storehouse->item_code,
                'used_quantity' => $quantityMl,
                'used_unit' => $storehouse->base_unit,
                'conversion_rate' => $storehouse->conversion_rate . " {$storehouse->base_unit} per {$storehouse->unit}",
                'stock_unit' => $storehouse->unit,
                'formula' => "{$quantityMl} ÷ {$storehouse->conversion_rate} = {$quantityInStockUnit}",
                'exact_stock_reduction' => $quantityInStockUnit,
                'rounded_stock_reduction' => $quantityRoundUp,
                'current_stock' => $storehouse->stock,
                'stock_after_reduction' => max(0, $storehouse->stock - $quantityRoundUp),
                'message' => "ใช้ {$quantityMl} {$storehouse->base_unit} = ลดสต็อก {$quantityRoundUp} {$storehouse->unit}"
            ]
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('❌ [API] Conversion calculator error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
});

//------------------- route API profits ----------------------//
Route::middleware('auth:sanctum')->prefix('profits')->group(function () {
    Route::get('/farm/{farmId}/summary', [ProfitController::class, 'getFarmProfitSummary'])->name('api.profits.farm_summary');
    Route::get('/batch/{batchId}/details', [ProfitController::class, 'getBatchProfitDetails'])->name('api.profits.batch_details');
});

//========== API: LOGGING ==========
/**
 * POST /api/log
 * Log actions from frontend
 */
Route::post('/log', function (Request $request) {
    try {
        $logData = $request->all();

        \Illuminate\Support\Facades\Log::channel('actions')->info('📝 [Frontend Action]', [
            'action' => $logData['action'] ?? 'unknown',
            'method' => $logData['method'] ?? null,
            'treatment_id' => $logData['treatment_id'] ?? null,
            'pen_count' => $logData['pen_count'] ?? 0,
            'status' => $logData['status'] ?? null,
            'message' => $logData['message'] ?? null,
            'timestamp' => $logData['timestamp'] ?? now()->toIso8601String(),
            'user_id' => auth()->id() ?? 'guest',
            'user_agent' => $request->header('User-Agent'),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Log recorded'
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('❌ [API] Log endpoint error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
});
