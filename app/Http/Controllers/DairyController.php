<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Farm;
use App\Models\Batch;
use App\Models\Barn;
use App\Models\Pen;
use App\Models\DairyRecord;
use App\Models\StoreHouse;
use App\Models\PigDeath;
use App\Models\BatchTreatment;
use App\Models\InventoryMovement;
use App\Models\DairyStorehouseUse;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DairyController extends Controller
{
    //--------------------------------------- VIEW ------------------------------------------//
    public function viewDairy(Request $request)
    {
        // farms
        $farms = Farm::select('id', 'farm_name')->get();

        // batches (มี relation ไปยัง farm เอาไว้ filter ใน JS)
        $batches = Batch::with('farm:id,farm_name')
            ->select('id', 'batch_code', 'farm_id')
            ->get();

        // barns (ผูกกับ farm_id เอาไว้ filter)
        $barns = Barn::select('id', 'farm_id', 'barn_code')->get();

        // pens (ผูกกับ barn_id เอาไว้ filter)
        $pens = Pen::select('id', 'barn_id', 'pen_code')->get();

        // storehouses (ใช้เป็น dropdown feed/medicine)
        $storehouses = StoreHouse::select('id', 'item_code', 'item_name', 'item_type', 'unit')->get();
        //dd($farms, $batches, $barns, $pens, $storehouses);

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

        return view(
            'admin.dairy_records.record.dairy_record',
            compact('farms', 'batches', 'barns', 'pens', 'storehouses', 'storehousesByTypeAndBatch')
        );
    }


    //Index
    public function indexDairy(Request $request)
    {
        $farms = Farm::all();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();
        $barns = Barn::all();

        $query = DairyRecord::with([
            'dairy_storehouse_uses.storehouse',
            'batch_treatments.pen',
            'pig_deaths.pen',
            'batch.farm',
            'barn',
        ]);

        // --- Filters ---
        if ($request->filled('farm_id')) {
            $query->whereHas('batch', fn($q) => $q->where('farm_id', $request->farm_id));
        }

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->filled('barn_id')) {
            $query->where('barn_id', $request->barn_id);
        }

        if ($request->filled('updated_at')) {
            $query->whereDate('updated_at', Carbon::parse($request->updated_at));
        }

        // --- Filter by type ---
        if ($request->filled('type')) {
            $type = $request->type;
            $query->where(function ($q) use ($type) {
                if ($type === 'food') {
                    $q->whereHas('dairy_storehouse_uses');
                } elseif ($type === 'treatment') {
                    $q->whereHas('batch_treatments');
                } elseif ($type === 'death') {
                    $q->whereHas('pig_deaths');
                }
            });
        }

        // --- Sorting ---
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // --- Pagination ---
        $perPage = $request->get('per_page', 10);
        $dairyRecords = $query->paginate($perPage);

        // Map feed quantity จาก dairy_storehouse_uses
        foreach ($dairyRecords as $record) {
            $record->feed_uses = $record->dairy_storehouse_uses
                ->filter(fn($dsu) => $dsu->storehouse && preg_match('/f93[1-3]/i', $dsu->storehouse->item_code))
                ->map(function ($dsu) {
                    // ใช้ quantity ที่บันทึกตรง ๆ ใน dairy_storehouse_uses
                    $dsu->quantity_from_dsu = $dsu->quantity ?? 0;
                    return $dsu;
                })
                ->values();
        }

        return view('admin.dairy_records.index', compact('farms', 'batches', 'barns', 'dairyRecords'));
    }






    //--------------------------------------- UPLOAD / CREATE ------------------------------------------//

    public function uploadDairy(Request $request)
    {
        try {
            $sections = [
                'feed_use'     => 'feed',
                'medicine_use' => 'medicine',
                'dead_pig'     => 'pigdeath',
            ];

            // --- Filter row ที่ add จริง ---
            $feedUses = collect($request->feed_use ?? [])
                ->filter(fn($row) => !empty($row['item_code']) || !empty($row['barn_id']))
                ->values()
                ->all();

            $medicineUses = collect($request->medicine_use ?? [])
                ->filter(fn($row) => !empty($row['item_code']) || !empty($row['barn_id']))
                ->values()
                ->all();

            $deadPigs = collect($request->dead_pig ?? [])
                ->filter(fn($row) => !empty($row['quantity']))
                ->values()
                ->all();

            // --- Merge กลับ request ---
            $request->merge([
                'feed_use'     => $feedUses,
                'medicine_use' => $medicineUses,
                'dead_pig'     => $deadPigs,
            ]);

            // ใช้ transaction เพื่อความ atomic
            DB::beginTransaction();

            foreach ($sections as $inputName => $section) {
                $rows = $request->input($inputName, []);

                foreach ($rows as $i => $rowInput) {
                    // --- แปลง empty string เป็น null ---
                    foreach (['item_code', 'item_name', 'note', 'cause', 'status', 'quantity'] as $field) {
                        if (isset($rowInput[$field]) && $rowInput[$field] === '') {
                            $rowInput[$field] = null;
                        }
                    }

                    // --- Decode barn_pen ---
                    $barnPenList = [];
                    if (!empty($rowInput['barn_pen'])) {
                        $barnPenList = json_decode($rowInput['barn_pen'], true) ?: [];
                    }

                    // feed: ถ้าไม่มี barn_pen ให้ใช้ barn_id hidden multiple
                    if ($section === 'feed' && empty($barnPenList) && !empty($rowInput['barn_id'])) {
                        $barnIds = json_decode($rowInput['barn_id'], true) ?: [];
                        foreach ($barnIds as $bid) {
                            $barnPenList[] = ['barn_id' => $bid, 'pen_id' => null];
                        }
                    }

                    if (empty($barnPenList)) {
                        // เก็บขั้นต่ำเพื่อให้สร้าง DairyRecord ได้ (แต่จะเช็ค barnId จริงก่อนใช้)
                        $barnPenList[] = ['barn_id' => null, 'pen_id' => null];
                    }

                    // --- Validation ---
                    $rules = [
                        'farm_id'  => 'required|exists:farms,id',
                        'batch_id' => 'required|exists:batches,id',
                        'date'     => 'required|date_format:d/m/Y H:i',
                    ];

                    if ($section === 'feed' || $section === 'medicine') {
                        $rules = array_merge($rules, [
                            'item_code' => 'nullable|string',
                            'item_name' => 'nullable|string',
                            'quantity'  => 'nullable|integer|min:1',
                            'note'      => 'nullable|string',
                        ]);
                    }

                    if ($section === 'pigdeath') {
                        $rules = array_merge($rules, [
                            'quantity' => 'required|integer|min:1',
                            'cause'    => 'nullable|string',
                            'note'     => 'nullable|string',
                        ]);
                    }

                    $validated = validator($rowInput, $rules)->validate();

                    // --- แปลงวันที่ ---
                    $dt = Carbon::createFromFormat('d/m/Y H:i', $validated['date']);
                    $formattedDate = $dt->format('Y-m-d H:i:s');

                    // --- หา barn_id ตัวแทน สำหรับสร้าง DairyRecord ---
                    $representBarnId = null;
                    foreach ($barnPenList as $bp) {
                        if (!empty($bp['barn_id'])) {
                            $representBarnId = $bp['barn_id'];
                            break;
                        }
                        if (!empty($bp['pen_id'])) {
                            $pen = Pen::find($bp['pen_id']);
                            if ($pen) {
                                $representBarnId = $pen->barn_id;
                                break;
                            }
                        }
                    }

                    if (!$representBarnId) {
                        throw new \Exception("ไม่พบ barn_id กรุณาเลือกเล้าหรือคอกให้ครบ (แถวที่ index: {$i})");
                    }

                    // --- สร้าง DairyRecord (ครั้งเดียวต่อ row) ---
                    $dairy = DairyRecord::create([
                        'batch_id' => $validated['batch_id'],
                        'barn_id'  => $representBarnId,
                        'date'     => $formattedDate,
                        'note'     => $validated['note'] ?? null,
                    ]);
                    $dairyId = $dairy->id;

                    // --- Loop ทุก barn/pen ที่เลือกในแถว ---
                    foreach ($barnPenList as $bp) {
                        $barnId = $bp['barn_id'] ?? null;
                        $penId  = $bp['pen_id'] ?? null;

                        if (!$barnId && $penId) {
                            $pen = Pen::find($penId);
                            $barnId = $pen ? $pen->barn_id : null;
                        }

                        if (!$barnId) {
                            throw new \Exception("ไม่พบ barn_id กรุณาเลือกเล้าหรือคอกให้ครบ (ภายในแถวเดียวกัน)");
                        }

                        // --- หา storehouse ถ้ามี item_code (สำหรับ feed หรือ medicine) ---
                        $storehouse = null;
                        if (in_array($section, ['feed', 'medicine']) && !empty($validated['item_code'])) {
                            $storehouse = Storehouse::where('farm_id', $validated['farm_id'])
                                ->where('item_code', $validated['item_code'])
                                ->first();

                            if (! $storehouse) {
                                throw new \Exception("ไม่พบสินค้าในคลังสำหรับรหัส {$validated['item_code']} ที่ฟาร์ม {$validated['farm_id']}");
                            }
                        }

                        // --- Feed ---
                        if ($section === 'feed' && !empty($validated['item_code'])) {
                            $usedQuantity = (int)($validated['quantity'] ?? 0);

                            Log::info('Feed Row to save', [
                                'dairy_id' => $dairyId,
                                'farm_id' => $validated['farm_id'],
                                'batch_id' => $validated['batch_id'],
                                'barn_id' => $barnId,
                                'item_code' => $validated['item_code'],
                                'quantity' => $usedQuantity,
                            ]);

                            // สร้าง dairy_storehouse_use (มี quantity)
                            DairyStorehouseUse::create([
                                'dairy_record_id' => $dairyId,
                                'storehouse_id'   => $storehouse->id,
                                'barn_id'         => $barnId,
                                'quantity'        => $usedQuantity,
                                'date'            => $formattedDate,
                                'note'            => $validated['note'] ?? null,
                            ]);

                            // --- อัปเดต inventory และสร้าง movement ---
                            if ($usedQuantity > 0) {
                                if ($storehouse->stock < $usedQuantity) {
                                    throw new \Exception("สินค้า {$validated['item_name']} ({$validated['item_code']}) สต็อกไม่พอ (เหลือ {$storehouse->stock}, ต้องการ {$usedQuantity})");
                                }

                                $storehouse->stock -= $usedQuantity;
                                $storehouse->save();

                                InventoryMovement::create([
                                    'storehouse_id' => $storehouse->id,
                                    'batch_id'      => $validated['batch_id'],
                                    'change_type'   => 'out',
                                    'quantity'      => $usedQuantity,
                                    'note'          => 'ใช้สินค้า (Batch: ' . $validated['batch_id'] . ')',
                                    'date'          => $formattedDate,
                                ]);
                            }
                        }

                        // --- Medicine ---
                        if ($section === 'medicine' && !empty($validated['item_code'])) {
                            $usedQuantity = (int)($validated['quantity'] ?? 0);

                            Log::info('Medicine Row to save', [
                                'dairy_id' => $dairyId,
                                'farm_id' => $validated['farm_id'],
                                'batch_id' => $validated['batch_id'],
                                'barn_id' => $barnId,
                                'pen_id' => $penId,
                                'item_code' => $validated['item_code'],
                                'quantity' => $usedQuantity,
                            ]);

                            BatchTreatment::create([
                                'dairy_record_id' => $dairyId,
                                'batch_id'        => $validated['batch_id'],
                                'pen_id'          => $penId,
                                'medicine_name'   => $validated['item_name'],
                                'medicine_code'   => $validated['item_code'],
                                'quantity'        => $usedQuantity,
                                'unit'            => $storehouse->unit ?? null,
                                'status'          => $validated['status'] ?? 'วางแผนว่าจะให้ยา',
                                'note'            => $validated['note'] ?? null,
                                'date'            => $formattedDate,
                            ]);

                            // บันทึก dairy_storehouse_use (เพื่อเชื่อมกับ inventory)
                            DairyStorehouseUse::create([
                                'dairy_record_id' => $dairyId,
                                'storehouse_id'   => $storehouse->id,
                                'barn_id'         => $barnId,
                                'quantity'        => $usedQuantity,
                                'date'            => $formattedDate,
                                'note'            => $validated['note'] ?? null,
                            ]);

                            // --- อัปเดต inventory และสร้าง movement ---
                            if ($usedQuantity > 0) {
                                if ($storehouse->stock < $usedQuantity) {
                                    throw new \Exception("สินค้า {$validated['item_name']} ({$validated['item_code']}) สต็อกไม่พอ (เหลือ {$storehouse->stock}, ต้องการ {$usedQuantity})");
                                }

                                $storehouse->stock -= $usedQuantity;
                                $storehouse->save();

                                InventoryMovement::create([
                                    'storehouse_id' => $storehouse->id,
                                    'batch_id'      => $validated['batch_id'],
                                    'change_type'   => 'out',
                                    'quantity'      => $usedQuantity,
                                    'note'          => 'ใช้สินค้า (Batch: ' . $validated['batch_id'] . ')',
                                    'date'          => $formattedDate,
                                ]);
                            }
                        }

                        // --- PigDeath ---
                        if ($section === 'pigdeath') {
                            $deadQuantity = $validated['quantity'] ?? 0;
                            if ($deadQuantity <= 0) continue;

                            $batch = Batch::find($validated['batch_id']);
                            if (!$batch) continue;

                            $currentAmount = $batch->total_pig_amount ?? 0;
                            $avgWeightPerPig = ($currentAmount > 0 && ($batch->total_pig_weight ?? 0) > 0)
                                ? $batch->total_pig_weight / $currentAmount
                                : 0;

                            $batch->total_pig_amount = max($currentAmount - $deadQuantity, 0);
                            $batch->total_pig_weight = max(($batch->total_pig_weight ?? 0) - ($avgWeightPerPig * $deadQuantity), 0);
                            $batch->save();

                            $remainingDead = $deadQuantity;
                            foreach ($barnPenList as $bp2) {
                                $barnId2 = $bp2['barn_id'] ?? null;
                                $penId2  = $bp2['pen_id'] ?? null;

                                $reduce = $remainingDead;

                                if ($penId2) {
                                    $allocation = DB::table('batch_pen_allocations')
                                        ->where('batch_id', $batch->id)
                                        ->where('pen_id', $penId2)
                                        ->first();

                                    if ($allocation) {
                                        $reduce = min($remainingDead, $allocation->allocated_pigs);

                                        DB::table('batch_pen_allocations')
                                            ->where('id', $allocation->id)
                                            ->update([
                                                'allocated_pigs' => $allocation->allocated_pigs - $reduce,
                                                'updated_at'     => now(),
                                            ]);
                                    }
                                }

                                PigDeath::create([
                                    'dairy_record_id' => $dairyId,
                                    'batch_id'        => $batch->id,
                                    'pen_id'          => $penId2,
                                    'quantity'        => $reduce,
                                    'cause'           => $validated['cause'] ?? null,
                                    'note'            => $validated['note'] ?? null,
                                    'date'            => $formattedDate,
                                ]);

                                $remainingDead -= $reduce;
                                if ($remainingDead <= 0) break;
                            }
                        }
                    } // end foreach barnPenList
                } // end foreach row
            } // end foreach section

            DB::commit();

            return redirect()->back()->with('success', 'บันทึกประจำวันเรียบร้อย');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Upload Dairy error", [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }






    //--------------------------------------- EDIT / UPDATE ------------------------------------------//

    public function update(Request $request, $id)
    {
        $record = DairyRecord::findOrFail($id);

        $validated = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'barn_id' => 'required|exists:barns,id',
            'date' => 'required|date_format:d/m/Y H:i',
            'note' => 'nullable|string',
        ]);

        $record->batch_id = $validated['batch_id'];
        $record->barn_id = $validated['barn_id'];
        $record->date = Carbon::createFromFormat('d/m/Y H:i', $validated['date'])->format('Y-m-d H:i:s');
        $record->note = $validated['note'] ?? null;
        $record->save();

        return redirect()->back()->with('success', 'แก้ไข Dairy Record เรียบร้อย');
    }

    //--------------------------------------- DELETE ------------------------------------------//

    public function delete($id)
    {
        $record = DairyRecord::findOrFail($id);
        $record->delete();

        return redirect()->back()->with('success', 'ลบ Dairy Record เรียบร้อย');
    }

    //--------------------------------------- EXPORT ------------------------------------------//

    public function exportPdf()
    {
        $dairyRecords = DairyRecord::with(['batch', 'barn'])->get();
        $pdf = Pdf::loadView('admin.dairy_records.pdf', compact('dairyRecords'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('dairy_records_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    public function exportCsv()
    {
        $filename = 'dairy_records_' . date('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Batch', 'Barn', 'Date', 'Note']);
            $records = DairyRecord::with(['batch', 'barn'])->get();
            foreach ($records as $r) {
                fputcsv($handle, [
                    $r->batch->batch_code ?? '-',
                    $r->barn->name ?? '-',
                    $r->date,
                    $r->note ?? '-'
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
