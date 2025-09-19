<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Barn;
use App\Models\Pen;
use App\Models\Farm;
use App\Models\Batch;
use App\Models\BatchTreatment;
use App\Models\Cost;
use App\Models\PigSell;
use App\Models\Feeding;
use App\Models\PigDeath;
use App\Models\PigEntryRecord;
use App\Models\DairyRecord;
use App\Models\StoreHouse;
use App\Models\InventoryMovement;


class PigEntryController extends Controller
{


    public function view_pig_entry_record()
    {
        $pig_entry_records = PigEntryRecord::with(['batch', 'costs'])->get();
        return view('admin.pig_entry_records.index', compact('pig_entry_records'));
    }


    //add_pig_entry_record
    public function pig_entry_record()
    {
        $farms = Farm::all();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();
        return view('admin.record.pig_entry_record', compact('farms', 'batches'));
    }

    //upload_pig_entry_record
   public function upload_pig_entry_record(Request $request)
{
    try {
        // validate
        $validated = $request->validate([
            'batch_id' => [
                'required',
                Rule::exists('batches', 'id')->where(function ($query) use ($request) {
                    return $query->where('status', 'กำลังเลี้ยง')
                                 ->where('farm_id', $request->farm_id);
                }),
            ],
            'pig_entry_date'   => 'required|date',
            'total_pig_amount' => 'required|numeric|min:1',
            'total_pig_weight' => 'required|numeric|min:0',
            'total_pig_price'  => 'required|numeric|min:0',
            'excess_weight_cost' => 'nullable|numeric|min:0',
            'transport_cost'     => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $batch = Batch::findOrFail($validated['batch_id']);

        // ✅ บันทึก pig_entry_record (ไม่เก็บไฟล์)
        $pigEntry = PigEntryRecord::create([
            'batch_id' => $batch->id,
            'farm_id'  => $batch->farm_id,
            'pig_entry_date'   => $validated['pig_entry_date'],
            'total_pig_amount' => $validated['total_pig_amount'],
            'total_pig_weight' => $validated['total_pig_weight'],
            'total_pig_price'  => $validated['total_pig_price'],
            'note' => $validated['note'] ?? null,
        ]);

        // ✅ อัปโหลดไฟล์ครั้งเดียว
        $filename = null;
        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('receipt_files'), $filename);
        }

        // ✅ สร้าง row ค่าลูกหมู
        $cost = Cost::create([
            'farm_id' => $batch->farm_id,
            'batch_id' => $batch->id,
            'cost_type' => 'piglet',
            'quantity' => $validated['total_pig_amount'],
            'price_per_unit' => $validated['total_pig_price'] / $validated['total_pig_amount'],
            'total_price' => $validated['total_pig_price'],
            'note' => 'ค่าลูกหมู',
            'receipt_file' => $filename,
            // 👇 transport_cost ตรงนี้
            'transport_cost' => $validated['transport_cost'] ?? 0,
        ]);

        // ✅ บันทึกค่าน้ำหนักเกิน (row แยก)
        if (!empty($validated['excess_weight_cost']) && $validated['excess_weight_cost'] > 0) {
            Cost::create([
                'farm_id' => $batch->farm_id,
                'batch_id' => $batch->id,
                'cost_type' => 'excess_weight',
                'quantity' => 1,
                'price_per_unit' => $validated['excess_weight_cost'],
                'total_price' => $validated['excess_weight_cost'],
                'note' => 'ค่าน้ำหนักส่วนเกิน',
                'receipt_file' => $filename,
            ]);
        }

        // ✅ อัปเดต totals ของ batch
        $batch->total_pig_amount = ($batch->total_pig_amount ?? 0) + $validated['total_pig_amount'];
        $batch->total_pig_weight = ($batch->total_pig_weight ?? 0) + $validated['total_pig_weight'];
        $batch->total_pig_price  = ($batch->total_pig_price ?? 0)  + $validated['total_pig_price'];
        $batch->save();

        return redirect()->back()->with('success', 'เพิ่มหมูเข้า + บันทึกค่าใช้จ่ายเรียบร้อย');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}



    //--------------------------------------- Index ------------------------------------------//
    //Index Pig Entry Record
    public function indexPigEntryRecord(Request $request)
{
    $farms = Farm::all();
    $batches = Batch::select('id', 'batch_code', 'farm_id')->get();

    $query = PigEntryRecord::with(['farm', 'batch.costs']); // <-- สำคัญ!

    // search
    if ($request->filled('search')) {
        $query->where('note', 'like', '%' . $request->search . '%');
    }

    // filter farm
    if ($request->filled('farm_id')) {
        $query->where('farm_id', $request->farm_id);
    }

    // sort
    $sortBy = $request->get('sort_by', 'updated_at');
    $sortOrder = $request->get('sort_order', 'desc');

    if (in_array($sortBy, ['pig_entry_date', 'total_pig_amount', 'total_pig_price', 'updated_at'])) {
        $query->orderBy($sortBy, $sortOrder);
    } else {
        $query->orderBy('updated_at', 'desc');
    }

    // pagination
    $perPage = $request->get('per_page', 10);
    $pigEntryRecords = $query->paginate($perPage);

    return view('admin.pig_entry_records.index', compact('farms', 'batches', 'pigEntryRecords'));
}





    // Edit PigEntryRecord
    public function editPigEntryRecord(Request $request)
    {
        $farms = Farm::all();
        $pigEntryRecords = PigEntryRecord::paginate(10);

        // ส่งไปหน้า index พร้อม modal แก้ไข
        return view('admin.pig_entry_records.index', compact('farms', 'pigEntryRecords'));
    }

    // Update PigEntryRecord
    public function updatePigentryrecord(Request $request, $id)
{
    try {
        $validated = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'pig_entry_date' => 'required|date',
            'total_pig_amount' => 'required|numeric|min:1',
            'total_pig_weight' => 'required|numeric|min:0',
            'total_pig_price' => 'required|numeric|min:0',
            'excess_weight_cost' => 'nullable|numeric|min:0',
            'transport_cost' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $record = PigEntryRecord::findOrFail($id);
        $batch = Batch::findOrFail($validated['batch_id']);

        // อัปโหลดไฟล์ใหม่ถ้ามี
        $filename = $record->receipt_file;
        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('receipt_files'), $filename);
        }

        // อัปเดตข้อมูล PigEntryRecord
        $record->update([
            'batch_id' => $batch->id,
            'farm_id' => $batch->farm_id,
            'pig_entry_date' => $validated['pig_entry_date'],
            'total_pig_amount' => $validated['total_pig_amount'],
            'total_pig_weight' => $validated['total_pig_weight'],
            'total_pig_price' => $validated['total_pig_price'],
            'note' => $validated['note'] ?? null,
            'receipt_file' => $filename,
        ]);

        // อัปเดต Cost ค่าลูกหมู
        Cost::updateOrCreate(
            ['batch_id' => $batch->id, 'cost_type' => 'piglet'],
            [
                'farm_id' => $batch->farm_id,
                'quantity' => $validated['total_pig_amount'],
                'price_per_unit' => $validated['total_pig_price'] / $validated['total_pig_amount'],
                'total_price' => $validated['total_pig_price'],
                'note' => 'ค่าลูกหมู',
                'receipt_file' => $filename,
            ]
        );

        // อัปเดตค่าน้ำหนักส่วนเกิน
        if (!empty($validated['excess_weight_cost']) && $validated['excess_weight_cost'] > 0) {
            Cost::updateOrCreate(
                ['batch_id' => $batch->id, 'cost_type' => 'excess_weight'],
                [
                    'farm_id' => $batch->farm_id,
                    'quantity' => 1,
                    'price_per_unit' => $validated['excess_weight_cost'],
                    'total_price' => $validated['excess_weight_cost'],
                    'note' => 'ค่าน้ำหนักส่วนเกิน',
                    'receipt_file' => $filename,
                ]
            );
        } else {
            Cost::where('batch_id', $batch->id)->where('cost_type', 'excess_weight')->delete();
        }

        // อัปเดตค่าขนส่ง
        Cost::where('batch_id', $batch->id)->update(['transport_cost' => $validated['transport_cost'] ?? 0]);

        return redirect()->back()->with('success', 'แก้ไขข้อมูลเรียบร้อย');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}

    // Delete PigEntryRecord
    public function deletePigEntryRecord($id)
    {
        $pigEntryRecord = PigEntryRecord::find($id);
        if (!$pigEntryRecord) {
            return redirect()->back()->with('error', 'ไม่พบรายการที่ต้องการลบ');
        }

        $pigEntryRecord->delete();
        return redirect()->route('pig_entry_records.index')->with('success', 'ลบรายการเรียบร้อยแล้ว');
    }

    //--------------------------------------- EXPORT ------------------------------------------//

    // Export PigEntryRecord to PDF
    public function exportPigEntryPdf()
    {
        $farms = Farm::all();
        $pigEntryRecords = PigEntryRecord::with(['farm', 'batch'])->get();

        $pdf = Pdf::loadView('admin.pig_entry_records.exports.pigentryrecord_pdf', compact('farms', 'pigEntryRecords'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Sarabun',
            ]);

        $filename = "pig_entry_records_" . date('Y-m-d_H-i-s') . ".pdf";

        return $pdf->download($filename);
    }

    // Export PigEntryRecord to CSV
    public function exportPigEntryCsv()
    {
        $pigEntryRecords = PigEntryRecord::with(['farm', 'batch'])->get();

        $filename = "pig_entry_records_" . date('Y-m-d_H-i-s') . ".csv";

        return response()->streamDownload(function () use ($pigEntryRecords) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Farm',
                'Batch Code',
                'Date',
                'Total Pigs',
                'Total Weight',
                'Total Price',
                'Note'
            ]);

            foreach ($pigEntryRecords as $record) {
                fputcsv($handle, [
                    $record->farm->farm_name ?? '-',
                    $record->batch->batch_code ?? '-',
                    $record->pig_entry_date,
                    $record->total_pig_amount,
                    $record->total_pig_weight,
                    $record->total_pig_price,
                    $record->note ?? '-',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
