<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Farm;
use App\Models\Barn;
use App\Models\BatchPenAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchEntryController extends Controller
{
    /**
     * List all batches
     */
    public function index(Request $request)
    {
        // Get all farms
        $farms = Farm::all();
        $farm = $farms->first();

        if (!$farm) {
            return redirect()->route('dashboard')->with('error', 'ไม่พบฟาร์มในระบบ');
        }

        // Get all batches for filter dropdown
        $allBatches = Batch::all();

        // Apply filters
        $query = Batch::query();

        // Filter by farm if provided (don't default to first farm - show all batches)
        $farmId = $request->get('farm_id');
        if ($farmId) {
            $query->where('farm_id', $farmId);
        }

        // Filter by batch if provided
        if ($request->get('batch_id')) {
            $query->where('id', $request->get('batch_id'));
        }

        // Filter by date
        if ($request->get('selected_date')) {
            $now = now();
            switch ($request->get('selected_date')) {
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [
                        $now->copy()->startOfWeek(),
                        $now->copy()->endOfWeek()
                    ]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', $now->month)
                          ->whereYear('created_at', $now->year);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', $now->year);
                    break;
            }
        }

        // Apply sort
        $sort = $request->get('sort', 'created_at');
        switch ($sort) {
            case 'name_asc':
                $query->orderBy('batch_code', 'ASC');
                break;
            case 'name_desc':
                $query->orderBy('batch_code', 'DESC');
                break;
            case 'quantity_asc':
                $query->orderBy('total_pig_amount', 'ASC');
                break;
            case 'quantity_desc':
                $query->orderBy('total_pig_amount', 'DESC');
                break;
            default:
                $query->orderByDesc('created_at');
        }

        // Get per_page from request (default 15)
        $perPage = $request->get('per_page', 15);
        $batches = $query->with(['allocations.barn', 'batch_metric'])->paginate($perPage);

        $barns = $farm->barns()->get();

        // Get statistics
        $stats = [
            'total' => Batch::where('farm_id', $farmId)->count(),
            'raising' => Batch::where('farm_id', $farmId)->where('status', 'raising')->count(),
            'selling' => Batch::where('farm_id', $farmId)->where('status', 'selling')->count(),
            'closed' => Batch::where('farm_id', $farmId)->where('status', 'closed')->count(),
        ];

        return view('admin.batch.index', [
            'batches' => $batches,
            'stats' => $stats,
            'barns' => $barns,
            'farms' => $farms,
            'allBatches' => $allBatches,
        ]);
    }

    /**
     * Show combined form for creating Batch + PigEntry
     */
    public function createWithEntry()
    {
        // Get first farm (or use farm_id from first batch)
        $farm = Farm::first();


        $barns = $farm->barns()->get();

        return view('admin.batch.index', [
            'farm' => $farm,
            'barns' => $barns,
        ]);
    }

    /**
     * Store combined Batch + PigEntry
     */
    public function storeWithEntry(Request $request)
    {
        // ✅ NEW: ใช้ farm_id จาก request แทน $farm->first()
        $farmId = $request->input('farm_id');
        $farm = Farm::findOrFail($farmId);

        // Validation
        $validated = $request->validate([
            // Batch fields
            'farm_id' => 'required|integer|exists:farms,id',
            'batch_code' => 'required|string|unique:batches,batch_code',
            'note' => 'nullable|string|max:1000',
            'barn_ids' => 'required|array|min:1',
            'barn_ids.*' => 'integer|exists:barns,id',

            // PigEntry fields
            'pig_entry_date' => 'required|date',
            'total_pig_amount' => 'required|integer|min:1',
            'total_pig_weight' => 'required|numeric|min:0.1',
            'total_pig_price' => 'required|numeric|min:0',
            'average_weight_per_pig' => 'required|numeric|min:0.1',
            'average_price_per_pig' => 'required|numeric|min:0',
        ]);

        try {
            // ✅ Validate barn capacity vs total pigs
            $barnIds = $validated['barn_ids'];
            $barns = \App\Models\Barn::whereIn('id', $barnIds)->get();
            $totalBarnCapacity = $barns->sum('pig_capacity');
            $totalPigs = $validated['total_pig_amount'];

            if ($totalPigs > $totalBarnCapacity) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', "จำนวนหมู ({$totalPigs} ตัว) เกินความจุของเล้า ({$totalBarnCapacity} ตัว) โปรดเลือกเล้าเพิ่มเติม");
            }

            // Prepare batch data
            $batchData = [
                'farm_id' => $farm->id,
                'batch_code' => $validated['batch_code'],
                'note' => $validated['note'] ?? null,
            ];

            // Prepare entry data (use total_pig_price from form, already validated)
            $entryData = [
                'farm_id' => $farm->id,
                'pig_entry_date' => $validated['pig_entry_date'],
                'total_pig_amount' => $validated['total_pig_amount'],
                'total_pig_weight' => $validated['total_pig_weight'],
                'average_weight_per_pig' => $validated['average_weight_per_pig'],
                'average_price_per_pig' => $validated['average_price_per_pig'],
                'total_pig_price' => $validated['total_pig_price'],
                'payment_status' => 'pending',
            ];

            // Create Batch + Entry together
            $result = Batch::createWithPigEntry($batchData, $entryData);
            $batch = $result['batch'];
            $entry = $result['entry'];

            // ✅ Allocate pigs to selected barns
            $barnIds = $validated['barn_ids'];
            $totalPigsToAllocate = $validated['total_pig_amount'];

            // Get all barns (NOT pens) - we allocate per barn
            $barns = \App\Models\Barn::whereIn('id', $barnIds)
                ->with('pens')
                ->get();

            $pigsRemaining = $totalPigsToAllocate;

            // Allocate pigs to each barn sequentially
            foreach ($barns as $barn) {
                if ($pigsRemaining <= 0) break;

                $barnCapacity = $barn->pig_capacity; // Capacity per barn (e.g., 760)
                $pigsForThisBarn = min($pigsRemaining, $barnCapacity);

                // Get pens in this barn
                $pens = $barn->pens()->get();
                $penCount = $pens->count();

                if ($penCount > 0) {
                    // Distribute pigs evenly among pens in this barn
                    $pigsPerPen = intval($pigsForThisBarn / $penCount);
                    $remainingPigsForBarn = $pigsForThisBarn % $penCount;

                    foreach ($pens as $penIndex => $pen) {
                        // Allocate: distribute evenly, put remainder in last pen
                        $allocatedPigs = $pigsPerPen + ($penIndex === $penCount - 1 ? $remainingPigsForBarn : 0);

                        if ($allocatedPigs > 0) {
                            BatchPenAllocation::create([
                                'batch_id' => $batch->id,
                                'barn_id' => $barn->id,
                                'pen_id' => $pen->id,
                                'allocated_pigs' => $allocatedPigs,
                                'current_quantity' => $allocatedPigs,
                            ]);
                        }
                    }

                    $pigsRemaining -= $pigsForThisBarn;
                }
            }

            return redirect()
                ->route('batch.index')
                ->with('success', "สร้างรุ่น {$batch->batch_code} พร้อมบันทึกเข้าหมูสำเร็จ! สถานะ: {$batch->status}");

        } catch (\Exception $e) {
            Log::error('Error creating batch with entry: ' . $e->getMessage(), [
                'farm_id' => $farm->id,
                'request' => $request->all(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Update payment for batch
     */
    public function update_payment(Request $request, $id)
    {
        $result = \App\Services\PaymentService::recordCostPayment($request, $id);

        if (!$result['success']) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $result['message']);
        }

        // Show success toast notification
        return redirect()
            ->route('batch.index')
            ->with('success', $result['message'])
            ->with('showToast', true);
    }

    /**
     * ลบ Batch (soft delete - สามารถกู้คืนได้)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $batch = Batch::findOrFail($id);

            // ใช้ helper ลบ
            $result = \App\Helpers\BatchRestoreHelper::softDeleteBatch($id, 'User deletion from batch list');

            if (!$result['success']) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $result['message']
                    ], 400);
                }
                return redirect()
                    ->back()
                    ->with('error', $result['message']);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'] ?? 'ลบสำเร็จ'
                ], 200);
            }

            return redirect()
                ->route('batch.index')
                ->with('success', $result['message']);

        } catch (\Exception $e) {
            Log::error('Error deleting batch: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

        /**
     * กู้คืน Batch ที่ถูก soft delete
     */
    public function restore($id)
    {
        try {
            $result = \App\Helpers\BatchRestoreHelper::restoreBatch($id);

            if (!$result['success']) {
                return redirect()
                    ->back()
                    ->with('error', $result['message']);
            }

            return redirect()
                ->route('batch.index')
                ->with('success', $result['message']);

        } catch (\Exception $e) {
            Log::error('Error restoring batch: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * อัปเดต Status ของ Batch (AJAX)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $batch = Batch::findOrFail($id);

            $validated = $request->validate([
                'status' => 'required|in:draft,raising,selling,closed,cancelled'
            ]);

            // ตรวจสอบว่า batch ถูก soft delete หรือไม่
            if ($batch->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถแก้ไขรุ่นที่ถูกลบได้'
                ], 403);
            }

            $batch->update(['status' => $validated['status']]);

            Log::info('Batch status updated', [
                'batch_id' => $id,
                'batch_code' => $batch->batch_code,
                'new_status' => $validated['status'],
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "อัปเดตสถานะรุ่น {$batch->batch_code} เป็น " . $this->getStatusName($validated['status']) . " สำเร็จ"
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'ข้อมูลไม่ถูกต้อง: ' . implode(', ', array_merge(...array_values($e->errors())))
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating batch status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ส่วนช่วย: แปลง status เป็นชื่อแบบไทย
     */
    private function getStatusName($status)
    {
        return match($status) {
            'draft' => 'ร่าง',
            'raising' => 'กำลังเลี้ยง',
            'selling' => 'กำลังขาย',
            'closed' => 'เสร็จแล้ว',
            'cancelled' => 'ยกเลิก',
            default => $status
        };
    }

    /**
     * ดู batches ที่ถูก delete (archived)
     */
    public function archived(Request $request)
    {
        $farm = Farm::findOrFail($request->get('farm_id', 1));
        $deletedBatches = \App\Helpers\BatchRestoreHelper::getDeletedBatches($farm->id);
        $stats = \App\Helpers\BatchRestoreHelper::getBatchStatistics($farm->id);

        return view('admin.batch.archived', [
            'batches' => $deletedBatches,
            'stats' => $stats,
            'farm' => $farm,
        ]);
    }
}
