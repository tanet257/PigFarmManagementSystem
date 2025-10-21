<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

use App\Models\Farm;
use App\Models\Batch;
use App\Models\Barn;
use App\Models\Pen;
use App\Models\Cost;
use App\Models\PigEntryRecord;
use App\Models\PigEntryDetail;
use Illuminate\Support\Facades\Log;
use App\Helpers\PigInventoryHelper;
use App\Helpers\NotificationHelper;

class PigEntryController extends Controller
{
    //-------------------------AJAX HELPER--------------------------------------//
    public function getBarnsByFarm($farmId)
    {
        $barns = Barn::where('farm_id', $farmId)->get(['id', 'barn_code']);
        return response()->json($barns);
    }

    public function getBarnAvailableCapacity($farmId)
    {
        try {
            $barns = Barn::where('farm_id', $farmId)->get();

            $barnData = $barns->map(function ($barn) {
                // คำนวณ allocated_pigs ทั้งหมดในเล้านี้
                $allocatedPigs = DB::table('batch_pen_allocations')
                    ->where('barn_id', $barn->id)
                    ->sum('current_quantity');

                $availableCapacity = $barn->pig_capacity - $allocatedPigs;

                return [
                    'id' => $barn->id,
                    'barn_code' => $barn->barn_code,
                    'pig_capacity' => $barn->pig_capacity,
                    'allocated_pigs' => $allocatedPigs,
                    'available_capacity' => max(0, $availableCapacity),
                    'is_full' => $availableCapacity <= 0
                ];
            });

            return response()->json($barnData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAvailableBarnsByFarm($farmId)
    {
        $barns = Barn::where('farm_id', $farmId)->get();

        $barns = $barns->map(function ($barn) {
            // ใช้ current_quantity ถ้ามี ถ้าไม่มีก็ fallback ไป allocated_pigs หรือ pig_amount
            $allocated = DB::table('batch_pen_allocations')
                ->where('barn_id', $barn->id)
                ->sum(DB::raw('COALESCE(current_quantity, allocated_pigs, pig_amount)'));

            $barn->remaining = ($barn->pig_capacity ?? 0) - ($allocated ?? 0);
            return $barn;
        });

        //เฉพาะ barn ที่ยังมีที่ว่าง
        $barns = $barns->filter(function ($barn) {
            return $barn->remaining > 0;
        })->values(); //reset keys

        return response()->json($barns);
    }

    // หน้าเพิ่ม Pig Entry
    public function pig_entry_record()
    {
        $farms = Farm::with('barns')->get();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->where('status', '!=', 'เสร็จสิ้น')->get();
        return view('admin.pig_entry_records.record.pig_entry_record', compact('farms', 'batches'));
    }

    // Upload Pig Entry Record
    public function upload_pig_entry_record(Request $request)
    {
        try {
            // Prefer the batch status if available; fall back to request input.
            $batchFromRequestId = $request->input('batch_id');
            $status = null;
            if ($batchFromRequestId) {
                $batchForStatus = Batch::find($batchFromRequestId);
                if ($batchForStatus) {
                    $status = $batchForStatus->status;
                }
            }

            // If still null, fall back to provided request status
            if (!$status) {
                $status = $request->input('status');
            }

            if ($status === "กำลังเลี้ยง") {
                $validated = $request->validate([
                    'farm_id'            => 'required|exists:farms,id',
                    'batch_id'           => 'required|exists:batches,id',
                    'barn_id'            => 'required|array|min:1',
                    'barn_id.*'          => 'exists:barns,id',
                    'pig_entry_date'     => 'required|string',
                    'total_pig_amount'   => 'required|numeric|min:1',
                    'total_pig_weight'   => 'required|numeric|min:0',
                    'total_pig_price'    => 'required|numeric|min:0',
                    'excess_weight_cost' => 'nullable|numeric|min:0',
                    'transport_cost'     => 'nullable|numeric|min:0',
                    'note'               => 'nullable|string',
                    'receipt_file'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                ]);

                DB::beginTransaction();

                // แปลงวันที่
                $dt = Carbon::createFromFormat('d/m/Y H:i', $validated['pig_entry_date']);
                $formattedDate = $dt->format('Y-m-d H:i');

                $batch = Batch::findOrFail($validated['batch_id']);
                $totalPigs = $validated['total_pig_amount'];
                $selectedBarns = Barn::whereIn('id', $validated['barn_id'])->get();

                // ตรวจสอบความจุที่เหลือใช้งานจริงของ barns
                $totalAvailableCapacity = 0;
                foreach ($selectedBarns as $barn) {
                    // คำนวณความจุที่ใช้ไปแล้ว
                    $usedCapacity = DB::table('batch_pen_allocations')
                        ->where('barn_id', $barn->id)
                        ->sum('current_quantity');
                    $availableCapacity = $barn->pig_capacity - $usedCapacity;
                    $totalAvailableCapacity += max(0, $availableCapacity);
                }

                if ($totalAvailableCapacity < $totalPigs) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'จำนวนหมูมากกว่าความจุที่เหลือของ barns ที่เลือก (เหลือ ' . $totalAvailableCapacity . ' ตัว แต่ต้อง ' . $totalPigs . ' ตัว)');
                }

                // บันทึก PigEntryRecord ก่อน เพื่อเอา ID ไปใช้ในการบันทึก details
                $avgWeight = $validated['total_pig_amount'] > 0
                    ? $validated['total_pig_weight'] / $validated['total_pig_amount']
                    : 0;
                $avgPrice = $validated['total_pig_amount'] > 0
                    ? $validated['total_pig_price'] / $validated['total_pig_amount']
                    : 0;

                $pigEntry = PigEntryRecord::create([
                    'batch_id'               => $batch->id,
                    'farm_id'                => $batch->farm_id,
                    'pig_entry_date'         => $formattedDate,
                    'total_pig_amount'       => $validated['total_pig_amount'],
                    'total_pig_weight'       => $validated['total_pig_weight'],
                    'total_pig_price'        => $validated['total_pig_price'],
                    'average_weight_per_pig' => $avgWeight,
                    'average_price_per_pig'  => $avgPrice,
                    'note'                   => $validated['note'] ?? null,
                ]);

                foreach ($selectedBarns as $barn) {
                    $allocateToBarn = min($barn->pig_capacity, $totalPigs);
                    $totalPigs -= $allocateToBarn;

                    $pens = Pen::where('barn_id', $barn->id)
                        ->where('status', 'กำลังใช้งาน')
                        ->get();

                    $remainingPigs = $allocateToBarn;
                    foreach ($pens as $pen) {
                        if ($remainingPigs <= 0) break;

                        $allocatedInPen = DB::table('batch_pen_allocations')
                            ->where('batch_id', $batch->id)
                            ->where('pen_id', $pen->id)
                            ->sum('allocated_pigs');

                        $availableInPen = $pen->pig_capacity - $allocatedInPen;
                        if ($availableInPen <= 0) continue;

                        $allocateToPen = min($availableInPen, $remainingPigs);
                        $remainingPigs -= $allocateToPen;

                        // Use PigInventoryHelper to create/update allocation record
                        $result = PigInventoryHelper::addPigs($batch->id, $barn->id, $pen->id, $allocateToPen);
                        if (!isset($result['success']) || $result['success'] !== true) {
                            DB::rollBack();
                            throw new \Exception('ไม่สามารถบันทึก allocation: ' . ($result['message'] ?? 'Unknown error'));
                        }

                        // บันทึกรายละเอียดการแจกจ่าย
                        \App\Models\PigEntryDetail::create([
                            'pig_entry_id' => $pigEntry->id,
                            'batch_id'     => $batch->id,
                            'barn_id'      => $barn->id,
                            'pen_id'       => $pen->id,
                            'quantity'     => $allocateToPen,
                        ]);
                    }
                }

                // บันทึก receipt file ใน Cost (ไม่เก็บใน pig_entry_records)
                $uploadedFileUrl = null;
                if ($request->hasFile('receipt_file')) {
                    $file = $request->file('receipt_file');
                    if ($file->isValid()) {
                        $uploadResponse = Cloudinary::upload(
                            $file->getRealPath(),
                            ['folder' => 'receipt_files']
                        );
                        $uploadedFileUrl = $uploadResponse['secure_url'] ?? null;
                    } else {
                        DB::rollBack();
                        return redirect()->back()->with('error', 'ไฟล์ที่ส่งมาไม่ถูกต้อง');
                    }
                }

                // สร้าง Cost ลูกหมู (บันทึก transport_cost และ excess_weight_cost ในคอลัมน์เดียวกัน)
                Cost::create([
                    'farm_id'              => $batch->farm_id,
                    'batch_id'             => $batch->id,
                    'date'                 => $formattedDate,
                    'cost_type'            => 'piglet',
                    'quantity'             => $validated['total_pig_amount'],
                    'price_per_unit'       => $avgPrice,
                    'total_price'          => $validated['total_pig_price'],
                    'transport_cost'       => $validated['transport_cost'] ?? 0,
                    'excess_weight_cost'   => $validated['excess_weight_cost'] ?? 0,
                    'note'                 => 'ค่าลูกหมู',
                    'receipt_file'         => $uploadedFileUrl,
                ]);

                DB::commit();
                return redirect()->back()->with('success', 'เพิ่มหมูเข้า + บันทึกค่าใช้จ่ายเรียบร้อย');
            } else {
                throw new \Exception("สถานะไม่ถูกต้อง ต้องเป็นกำลังเลี้ยงเท่านั้น");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }


    // ====================== Index / Edit / Update / Delete ======================== //

    public function indexPigEntryRecord(Request $request)
    {
        $farms = Farm::with('barns')->get();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->where('status', '!=', 'เสร็จสิ้น')->get();
        $barns = Barn::all();

        $query = PigEntryRecord::with(['farm', 'batch.costs']);

        // Search
        if ($request->filled('search')) {
            $query->where('note', 'like', '%' . $request->search . '%');
        }

        // Date Filter
        if ($request->filled('selected_date')) {
            $date = Carbon::now();
            switch ($request->selected_date) {
                case 'today':
                    $query->whereDate('pig_entry_date', $date);
                    break;
                case 'this_week':
                    $query->whereBetween('pig_entry_date', [$date->startOfWeek(), $date->copy()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('pig_entry_date', $date->month)
                        ->whereYear('pig_entry_date', $date->year);
                    break;
                case 'this_year':
                    $query->whereYear('pig_entry_date', $date->year);
                    break;
            }
        }

        // Farm Filter
        if ($request->filled('farm_id')) {
            $query->where('farm_id', $request->farm_id);
        }

        // Batch Filter
        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['pig_entry_date', 'total_pig_amount', 'total_pig_price', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $pigEntryRecords = $query->paginate($perPage);

        return view('admin.pig_entry_records.index', compact('barns', 'farms', 'batches', 'pigEntryRecords'));
    }

    public function editPigEntryRecord(Request $request)
    {
        $farms = Farm::all();
        $pigEntryRecords = PigEntryRecord::paginate(10);
        return view('admin.pig_entry_records.index', compact('farms', 'pigEntryRecords'));
    }

    public function updatePigentryrecord(Request $request, $id)
    {
        try {
            // ถ้า batch_id ว่าง ให้ใช้ batch_id_backup
            if (empty($request->input('batch_id')) && !empty($request->input('batch_id_backup'))) {
                $request->merge(['batch_id' => $request->input('batch_id_backup')]);
            }

            $validated = $request->validate([
                'batch_id' => 'required|exists:batches,id',
                'pig_entry_date'    => 'required|string',
                'total_pig_amount' => 'required|numeric|min:1',
                'total_pig_weight' => 'required|numeric|min:0',
                'total_pig_price' => 'required|numeric|min:0',
                'excess_weight_cost' => 'nullable|numeric|min:0',
                'transport_cost' => 'nullable|numeric|min:0',
                'note' => 'nullable|string',
                'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'delete_receipt_file' => 'sometimes',
            ]);

            $record = PigEntryRecord::findOrFail($id);
            $batch = Batch::findOrFail($validated['batch_id']);

            // แปลงวันที่ให้เป็นรูปแบบ database
            $dt = Carbon::createFromFormat('d/m/Y H:i', $validated['pig_entry_date']);
            $formattedDate = $dt->format('Y-m-d H:i');

            $uploadedFileUrl = $record->receipt_file ?? null;
            $wantsDelete = $request->boolean('delete_receipt_file');
            if ($wantsDelete) $uploadedFileUrl = null;

            if ($request->hasFile('receipt_file')) {
                $file = $request->file('receipt_file');
                if (!$file->isValid()) return redirect()->back()->with('error', 'ไฟล์ที่ส่งมาไม่ถูกต้อง');

                $uploadedFileUrl = Cloudinary::upload(
                    $file->getRealPath(),
                    ['folder' => 'receipt_files']
                )->getSecurePath();
            }

            $record->update([
                'batch_id' => $batch->id,
                'farm_id' => $batch->farm_id,
                'pig_entry_date' => $formattedDate,
                'total_pig_amount' => $validated['total_pig_amount'],
                'total_pig_weight' => $validated['total_pig_weight'],
                'total_pig_price' => $validated['total_pig_price'],
                'note' => $validated['note'] ?? null,
                'receipt_file' => $uploadedFileUrl,
            ]);

            Cost::updateOrCreate(
                ['batch_id' => $batch->id, 'cost_type' => 'piglet'],
                [
                    'farm_id' => $batch->farm_id,
                    'date' => $formattedDate,
                    'quantity' => $validated['total_pig_amount'],
                    'price_per_unit' => $validated['total_pig_price'] / max(1, $validated['total_pig_amount']),
                    'total_price' => $validated['total_pig_price'],
                    'transport_cost' => $validated['transport_cost'] ?? 0,
                    'excess_weight_cost' => $validated['excess_weight_cost'] ?? 0,
                    'note' => 'ค่าลูกหมู',
                    'receipt_file' => $uploadedFileUrl,
                ]
            );

            return redirect()->back()->with('success', 'แก้ไขข้อมูลเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function deletePigEntryRecord($id)
    {
        try {
            DB::beginTransaction();

            $pigEntryRecord = PigEntryRecord::find($id);
            if (!$pigEntryRecord) {
                return redirect()->back()->with('error', 'ไม่พบรายการที่ต้องการลบ');
            }

            $batchId = $pigEntryRecord->batch_id;

            // ดึงรายละเอียดการแจกจ่าย
            $entryDetails = PigEntryDetail::where('pig_entry_id', $id)->get();

            // คืนค่าแต่ละ allocation ตามรายละเอียดที่บันทึกไว้
            foreach ($entryDetails as $detail) {

                // ใช้ helper มาลด inventory
                $result = PigInventoryHelper::reducePigInventory(
                    $detail->batch_id,
                    $detail->pen_id,
                    $detail->quantity,
                    'pig_entry_deletion'
                );

                if (!$result['success']) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'ไม่สามารถคืนค่าหมูได้: ' . $result['message']);
                }
            }

            // ลบรายละเอียด
            PigEntryDetail::where('pig_entry_id', $id)->delete();

            // ลบ cost records ที่เกี่ยวข้อง
            Cost::where('batch_id', $batchId)->delete();

            // ลบ pig entry record
            $pigEntryRecord->delete();

            DB::commit();
            return redirect()->route('pig_entry_records.index')
                ->with('success', 'ลบรายการและคืนหมูเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }    //--------------------------------------- EXPORT ------------------------------------------//
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

    /**
     * บันทึกการชำระเงินสำหรับการรับเข้าหมู
     */
    public function update_payment(Request $request, $id)
    {
        try {
            $record = PigEntryRecord::findOrFail($id);

            // Validate input - แยก validation logic เพื่อให้ messages ออกมาชัดเจน
            try {
                $validated = $request->validate(
                    [
                        'paid_amount' => 'required|numeric|min:0.01',
                        'payment_method' => 'required|in:เงินสด,โอนเงิน',
                        'receipt_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                        'note' => 'nullable|string',
                    ],
                    [
                        'paid_amount.required' => 'จำนวนเงินที่ชำระเป็นบังคับ',
                        'paid_amount.numeric' => 'จำนวนเงินต้องเป็นตัวเลข',
                        'paid_amount.min' => 'จำนวนเงินต้องมากกว่า 0',
                        'payment_method.required' => 'กรุณาเลือกวิธีชำระเงิน',
                        'payment_method.in' => 'วิธีชำระเงินไม่ถูกต้อง',
                        'receipt_file.required' => 'กรุณาอัปโหลดหลักฐานการชำระเงิน',
                        'receipt_file.file' => 'หลักฐานการชำระเงินต้องเป็นไฟล์',
                        'receipt_file.mimes' => 'ประเภทไฟล์ต้องเป็น jpg, jpeg, png หรือ pdf',
                        'receipt_file.max' => 'ขนาดไฟล์ต้องไม่เกิน 5 MB',
                    ]
                );
            } catch (\Illuminate\Validation\ValidationException $ve) {
                // Convert errors array to string
                $errorMessages = [];
                foreach ($ve->errors() as $field => $messages) {
                    $errorMessages = array_merge($errorMessages, $messages);
                }
                $errorText = implode("\n", $errorMessages);

                // Return validation errors
                return redirect()->back()
                    ->withInput()
                    ->with('error', $errorText);
            }

            DB::beginTransaction();

            // อัปโหลดไฟล์ receipt (ต้องมี)
            $receiptPath = null;
            if ($request->hasFile('receipt_file')) {
                $file = $request->file('receipt_file');
                if ($file->isValid()) {
                    try {
                        $uploadResult = Cloudinary::upload($file->getRealPath(), [
                            'folder' => 'pig-farm/pig-entry-receipts',
                            'resource_type' => 'auto',
                        ]);

                        // CloudinaryEngine::upload() returns the engine instance itself
                        // Call getSecurePath() to extract the URL from the stored response
                        $receiptPath = $uploadResult->getSecurePath();
                        Log::info('Receipt path from getSecurePath: ' . ($receiptPath ?? 'null'));
                    } catch (\Exception $e) {
                        Log::error('Cloudinary upload error: ' . $e->getMessage());
                        DB::rollBack();
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'ไม่สามารถอัปโหลดไฟล์สลิปได้ (' . $e->getMessage() . ')');
                    }
                } else {
                    DB::rollBack();
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'ไฟล์ที่ส่งมาไม่ถูกต้อง');
                }
            }

            // ตรวจสอบว่าอัปโหลดสำเร็จ
            if (!$receiptPath) {
                DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'ไม่สามารถอัปโหลดไฟล์สลิปได้ กรุณาลองใหม่');
            }            // สร้าง Cost record เพื่อบันทึกการชำระเงิน
            $totalAmount = $record->total_pig_price +
                          ($record->batch->costs->sum('excess_weight_cost') ?? 0) +
                          ($record->batch->costs->sum('transport_cost') ?? 0);

            Cost::create([
                'farm_id' => $record->batch->farm_id,
                'batch_id' => $record->batch_id,
                'pig_entry_record_id' => $record->id,
                'cost_type' => 'payment',
                'amount' => $validated['paid_amount'],
                'payment_method' => $validated['payment_method'],
                'receipt_file' => $receiptPath,
                'payment_status' => 'pending',
                'paid_date' => now()->toDateString(),
                'date' => now()->toDateString(),
                'note' => $validated['note'] ?? 'บันทึกการชำระเงิน - ' . $record->batch->batch_code,
            ]);

            // ส่งแจ้งเตือนให้ Admin อนุมัติการชำระเงิน
            NotificationHelper::notifyAdminsPigEntryPaymentRecorded($record, auth()->user());

            DB::commit();

            return redirect()->route('pig_entry_records.index')
                ->with('success', 'บันทึกการชำระเงินเรียบร้อยแล้ว - รอ admin อนุมัติ');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment update error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}
