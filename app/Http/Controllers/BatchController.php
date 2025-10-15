<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Farm;
use App\Models\Batch;
use App\Models\Barn;
use App\Models\Pen;




class BatchController extends Controller
{
    //--------------------------------------- CREATE ------------------------------------------//

    //add_batch
    /* public function add_batch()
    {
        $farms = Farm::all();
        $barns = Barn::all();
        $pens = Pen::all();
        return view('admin.add.add_batch', compact('farms', 'barns', 'pens'));
    }

    //upload_batch
    public function upload_batch(Request $request)
    {
        try {
            //validate
            $validated = $request->validate([
                // ทำให้ batch_id ไม่ซ้ำกันภายใน farm_id
                'batch_id' => 'required|unique:batches,batch_id',

                //'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $data = new Batch;
            $data->farm_id = $validated['farm_id'];

            //unique
            $data->batch_code = $validated['batch_code'];

            $data->status = $validated['status'] ?? 'กำลังเลี้ยง';
            $data->note = $validated['note'] ?? null;

            $data->start_date = Carbon::now(); // เวลาปัจจุบัน
            $data->end_date = $validated['end_date'];

            $data->save();

            return redirect()->back()->with('success', 'เพิ่มรุ่นเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มรุ่น: ' . $e->getMessage());
        }
    }
    */

    //--------------------------------------- Index ------------------------------------------//

    //Index batch
    public function indexBatch(Request $request)
    {
        $query = Batch::with('farm.barns.pens');

        if ($request->filled('search')) {
            $query->where('batch_code', 'like', '%' . $request->search . '%');
        }

        // Date Filter
        if ($request->filled('selected_date')) {
            $date = Carbon::now();
            switch ($request->selected_date) {
                case 'today':
                    $query->whereDate('created_at', $date);
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [$date->startOfWeek(), $date->copy()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', $date->year);
                    break;
            }
        }

        if ($request->filled('farm_id')) {
            $query->where('farm_id', $request->farm_id);
        }

        // Batch Filter
        if ($request->filled('batch_id')) {
            $query->where('id', $request->batch_id);
        }

        if ($request->filled('sort_by')) {
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($request->sort_by, $sortOrder);
        }

        $perPage = $request->get('per_page', 10);
        $batches = $query->paginate($perPage);

        // --- คำนวณค่าเฉลี่ยน้ำหนักต่อตัว ---
        foreach ($batches as $batch) {
            $batch->avg_pig_weight = $batch->total_pig_amount > 0
                ? $batch->total_pig_weight / $batch->total_pig_amount
                : 0;
        }

        $farms = Farm::all();
        $allBatches = Batch::select('id', 'batch_code', 'farm_id')->get();

        return view('admin.batches.index', compact('batches', 'farms', 'allBatches'));
    }


    //Create Batch
    public function createBatch(Request $request)
    {
        try {
            //validate
            $validated = $request->validate([
                'farm_id'  => 'required|exists:farms,id',
                'batch_code' => 'required|unique:batches,batch_code',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $data = new Batch;
            $data->farm_id = $validated['farm_id'];

            //unique
            $data->batch_code = $validated['batch_code'];

            $data->status = $validated['status'] ?? 'กำลังเลี้ยง';
            $data->note = $validated['note'] ?? null;

            $data->start_date = Carbon::now(); // เวลาปัจจุบัน
            $data->end_date = $validated['end_date'] ?? null;

            $data->save();

            return redirect()->back()->with('success', 'เพิ่มรุ่นเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มรุ่น: ' . $e->getMessage());
        }
    }

    //Edit batch
    public function editBatch(Request $request)
    {
        $farms = Farm::all();
        $batch = Batch::all();

        return view('admin.batches.edit', compact('farm', 'batch'));
    }

    //Update batch
    public function updateBatch(Request $request, $id)
    {
        $batch = Batch::findOrFail($id);

        $validated = $request->validate([
            'batch_code' => 'required|string|max:255',
            'status'     => 'required|string',
            'note'       => 'nullable|string',
            'start_date' => 'nullable|date',
        ]);

        // เช็คการเปลี่ยนสถานะ
        $oldStatus = $batch->status;
        $newStatus = $request->status;

        $batch->status = $newStatus;

        if ($oldStatus != 'เสร็จสิ้น' && $newStatus == 'เสร็จสิ้น') {
            // ถ้าเปลี่ยนจากไม่เสร็จสิ้น → เสร็จสิ้น
            $batch->end_date = now(); // อัปเดตเป็นเวลาปัจจุบัน
        }

        $batch->update($validated);

        return redirect()->route('batches.index')->with('success', 'แก้ไข Batch สำเร็จ');
    }

    //Delete batch
    public function deleteBatch($id)
    {
        $batch = Batch::find($id);
        if (!$batch) {
            return redirect()->back()->with('error', 'ไม่พบรุ่นที่ต้องการลบ');
        }

        $batch->delete();
        return redirect()->route('batches.index')->with('success', 'ลบรุ่นหมูเรียบร้อยแล้ว');
    }

    //--------------------------------------- EXPORT ------------------------------------------//

    // Export batch to PDF
    public function exportPdf()
    {
        $batches = Batch::all();

        // ตั้งค่า dompdf options
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true); // รองรับ HTML5
        $options->set('isRemoteEnabled', true);     // โหลดไฟล์ font จาก URL ได้
        $options->set('defaultFont', 'Sarabun'); // ตั้ง default font

        // สร้าง PDF
        $pdf = Pdf::loadView('admin.batches.exports.pdf', compact('batches'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Sarabun',
            ]);


        // ตั้งชื่อไฟล์
        $filename = "batches_export_" . date('Y-m-d_H-i-s') . ".pdf";

        return $pdf->download($filename);
    }

    //export batch to csv
    public function exportCsv()
    {
        $batches = Batch::all();

        $filename = "batches_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['Batch Code', 'Farm', 'Barn', 'Pen', 'Status', 'Start Date', 'End Date']);

        foreach ($batches as $batch) {
            fputcsv($handle, [
                $batch->batch_code,
                $batch->farm->name ?? '-',
                $batch->barn->name ?? '-',
                $batch->pen->name ?? '-',
                $batch->status,
                $batch->start_date,
                $batch->end_date,
            ]);
        }

        fclose($handle);

        return response()->streamDownload(function () use ($batches) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Batch Code', 'Farm', 'Barn', 'Pen', 'Status', 'Start Date', 'End Date']);
            foreach ($batches as $batch) {
                fputcsv($handle, [
                    $batch->batch_code,
                    $batch->farm->name ?? '-',
                    $batch->barn->name ?? '-',
                    $batch->pen->name ?? '-',
                    $batch->status,
                    $batch->start_date,
                    $batch->end_date,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
