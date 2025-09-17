<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Farm;
use App\Models\Batch;

use App\Models\Cost;

use App\Models\StoreHouse;
use App\Models\InventoryMovement;


class StoreHouseController extends Controller
{

    //--------------------------------------- CREATE ------------------------------------------//

    //add_storehouse_record
    public function store_house_record(Request $request)
    {
        $farms = Farm::all();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();
        $storehouses = Storehouse::all();

        // unit type
        $unitsByType = [
            'feed' => ['กระสอบ'],
            'medicine' => ['ขวด', 'กล่อง', 'ซอง'],
            'wage' => ['บาท'],
            'electric_bill' => ['บาท'],
            'water_bill' => ['บาท'],
        ];

        return view('admin.record.store_house_record', compact('farms', 'batches', 'storehouses', 'unitsByType'));
    }

    //upload_store_house_record
    public function upload_store_house_record(Request $request)
    {
        try {
            // กำหนด units ตาม type
            $unitsByType = [
                'feed' => ['kg', 'กระสอบ'],
                'medicine' => ['ขวด', 'กล่อง', 'ซอง'],
                'wage' => ['บาท'],
                'electric_bill' => ['บาท'],
                'water_bill' => ['บาท'],
            ];

            // แปลง empty string เป็น null (unit, item_code, item_name)
            $input = $request->all();
            foreach (['unit', 'item_code', 'item_name'] as $field) {
                if (isset($input[$field]) && $input[$field] === '') $input[$field] = null;
            }
            $request->merge($input);

            // กำหนด allowed units ตาม item_type
            $allowedUnits = $unitsByType[$request->input('item_type')] ?? [];

            // Validate
            $validated = $request->validate([
                'batch_id' => [
                    'required',
                    Rule::exists('batches', 'id')->where(function ($query) use ($request) {
                        return $query->where('status', 'กำลังเลี้ยง')
                            ->where('farm_id', $request->farm_id);
                    }),
                ],
                'date' => ['required', function ($attribute, $value, $fail) use ($request) {
                    try {
                        if (in_array($request->input('item_type'), ['wage', 'electric_bill', 'water_bill'])) {
                            // monthly row: m/Y
                            Carbon::createFromFormat('m/Y', $value);
                        } else {
                            // feed/medicine: d/m/Y H:i
                            $dt = Carbon::createFromFormat('d/m/Y H:i', $value);
                            if ($dt->isFuture()) $fail('วันที่ต้องไม่อยู่ในอนาคต');
                        }
                    } catch (\Exception $e) {
                        $fail('รูปแบบวันที่ไม่ถูกต้อง');
                    }
                }],
                'item_type'      => 'required|string',
                'item_code'      => 'nullable|string',
                'item_name'      => 'nullable|string',
                'stock'          => 'nullable|integer|min:1',
                'price_per_unit' => 'nullable|numeric|min:0',
                'unit'           => ['nullable', Rule::in($allowedUnits)],
                'transport_cost' => 'nullable|numeric|min:0',
                'receipt_file'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'note'           => 'nullable|string',
            ]);

            $batch = Batch::findOrFail($validated['batch_id']);

            // แยกกรณี feed/medicine vs wage/electric_bill/water_bill
            if (!in_array($validated['item_type'], ['wage', 'electric_bill', 'water_bill'])) {
                // feed/medicine
                $dt = Carbon::createFromFormat('d/m/Y H:i', $validated['date']);
                $formattedDate = $dt->format('Y-m-d H:i:s');

                // หา StoreHouse ถ้ามีอยู่แล้ว
                $storehouse = StoreHouse::firstOrNew([
                    'farm_id'   => $batch->farm_id,
                    'item_code' => $validated['item_code'] ?? null,
                ]);

                // อัปเดทข้อมูล
                $storehouse->item_name = $validated['item_name'] ?? $validated['item_code'] ?? 'Unknown Item';
                $storehouse->item_type = $validated['item_type'];
                $storehouse->unit = $validated['unit'];
                $storehouse->status = 'available';
                $storehouse->stock = ($storehouse->stock ?? 0) + ($validated['stock'] ?? 0);

                // อัปโหลด receipt ถ้ามี
                if (!empty($validated['receipt_file'])) {
                    $file = $validated['receipt_file'];
                    $filename = time() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('receipt_files'), $filename);
                    $storehouse->receipt_file = $filename;
                }

                $storehouse->save();

                // คำนวณค่าใช้จ่าย
                $total = ($validated['stock'] ?? 0) * ($validated['price_per_unit'] ?? 0) + ($validated['transport_cost'] ?? 0);

                Cost::create([
                    'farm_id'        => $batch->farm_id,
                    'batch_id'       => $batch->id,
                    'date'           => $formattedDate,
                    'cost_type'      => $validated['item_type'],
                    'item_code'      => $validated['item_code'] ?? null,
                    'quantity'       => $validated['stock'],
                    'price_per_unit' => $validated['price_per_unit'] ?? 0,
                    'transport_cost' => $validated['transport_cost'] ?? 0,
                    'unit'           => $validated['unit'] ?? null,
                    'total_price'    => $total,
                    'note'           => $validated['note'] ?? null,
                    'receipt_file'   => $storehouse->receipt_file ?? null,
                ]);

                // log movement
                InventoryMovement::create([
                    'storehouse_id' => $storehouse->id,
                    'change_type'   => 'in',
                    'quantity'      => $validated['stock'],
                    'note'          => 'เพิ่มสินค้าเข้าคลังจากการบันทึกค่าใช้จ่าย (Batch: ' . $batch->id . ')',
                    'date'          => $formattedDate,
                ]);
            } else {
                // wage/electric_bill/water_bill (monthly row)
                $dt = Carbon::createFromFormat('m/Y', $validated['date'])->startOfMonth();
                $formattedDate = $dt->format('Y-m-d');

                $total = $validated['price_per_unit'] ?? 0;

                $filename = null;
                if (!empty($validated['receipt_file'])) {
                    $file = $validated['receipt_file'];
                    $filename = time() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('receipt_files'), $filename);
                }

                Cost::create([
                    'farm_id'        => $batch->farm_id,
                    'batch_id'       => $batch->id,
                    'date'           => $formattedDate,
                    'cost_type'      => $validated['item_type'],
                    'item_code'      => $validated['item_code'] ?? null,
                    'price_per_unit' => $validated['price_per_unit'] ?? 0,

                    'unit'           => $validated['unit'] ?? null,
                    'total_price'    => $total,
                    'note'           => $validated['note'] ?? null,
                    'receipt_file'   => $filename,
                ]);
            }

            return redirect()->back()->with('success', 'เพิ่มสินค้าเข้าคลัง + บันทึกค่าใช้จ่าย + อัปเดตสต็อกเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    //--------------------------------------- Index ------------------------------------------//

    // Index Storehouse
    // Index Storehouse
    public function indexStorehouse(Request $request)
    {
        $farms = Farm::all();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();

        // query จาก farm และ latestCost
        $query = StoreHouse::with(['farm', 'latestCost']);

        // search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('item_name', 'like', '%' . $request->search . '%')
                    ->orWhere('item_code', 'like', '%' . $request->search . '%');
            });
        }

        // filter farm
        if ($request->filled('farm_id')) {
            $query->where('farm_id', $request->farm_id);
        }

        // sort
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'date') {
            // sort ตาม latestCost->date
            $query->whereHas('latestCost', function ($q) use ($sortOrder) {
                $q->orderBy('date', $sortOrder);
            });
        } elseif (in_array($sortBy, ['stock', 'total_price', 'updated_at'])) {
            // stock หรือ updated_at ของ storehouse
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        // pagination
        $perPage = $request->get('per_page', 10);
        $storehouses = $query->paginate($perPage);

        return view('admin.storehouses.index', compact('farms', 'batches', 'storehouses'));
    }


    //Edit storehouse
    public function editStoreHouse($id)
    {
        //$storehouse = StoreHouse::with(['farm', 'barn', 'pen'])->findOrFail($id);
        $storehouse = StoreHouse::findOrFail($id);
        if (!$storehouse) {
            return redirect()->back()->with('error', 'ไม่พบรุ่นที่ต้องการแก้ไข');
        } else {
            return view('admin.storehouses.edit', compact('storehouse'));
        }
    }

    //Update storehouse
    public function updateStoreHouse(Request $request, $id)
    {
        $storehouse = StoreHouse::findOrFail($id);

        $validated = $request->validate([
            'batch_code' => 'required|string|max:255',
            'status'     => 'required|string',
            'note'       => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $storehouse->update($validated);

        return redirect()->route('storehouses.index')->with('success', 'แก้ไข StoreHouse สำเร็จ');
    }

    //Delete storehous
    public function deleteStoreHouse($id)
    {
        $storehouse = StoreHouse::find($id);
        if (!$storehouse) {
            return redirect()->back()->with('error', 'ไม่พบรุ่นที่ต้องการลบ');
        }

        $storehouse->delete();
        return redirect()->route('storehouses.index')->with('success', 'ลบรุ่นหมูเรียบร้อยแล้ว');
    }

    //--------------------------------------- EXPORT ------------------------------------------//

    // Export storehouse to PDF
    public function exportPdf()
    {
        $storehouses = StoreHouse::all();

        // ตั้งค่า dompdf options
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true); // รองรับ HTML5
        $options->set('isRemoteEnabled', true);     // โหลดไฟล์ font จาก URL ได้
        $options->set('defaultFont', 'Sarabun'); // ตั้ง default font

        // สร้าง PDF
        $pdf = Pdf::loadView('admin.exports.storehouses_pdf', compact('storehouses'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Sarabun',
            ]);


        // ตั้งชื่อไฟล์
        $filename = "storehouses_export_" . date('Y-m-d_H-i-s') . ".pdf";

        return $pdf->download($filename);
    }

    //export storehouse to csv
    public function exportCsv()
    {
        $storehouses = StoreHouse::all();

        $filename = "storehouses" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['Storehouse Code', 'Farm', 'Barn', 'Pen', 'Status', 'Start Date', 'End Date']);

        foreach ($storehouses as $storehouse) {
            fputcsv($handle, [
                $storehouse->batch_code,
                $storehouse->farm->name ?? '-',
                $storehouse->barn->name ?? '-',
                $storehouse->pen->name ?? '-',
                $storehouse->status,
                $storehouse->start_date,
                $storehouse->end_date,
            ]);
        }

        fclose($handle);

        return response()->streamDownload(function () use ($storehouses) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Batch Code', 'Farm', 'Barn', 'Pen', 'Status', 'Start Date', 'End Date']);
            foreach ($storehouses as $storehouse) {
                fputcsv($handle, [
                    $storehouse->batch_code,
                    $storehouse->farm->name ?? '-',
                    $storehouse->barn->name ?? '-',
                    $storehouse->pen->name ?? '-',
                    $storehouse->status,
                    $storehouse->start_date,
                    $storehouse->end_date,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
