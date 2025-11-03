<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Farm;
use App\Models\Batch;
use App\Models\Barn;
use App\Models\Pen;
use App\Models\BatchTreatment;
use App\Models\DairyRecord;
use App\Models\StoreHouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TreatmentController extends Controller
{
    public function index(Request $request)
    {
        $query = BatchTreatment::with(['batch.farm', 'treatmentDetails.pen.barn', 'treatmentDetails.barn'])
            ->orderBy('planned_start_date', 'desc');

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('medicine_name', 'like', '%' . $request->search . '%')
                    ->orWhere('medicine_code', 'like', '%' . $request->search . '%')
                    ->orWhere('disease_name', 'like', '%' . $request->search . '%')
                    ->orWhere('note', 'like', '%' . $request->search . '%')
                    ->orWhereHas('batch', function($sq) use ($request) {
                        $sq->where('batch_code', 'like', '%' . $request->search . '%');
                    });
            });
        }

        // Filter by date
        if ($request->filled('selected_date')) {
            $date = Carbon::now();
            if ($request->selected_date === 'today') {
                $query->whereDate('planned_start_date', $date->toDateString());
            } elseif ($request->selected_date === 'this_week') {
                $query->whereBetween('planned_start_date', [
                    $date->copy()->startOfWeek(),
                    $date->copy()->endOfWeek()
                ]);
            } elseif ($request->selected_date === 'this_month') {
                $query->whereMonth('planned_start_date', $date->month)
                    ->whereYear('planned_start_date', $date->year);
            } elseif ($request->selected_date === 'this_year') {
                $query->whereYear('planned_start_date', $date->year);
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('treatment_status', $request->status);
        }

        // Filter by farm
        if ($request->filled('farm_id')) {
            $query->whereHas('batch', function($q) use ($request) {
                $q->where('farm_id', $request->farm_id);
            });
        }

        // Filter by batch
        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        $treatments = $query->paginate($request->get('per_page', 10));
        $farms = Farm::all();
        $batches = Batch::where('status', '!=', 'เสร็จสิ้น')
            ->where('status', '!=', 'cancelled')
            ->get();

        // ✅ Fetch medicines for modal
        $medicines = \App\Models\StoreHouse::where('item_type', 'medicine')
            ->where('status', '!=', 'cancelled')
            ->get();

        return view('admin.treatments.index', compact('treatments', 'farms', 'batches', 'medicines'));
    }

    public function show($id)
    {
        try {
            $treatment = BatchTreatment::with(['treatmentDetails.pen.barn', 'treatmentDetails.barn', 'batch.farm'])
                ->findOrFail($id);

            // Transform treatment details
            $details = [];
            if ($treatment->treatmentDetails) {
                foreach ($treatment->treatmentDetails as $detail) {
                    $details[] = [
                        'id' => $detail->id,
                        'pen_id' => $detail->pen_id,
                        'barn_id' => $detail->barn_id,
                        'quantity_used' => $detail->quantity_used,
                        'treatment_date' => $detail->treatment_date,
                        'note' => $detail->note,
                        'pen' => $detail->pen ? [
                            'id' => $detail->pen->id,
                            'pen_code' => $detail->pen->pen_code,
                            'current_quantity' => $detail->pen->current_quantity ?? 0
                        ] : null,
                        'barn' => $detail->barn ? [
                            'id' => $detail->barn->id,
                            'barn_code' => $detail->barn->barn_code
                        ] : null,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $treatment->id,
                    'batch_id' => $treatment->batch_id,
                    'batch_code' => ($treatment->batch ? $treatment->batch->batch_code : '-') ?? '-',
                    'pen_id' => $treatment->pen_id,
                    'farm_id' => ($treatment->batch ? $treatment->batch->farm_id : null) ?? null,
                    'disease_name' => $treatment->disease_name ?? '-',
                    'medicine_name' => $treatment->medicine_name ?? '-',
                    'medicine_code' => $treatment->medicine_code ?? '-',
                    'dosage' => $treatment->dosage ?? '-',
                    'frequency' => $treatment->frequency ?? '-',
                    'quantity' => $treatment->quantity ?? 0,
                    'unit' => $treatment->unit ?? '-',
                    'planned_start_date' => $treatment->planned_start_date ?? '-',
                    'planned_duration' => $treatment->planned_duration ?? 0,
                    'actual_start_date' => $treatment->actual_start_date ?? null,
                    'actual_end_date' => $treatment->actual_end_date ?? null,
                    'treatment_status' => $treatment->treatment_status ?? 'pending',
                    'treatment_level' => $treatment->treatment_level ?? 'pen',
                    'note' => $treatment->note ?? '',
                    'batch' => $treatment->batch ? [
                        'id' => $treatment->batch->id,
                        'batch_code' => $treatment->batch->batch_code,
                        'farm_id' => $treatment->batch->farm_id,
                        'farm' => $treatment->batch->farm ? [
                            'id' => $treatment->batch->farm->id,
                            'farm_name' => $treatment->batch->farm->farm_name
                        ] : null,
                    ] : null,
                    'details' => $details,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Treatment show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลการรักษา'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'medicine_code' => 'nullable|string',
            'treatment_status' => 'required|string|in:pending,ongoing,completed,stopped',
            'note' => 'nullable|string',
            'actual_start_date' => 'nullable|date_format:Y-m-d',
            'actual_end_date' => 'nullable|date_format:Y-m-d'
        ]);

        DB::beginTransaction();
        try {
            $treatment = BatchTreatment::findOrFail($id);

            // อัพเดทข้อมูลการรักษา
            $treatment->update([
                'quantity' => $validated['quantity'],
                'medicine_code' => $validated['medicine_code'] ?? $treatment->medicine_code,
                'treatment_status' => $validated['treatment_status'],
                'note' => $validated['note'],
                'actual_start_date' => $validated['actual_start_date'],
                'actual_end_date' => $validated['actual_end_date']
            ]);

            // สร้าง หรือ อัพเดท dairy_record
            if (!$treatment->dairy_record) {
                // สร้าง dairy_record ใหม่ ถ้ายังไม่มี
                $dairyRecord = DairyRecord::create([
                    'batch_id' => $treatment->batch_id,
                    'farm_id' => $treatment->batch->farm_id,
                    'date' => $validated['actual_start_date'] ?? now()->toDateString(),
                    'note' => "บันทึกจากการรักษา: {$treatment->disease_name}",
                ]);
                $treatment->update(['dairy_record_id' => $dairyRecord->id]);
            } else {
                // อัพเดท dairy_record ที่มีอยู่
                $treatment->dairy_record->update([
                    'note' => "บันทึกจากการรักษา: {$treatment->disease_name} - {$validated['note']}",
                    'date' => $validated['actual_start_date'] ?? $treatment->dairy_record->date
                ]);
            }

            DB::commit();
            return redirect()->route('treatments.index')->with('success', 'อัพเดทข้อมูลการรักษาเรียบร้อย');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Treatment update error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $treatment = BatchTreatment::findOrFail($id);
            $dairyRecord = $treatment->dairy_record;

            // ลบข้อมูลการรักษา
            $treatment->delete();

            // ถ้า dairy record ไม่มีข้อมูลอื่นๆ แล้ว ให้ลบทิ้ง
            if ($dairyRecord &&
                $dairyRecord->dairy_storehouse_uses()->count() === 0 &&
                $dairyRecord->batch_treatments()->count() === 0 &&
                $dairyRecord->pig_deaths()->count() === 0) {
                $dairyRecord->delete();
            }

            DB::commit();
            return redirect()->route('treatments.index')->with('success', 'ลบข้อมูลการรักษาเรียบร้อย');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Export treatments to CSV
     */
    public function exportCsv(Request $request)
    {
        $query = BatchTreatment::with(['batch.farm', 'treatmentDetails', 'inventoryMovements'])
            ->orderBy('planned_start_date', 'desc');

        // Apply filters if provided
        if ($request->filled('farm_id')) {
            $query->whereHas('batch', function($q) use ($request) {
                $q->where('farm_id', $request->farm_id);
            });
        }

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->filled('status')) {
            $query->where('treatment_status', $request->status);
        }

        $treatments = $query->get();

        // Create CSV
        $fileName = 'treatments_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($treatments) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Headers
            fputcsv($file, [
                'รหัสการรักษา',
                'รหัสรุ่น',
                'ฟาร์ม',
                'โรค/อาการ',
                'ยา/วัคซีน',
                'โดส',
                'ความถี่',
                'สถานะ',
                'วันที่วางแผน',
                'ระยะเวลา (วัน)',
                'จำนวนคอก',
                'ยาใช้ทั้งหมด (หน่วย)',
                'หมายเหตุ',
                'วันที่บันทึก',
            ]);

            // Data rows
            foreach ($treatments as $treatment) {
                $detailCount = $treatment->treatmentDetails ? count($treatment->treatmentDetails) : 0;
                $totalQuantity = $treatment->inventoryMovements ?
                    $treatment->inventoryMovements->sum('quantity') : 0;

                fputcsv($file, [
                    $treatment->id,
                    $treatment->batch ? $treatment->batch->batch_code : '-',
                    $treatment->batch && $treatment->batch->farm ? $treatment->batch->farm->farm_name : '-',
                    $treatment->disease_name ?? '-',
                    $treatment->medicine_name ?? '-',
                    $treatment->dosage ?? '-',
                    $this->getFrequencyLabel($treatment->frequency),
                    $this->getStatusLabel($treatment->treatment_status),
                    $treatment->planned_start_date ? \Carbon\Carbon::parse($treatment->planned_start_date)->format('d/m/Y') : '-',
                    $treatment->planned_duration ?? 0,
                    $detailCount,
                    $totalQuantity,
                    $treatment->note ?? '-',
                    $treatment->created_at ? \Carbon\Carbon::parse($treatment->created_at)->format('d/m/Y H:i') : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper function to get frequency label
     */
    private function getFrequencyLabel($frequency)
    {
        $labels = [
            'once' => '1 ครั้ง',
            'daily' => 'วันละ 1 ครั้ง',
            'twice_daily' => 'วันละ 2 ครั้ง',
            'every_other_day' => 'วันเว้นวัน',
            'weekly' => 'สัปดาห์ละ 1 ครั้ง'
        ];
        return $labels[$frequency] ?? $frequency ?? '-';
    }

    /**
     * Helper function to get status label
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'รอดำเนินการ',
            'ongoing' => 'กำลังดำเนินการ',
            'completed' => 'เสร็จสิ้น',
            'stopped' => 'หยุดการรักษา'
        ];
        return $labels[$status] ?? $status ?? '-';
    }
}
