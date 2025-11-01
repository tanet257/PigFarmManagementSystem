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
use Carbon\Carbon;

class TreatmentController extends Controller
{
    public function index(Request $request)
    {
        $query = BatchTreatment::with(['dairy_record', 'pen', 'batch.farm'])
            ->orderBy('date', 'desc');

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('medicine_name', 'like', '%' . $request->search . '%')
                    ->orWhere('medicine_code', 'like', '%' . $request->search . '%')
                    ->orWhere('note', 'like', '%' . $request->search . '%')
                    ->orWhereHas('batch', function($sq) use ($request) {
                        $sq->where('batch_code', 'like', '%' . $request->search . '%');
                    });
            });
        }

        // Filter by date
        if ($request->filled('date')) {
            $date = Carbon::parse($request->date);
            $query->whereDate('date', $date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        // Filter by barn/pen
        if ($request->filled('pen_id')) {
            $query->where('pen_id', $request->pen_id);
        }

        $treatments = $query->paginate(10);
        $farms = Farm::all();
        $batches = Batch::where('status', '!=', 'เสร็จสิ้น')
            ->where('status', '!=', 'cancelled')
            ->get();
        $pens = Pen::all();
        
        return view('admin.treatments.index', compact('treatments', 'farms', 'batches', 'pens'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'status' => 'required|string',
            'note' => 'nullable|string',
            'date' => 'required|date_format:Y-m-d H:i:s'
        ]);

        DB::beginTransaction();
        try {
            $treatment = BatchTreatment::findOrFail($id);
            $dairyRecord = $treatment->dairy_record;

            // อัพเดทข้อมูลการรักษา
            $treatment->update([
                'quantity' => $validated['quantity'],
                'status' => $validated['status'],
                'note' => $validated['note'],
                'date' => $validated['date']
            ]);

            // อัพเดท dairy record ที่เกี่ยวข้อง
            if ($dairyRecord) {
                $dairyRecord->update([
                    'note' => $validated['note'],
                    'date' => $validated['date']
                ]);
            }

            DB::commit();
            return redirect()->route('treatments.index')->with('success', 'อัพเดทข้อมูลการรักษาเรียบร้อย');
        } catch (\Exception $e) {
            DB::rollBack();
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
}