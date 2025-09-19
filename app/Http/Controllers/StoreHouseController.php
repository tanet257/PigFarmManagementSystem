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

    // upload_store_house_record
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
                'farm_id'   => 'required|exists:farms,id',
                'batch_id'  => 'required|exists:batches,id',
                'date' => ['required', function ($attribute, $value, $fail) use ($request) {
                    try {
                        if (in_array($request->input('item_type'), ['wage', 'electric_bill', 'water_bill'])) {
                            Carbon::createFromFormat('m/Y', $value);
                        } else {
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

            if (!in_array($validated['item_type'], ['wage', 'electric_bill', 'water_bill'])) {
                // feed/medicine
                $dt = Carbon::createFromFormat('d/m/Y H:i', $validated['date']);
                $formattedDate = $dt->format('Y-m-d H:i:s');

                // หา StoreHouse ที่มีอยู่แล้ว
                $storehouse = StoreHouse::where('farm_id', $validated['farm_id'])
                    ->where('item_code', $validated['item_code'])
                    ->first();

                if (!$storehouse) {
                    return redirect()->back()->with('error', 'ไม่พบสินค้านี้ในคลัง กรุณาสร้าง item ก่อนอัปเดต');
                }

                // อัปเดทข้อมูล
                if (!empty($validated['item_name'])) {
                    $storehouse->item_name = $validated['item_name']; // ใช้เฉพาะถ้ามีค่า
                }
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

                $storehouse->note = $validated['note'];

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
                // wage/electric_bill/water_bill
                $dt = Carbon::createFromFormat('m/Y', $validated['date'])->startOfMonth();
                $formattedDate = $dt->format('Y-m-d');

                $total = $validated['price_per_unit'] ?? 0;
                $filename = null;

                if (!empty($validated['receipt_file'])) {
                    $file = $validated['receipt_file'];
                    $filename = time() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('receipt_files'), $filename);
                }

                // หา record เดิม ถ้ามี
                $existingCost = Cost::where('farm_id', $batch->farm_id)
                    ->where('batch_id', $batch->id)
                    ->where('date', $formattedDate)
                    ->where('cost_type', $validated['item_type'])
                    ->first();

                if ($existingCost) {
                    // อัปเดต note และ receipt_file
                    $existingCost->note = $validated['note'] ?? $existingCost->note;
                    if ($filename) {
                        $existingCost->receipt_file = $filename;
                    }
                    $existingCost->save();
                } else {
                    // สร้างใหม่
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
            }

            return redirect()->back()->with('success', 'อัปเดตสินค้าในคลัง + บันทึกค่าใช้จ่ายเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }


    //--------------------------------------- Index ------------------------------------------//

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

    //Create store_item
    public function createItem(Request $request)
    {
        try {
            $validated = $request->validate([
                'farm_id'   => 'required|exists:farms,id',
                'item_type' => 'required|string',
                'item_code' => 'required|string',
                'item_name' => 'required|string',
                'unit' => 'required|string',
                'note' => 'nullable|string',

            ]);

            $data = new StoreHouse;
            $data->farm_id   = $validated['farm_id'];
            $data->item_type = $validated['item_type'];
            $data->item_code = $validated['item_code'];
            $data->item_name = $validated['item_name'];
            $data->unit   = $validated['unit'];
            $data->note   = $validated['note'] ?? null;
            $data->status = $request->status ?? 'unavailable';

            $data->save();

            return back()->with('success', 'เพิ่มรายการสินค้าใหม่เรียบร้อย');
        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    //Edit storehouse
    public function editStoreHouse(Request $request)
    {
        $farms = Farm::all();
        $storehouses = StoreHouse::paginate(10);

        // ส่งไปหน้า index พร้อม modal แก้ไข
        return view('admin.storehouses.index', compact('farms', 'storehouses'));
    }


    //Update storehouse
    public function updateStoreHouse(Request $request, $id)
    {
        $storehouse = StoreHouse::findOrFail($id);

        $validated = $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'item_code' => 'required|string',
            'item_name' => 'required|string',
            'unit' => 'required|string',
            'note' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $storehouse->update($validated);

        // ถ้ามีการอัปโหลดไฟล์
        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('receipt_files'), $filename);

            // อัปเดต cost ล่าสุดของ storehouse
            $latestCost = $storehouse->costs()->latest()->first();
            if ($latestCost) {
                $latestCost->update(['receipt_file' => $filename]);
            }
        }

        return redirect()->back()->with('success', 'แก้ไข StoreHouse สำเร็จ และอัปเดตใบเสร็จใน Cost เรียบร้อย');
    }



    //Delete storehous
    public function deleteStoreHouse($id)
    {
        $storehouse = StoreHouse::find($id);
        if (!$storehouse) {
            return redirect()->back()->with('error', 'ไม่พบรายการที่ต้องการลบ');
        }

        $storehouse->delete();
        return redirect()->route('storehouses.index')->with('success', 'ลบรายการเรียบร้อยแล้ว');
    }

    //--------------------------------------- EXPORT ------------------------------------------//

    // Export storehouse to PDF
    public function exportPdf()
    {
        $farms = Farm::all();
        $storehouses = StoreHouse::all();

        // ตั้งค่า dompdf options
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true); // รองรับ HTML5
        $options->set('isRemoteEnabled', true);     // โหลดไฟล์ font จาก URL ได้
        $options->set('defaultFont', 'Sarabun'); // ตั้ง default font

        // สร้าง PDF
        $pdf = Pdf::loadView('admin.storehouses.exports.storehouses_pdf', compact('farms', 'storehouses'))
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
        fputcsv($handle, ['Farm', 'Item Code', 'Item Name', 'Type', 'Unit', 'Stock', 'Status']);

        foreach ($storehouses as $storehouse) {
            fputcsv($handle, [
                $storehouse->farm->name ?? '-',
                $storehouse->item->type ?? '-',
                $storehouse->item->code ?? '-',
                $storehouse->item->name ?? '-',
                $storehouse->unit ?? '-',
                $storehouse->stock ?? '-',
                $storehouse->status,
                $storehouse->start_date,
                $storehouse->end_date,
            ]);
        }

        fclose($handle);

        return response()->streamDownload(function () use ($storehouses) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Farm', 'Item Code', 'Item Name', 'Type', 'Unit', 'Stock', 'Status']);
            foreach ($storehouses as $storehouse) {
                fputcsv($handle, [
                    $storehouse->farm->name ?? '-',
                    $storehouse->item->type ?? '-',
                    $storehouse->item->code ?? '-',
                    $storehouse->item->name ?? '-',
                    $storehouse->unit ?? '-',
                    $storehouse->stock ?? '-',
                    $storehouse->status,
                    $storehouse->start_date,
                    $storehouse->end_date,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
