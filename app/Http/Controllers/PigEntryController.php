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
use App\Helpers\PigInventoryHelper;

class PigEntryController extends Controller
{
    //-------------------------AJAX HELPER--------------------------------------//
    public function getBarnsByFarm($farmId)
    {
        $barns = Barn::where('farm_id', $farmId)->get(['id', 'barn_code']);
        return response()->json($barns);
    }

    public function getBatchesByFarm($farmId)
    {
        $batches = Batch::where('farm_id', $farmId)->get(['id', 'batch_code']);
        return response()->json($batches);
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
        $farms = Farm::all();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();
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

                // แปลงวันที่
                $dt = Carbon::createFromFormat('d/m/Y H:i', $validated['pig_entry_date']);
                $formattedDate = $dt->format('Y-m-d H:i');

                $batch = Batch::findOrFail($validated['batch_id']);
                $totalPigs = $validated['total_pig_amount'];
                $selectedBarns = Barn::whereIn('id', $validated['barn_id'])->get();

                // ตรวจสอบความจุรวมของ barns
                $totalBarnCapacity = $selectedBarns->sum(fn($barn) => $barn->pig_capacity);
                if ($totalBarnCapacity < $totalPigs) {
                    return redirect()->back()->with('error', 'จำนวนหมูมากกว่าความจุรวมของ barns ที่เลือก');
                }

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
                            ->where('pen_id', $pen->id)
                            ->sum('allocated_pigs');

                        $availableInPen = $pen->pig_capacity - $allocatedInPen;
                        if ($availableInPen <= 0) continue;

                        $allocateToPen = min($availableInPen, $remainingPigs);
                        $remainingPigs -= $allocateToPen;

                        // Use PigInventoryHelper to create/update allocation record
                        $result = PigInventoryHelper::addPigs($batch->id, $barn->id, $pen->id, $allocateToPen);
                        if (!isset($result['success']) || $result['success'] !== true) {
                            // Bubble up error to outer catch
                            throw new \Exception('ไม่สามารถบันทึก allocation: ' . ($result['message'] ?? 'Unknown error'));
                        }
                    }
                }

                // บันทึก PigEntryRecord
                $pigEntry = PigEntryRecord::create([
                    'batch_id'          => $batch->id,
                    'farm_id'           => $batch->farm_id,
                    'pig_entry_date'    => $formattedDate,
                    'total_pig_amount'  => $validated['total_pig_amount'],
                    'total_pig_weight'  => $validated['total_pig_weight'],
                    'total_pig_price'   => $validated['total_pig_price'],
                    'note'              => $validated['note'] ?? null,
                ]);

                // อัปโหลด Cloudinary
                $uploadedFileUrl = null;
                if ($request->hasFile('receipt_file')) {
                    $file = $request->file('receipt_file');
                    if ($file->isValid()) {
                        $uploadedFileUrl = Cloudinary::upload(
                            $file->getRealPath(),
                            ['folder' => 'receipt_files']
                        )->getSecurePath();
                    } else {
                        return redirect()->back()->with('error', 'ไฟล์ที่ส่งมาไม่ถูกต้อง');
                    }
                }

                // สร้าง Cost ลูกหมู
                Cost::create([
                    'farm_id'        => $batch->farm_id,
                    'batch_id'       => $batch->id,
                    'date'           => $formattedDate,
                    'cost_type'      => 'piglet',
                    'quantity'       => $validated['total_pig_amount'],
                    'price_per_unit' => $validated['total_pig_price'] / $validated['total_pig_amount'],
                    'total_price'    => $validated['total_pig_price'],
                    'note'           => 'ค่าลูกหมู',
                    'receipt_file'   => $uploadedFileUrl,
                    'transport_cost' => $validated['transport_cost'] ?? 0,
                ]);

                // สร้าง Cost น้ำหนักเกิน
                if (!empty($validated['excess_weight_cost']) && $validated['excess_weight_cost'] > 0) {
                    Cost::create([
                        'farm_id'        => $batch->farm_id,
                        'batch_id'       => $batch->id,
                        'date'           => $formattedDate,
                        'cost_type'      => 'excess_weight',
                        'quantity'       => 1,
                        'price_per_unit' => $validated['excess_weight_cost'],
                        'total_price'    => $validated['excess_weight_cost'],
                        'note'           => 'ค่าน้ำหนักส่วนเกิน',
                        'receipt_file'   => $uploadedFileUrl,
                    ]);
                }

                // อัปเดต totals ของ batch
                $batch->total_pig_amount = ($batch->total_pig_amount ?? 0) + $validated['total_pig_amount'];
                $batch->total_pig_weight = ($batch->total_pig_weight ?? 0) + $validated['total_pig_weight'];
                $batch->total_pig_price  = ($batch->total_pig_price ?? 0)  + $validated['total_pig_price'];
                $batch->save();

                return redirect()->back()->with('success', 'เพิ่มหมูเข้า + บันทึกค่าใช้จ่ายเรียบร้อย');
            } else {
                throw new \Exception("สถานะไม่ถูกต้อง ต้องเป็นกำลังเลี้ยงเท่านั้น");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }


    // ====================== Index / Edit / Update / Delete ======================== //

    public function indexPigEntryRecord(Request $request)
    {
        $farms = Farm::all();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();
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
                    'quantity' => $validated['total_pig_amount'],
                    'price_per_unit' => $validated['total_pig_price'] / max(1, $validated['total_pig_amount']),
                    'total_price' => $validated['total_pig_price'],
                    'note' => 'ค่าลูกหมู',
                    'receipt_file' => $uploadedFileUrl,
                ]
            );

            if (!empty($validated['excess_weight_cost']) && $validated['excess_weight_cost'] > 0) {
                Cost::updateOrCreate(
                    ['batch_id' => $batch->id, 'cost_type' => 'excess_weight'],
                    [
                        'farm_id' => $batch->farm_id,
                        'quantity' => 1,
                        'price_per_unit' => $validated['excess_weight_cost'],
                        'total_price' => $validated['excess_weight_cost'],
                        'note' => 'ค่าน้ำหนักส่วนเกิน',
                        'receipt_file' => $uploadedFileUrl,
                    ]
                );
            } else {
                Cost::where('batch_id', $batch->id)->where('cost_type', 'excess_weight')->delete();
            }

            Cost::where('batch_id', $batch->id)
                ->where('cost_type', 'transport')
                ->update([
                    'total_price' => $validated['transport_cost'] ?? 0,
                    'transport_cost' => $validated['transport_cost'] ?? 0
                ]);

            return redirect()->back()->with('success', 'แก้ไขข้อมูลเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function deletePigEntryRecord($id)
    {
        $pigEntryRecord = PigEntryRecord::find($id);
        if (!$pigEntryRecord) return redirect()->back()->with('error', 'ไม่พบรายการที่ต้องการลบ');

        $pigEntryRecord->delete();
        return redirect()->route('pig_entry_records.index')->with('success', 'ลบรายการเรียบร้อยแล้ว');
    }

    //--------------------------------------- EXPORT ------------------------------------------//
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
}
