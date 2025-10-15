<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;

use App\Models\Farm;
use App\Models\Batch;

use App\Models\Cost;

use App\Models\StoreHouse;
use App\Models\InventoryMovement;


class StoreHouseController extends Controller
{

    //--------------------------------------- VIEW ------------------------------------------//

    //view storehouse record
    public function store_house_record(Request $request)
    {
        // farms
        $farms = Farm::all();

        // batches
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();

        // storehouses
        $storehouses = StoreHouse::all();

        /**
         * ---------- สำหรับ item_code ----------
         * group: item_type -> batch_id -> [item_code, item_name]
         * ใช้ InventoryMovement + storehouse
         */
        $storehousesByTypeAndBatch = InventoryMovement::with('storehouse')
            ->get()
            ->groupBy(fn($movement) => $movement->storehouse->item_type)
            ->map(function ($group) {
                return $group->groupBy('batch_id')->map(function ($batchGroup) {
                    return $batchGroup->mapWithKeys(function ($movement) {
                        return [
                            $movement->storehouse->item_code => [
                                'item_code' => $movement->storehouse->item_code,
                                'item_name' => $movement->storehouse->item_name,
                            ]
                        ];
                    });
                });
            });

        /**
         * ---------- สำหรับ unit ----------
         * group: item_type -> units
         * ดึงจาก StoreHouse โดยตรง
         */
        $unitsByType = Storehouse::select('item_type', 'unit')
            ->get()
            ->groupBy('item_type')
            ->map(fn($group) => $group->pluck('unit')->unique()->values());



        //dd($storehouses->groupBy('item_type')->map(fn($g) => $g->pluck('unit')->unique()->values()));

        return view(
            'admin.storehouses.record.store_house_record',
            compact('farms', 'batches', 'storehouses', 'storehousesByTypeAndBatch', 'unitsByType')
        );
    }


    // upload_store_house_record
    public function upload_store_house_record(Request $request)
    {
        try {
            $unitsByType = [
                'feed' => ['kg', 'กระสอบ'],
                'medicine' => ['ขวด', 'กล่อง', 'ซอง'],
                'wage' => ['บาท'],
                'electric_bill' => ['บาท'],
                'water_bill' => ['บาท'],
            ];

            $sections = ['feed', 'medicine', 'monthly'];

            foreach ($sections as $section) {
                $rows = $request->input($section, []);

                foreach ($rows as $i => $rowInput) {
                    // แปลง empty string เป็น null
                    foreach (['unit', 'item_code', 'item_name'] as $field) {
                        if (isset($rowInput[$field]) && $rowInput[$field] === '') $rowInput[$field] = null;
                    }

                    $allowedUnits = $unitsByType[$rowInput['item_type']] ?? [];
                    Log::info("Row input", $rowInput);
                    // Validation
                    $validated = validator($rowInput, [
                        'farm_id'   => 'required|exists:farms,id',
                        'batch_id'  => 'required|exists:batches,id',
                        'date' => ['required', function ($attribute, $value, $fail) use ($rowInput) {
                            try {
                                if (in_array($rowInput['item_type'], ['wage', 'electric_bill', 'water_bill'])) {
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
                        'note'           => 'nullable|string',
                    ])->validate();

                    $batch = Batch::findOrFail($validated['batch_id']);

                    // อัปโหลดไฟล์ถ้ามี
                    $uploadedFileUrl = null;
                    if ($request->hasFile("$section.$i.receipt_file")) {
                        $file = $request->file("$section.$i.receipt_file");
                        if ($file->isValid()) {
                            $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), ['folder' => 'receipt_files'])->getSecurePath();
                        }
                    }

                    // -------------------
                    // STOREHOUSE
                    // -------------------
                    $storehouse = null;
                    if (!in_array($validated['item_type'], ['wage', 'electric_bill', 'water_bill'])) {
                        $storehouse = StoreHouse::where('farm_id', $validated['farm_id'])

                            ->where('item_code', $validated['item_code'])
                            ->first();

                        if ($storehouse) {
                            $storehouse->stock = ($storehouse->stock ?? 0) + ($validated['stock'] ?? 0);
                            if (!empty($validated['item_name'])) $storehouse->item_name = $validated['item_name'];
                            $storehouse->item_type = $validated['item_type'];
                            $storehouse->unit = $validated['unit'];
                            $storehouse->status = 'available';
                            $storehouse->note = $validated['note'];
                            $storehouse->save();
                        } else {
                            $storehouse = StoreHouse::create([
                                'farm_id'   => $validated['farm_id'],
                                'batch_id'  => $validated['batch_id'],
                                'item_code' => $validated['item_code'],
                                'item_name' => $validated['item_name'] ?? null,
                                'item_type' => $validated['item_type'],
                                'unit'      => $validated['unit'] ?? null,
                                'status'    => 'available',
                                'stock'     => $validated['stock'] ?? 0,
                                'note'      => $validated['note'] ?? null,
                            ]);
                        }
                    }

                    // -------------------
                    // COST + INVENTORY
                    // -------------------
                    $total = ($validated['stock'] ?? 0) * ($validated['price_per_unit'] ?? 0) + ($validated['transport_cost'] ?? 0);

                    if (in_array($validated['item_type'], ['wage', 'electric_bill', 'water_bill'])) {
                        $dt = Carbon::createFromFormat('m/Y', $validated['date'])->startOfMonth();
                        $formattedDate = $dt->format('Y-m-d');

                        $existingCost = Cost::where('farm_id', $batch->farm_id)
                            ->where('batch_id', $batch->id)
                            ->where('date', $formattedDate)
                            ->where('cost_type', $validated['item_type'])
                            ->first();

                        if ($existingCost) {
                            $existingCost->note = $validated['note'] ?? $existingCost->note;
                            if ($uploadedFileUrl) $existingCost->receipt_file = $uploadedFileUrl;
                            $existingCost->save();
                        } else {
                            Cost::create([
                                'farm_id'        => $batch->farm_id,
                                'batch_id'       => $batch->id,
                                'date'           => $formattedDate,
                                'cost_type'      => $validated['item_type'],
                                'item_code'      => null,
                                'price_per_unit' => $validated['price_per_unit'] ?? 0,
                                'unit'           => $validated['unit'] ?? null,
                                'total_price'    => $total,
                                'note'           => $validated['note'] ?? null,
                                'receipt_file'   => $uploadedFileUrl,
                            ]);
                        }
                    } else {
                        $dt = Carbon::createFromFormat('d/m/Y H:i', $validated['date']);
                        $formattedDate = $dt->format('Y-m-d H:i:s');

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
                            'receipt_file'   => $uploadedFileUrl ?? null,
                        ]);

                        InventoryMovement::create([
                            'storehouse_id' => $storehouse->id,
                            'batch_id'      => $batch->id,
                            'change_type'   => 'in',
                            'quantity'      => $validated['stock'],
                            'note'          => 'เพิ่มสินค้าเข้าคลังจากการบันทึกค่าใช้จ่าย (Batch: ' . $batch->id . ')',
                            'date'          => $formattedDate,
                        ]);
                    }
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

        // filter category (item_type)
        if ($request->filled('category')) {
            $query->where('item_type', $request->category);
        }

        // Date Filter (based on updated_at)
        if ($request->filled('selected_date')) {
            $date = \Carbon\Carbon::now();
            switch ($request->selected_date) {
                case 'today':
                    $query->whereDate('updated_at', $date);
                    break;
                case 'this_week':
                    $query->whereBetween('updated_at', [$date->startOfWeek(), $date->copy()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('updated_at', $date->month)
                        ->whereYear('updated_at', $date->year);
                    break;
                case 'this_year':
                    $query->whereYear('updated_at', $date->year);
                    break;
            }
        }

        // filter stock status
        if ($request->filled('stock_status')) {
            if ($request->stock_status == 'in_stock') {
                // มีสินค้า: stock > min_quantity
                $query->whereRaw('stock > COALESCE(min_quantity, 0)');
            } elseif ($request->stock_status == 'low_stock') {
                // สินค้าใกล้หมด: 0 < stock < min_quantity
                $query->whereRaw('stock > 0')
                    ->whereRaw('stock < COALESCE(min_quantity, 0)');
            } elseif ($request->stock_status == 'out_of_stock') {
                // สินค้าหมด: stock <= 0
                $query->where('stock', '<=', 0);
            }
        }

        // sort - รองรับทั้ง sort parameter (จาก dropdown) และ sort_by/sort_order (เดิม)
        if ($request->filled('sort')) {
            // จาก dropdown
            switch ($request->sort) {
                case 'name_asc':
                    $query->orderBy('item_name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('item_name', 'desc');
                    break;
                case 'quantity_asc':
                    $query->orderBy('stock', 'asc');
                    break;
                case 'quantity_desc':
                    $query->orderBy('stock', 'desc');
                    break;
                default:
                    $query->orderBy('updated_at', 'desc');
            }
        } else {
            // sort แบบเดิม (sort_by และ sort_order)
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
                'min_quantity' => 'nullable|numeric|min:0',
                'note' => 'nullable|string',

            ]);

            $data = new StoreHouse;
            $data->farm_id   = $validated['farm_id'];
            $data->item_type = $validated['item_type'];
            $data->item_code = $validated['item_code'];
            $data->item_name = $validated['item_name'];
            $data->unit   = $validated['unit'];
            $data->min_quantity = $validated['min_quantity'] ?? 0;
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


        $validated = $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'item_code' => 'required|string',
            'item_name' => 'required|string',
            'unit' => 'required|string',
            'min_quantity' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'delete_receipt_file' => 'sometimes',
        ]);

        $storehouse = StoreHouse::findOrFail($id);

        // อัปเดตฟิลด์อื่น ๆ ของ storehouse
        $storehouse->farm_id   = $validated['farm_id'];
        $storehouse->item_code = $validated['item_code'];
        $storehouse->item_name = $validated['item_name'];
        $storehouse->unit      = $validated['unit'];
        $storehouse->min_quantity = $validated['min_quantity'] ?? 0;
        $storehouse->note      = $validated['note'] ?? null;
        $storehouse->save();

        // จัดการใบเสร็จใน Cost (latest)
        $latestCost = $storehouse->latestCost()->first();
        if ($latestCost) {
            $uploadedFileUrl = $latestCost->receipt_file; // เริ่มด้วยค่าเดิม

            if ($request->boolean('delete_receipt_file')) {
                $uploadedFileUrl = null;
            }

            if ($request->hasFile('receipt_file') && $request->file('receipt_file')->isValid()) {
                $uploadedFileUrl = Cloudinary::upload(
                    $request->file('receipt_file')->getRealPath(),
                    ['folder' => 'receipt_files']
                )->getSecurePath();
            }

            // อัปเดต cost
            $latestCost->update(['receipt_file' => $uploadedFileUrl]);
        }


        return redirect()->back()->with('success', 'แก้ไข StoreHouse และอัปเดตใบเสร็จใน Cost เรียบร้อย');
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
