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
use App\Helpers\PigInventoryHelper;

class DairyController extends Controller
{
    // Helper for updating note and date
    private function updateNoteAndDate($model, $note, $dateField, $dateValue)
    {
        $model->update([
            'note' => $note ?? $model->note,
            $dateField => $dateValue,
        ]);
    }
    //--------------------------------------- VIEW ------------------------------------------//
    public function viewDairy(Request $request)
    {
        // farms
        $farms = Farm::select('id', 'farm_name')->get();

        // batches (มี relation ไปยัง farm เอาไว้ filter ใน JS)
        $batches = Batch::with('farm:id,farm_name')
            ->select('id', 'batch_code', 'farm_id')
            ->where('status', '!=', 'เสร็จสิ้น')
            ->where('status', '!=', 'cancelled')
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
        $batches = Batch::select('id', 'batch_code', 'farm_id')
            ->where('status', '!=', 'เสร็จสิ้น')
            ->where('status', '!=', 'cancelled')  // ✅ ยกเว้น cancelled
            ->get();
        $barns = Barn::all();

        $query = DairyRecord::with([
            'dairy_storehouse_uses.storehouse',
            'dairy_storehouse_uses.barn',
            'batch_treatments.pen',
            'pig_deaths.pen',
            'batch.farm',
            'barn',
        ]);

        // --- Search ---
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('note', 'like', '%' . $request->search . '%')
                    ->orWhereHas('batch', function ($sq) use ($request) {
                        $sq->where('batch_code', 'like', '%' . $request->search . '%');
                    });
            });
        }

        // --- Date Filter ---
        if ($request->filled('selected_date')) {
            $date = Carbon::now();
            switch ($request->selected_date) {
                case 'today':
                    $query->whereDate('date', $date);
                    break;
                case 'this_week':
                    $query->whereBetween('date', [$date->startOfWeek(), $date->copy()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('date', $date->month)
                        ->whereYear('date', $date->year);
                    break;
                case 'this_year':
                    $query->whereYear('date', $date->year);
                    break;
            }
        }

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

        // Map display fields for blade
        foreach ($dairyRecords as $record) {
            // Default values
            $record->display_barn = '-';
            $record->display_details = '-';
            $record->display_quantity = '-';

            // Feed
            if ($request->type === 'food' || (!$request->filled('type') && $record->dairy_storehouse_uses->count())) {
                $use = $record->dairy_storehouse_uses->first();
                if ($use) {
                    $record->display_barn = optional($use->barn)->barn_code ?? optional($record->barn)->barn_code ?? '-';
                    $record->display_details = 'รหัส: ' . (optional($use->storehouse)->item_code ?? '-') . ', หน่วย: ' . (optional($use->storehouse)->unit ?? '-') . ($use->note ? ', ' . $use->note : '');
                    $record->display_quantity = $use->quantity ?? '-';
                }
            }
            // Treatment
            elseif ($request->type === 'treatment' || (!$request->filled('type') && $record->batch_treatments->count())) {
                $bt = $record->batch_treatments->first();
                if ($bt) {
                    $record->display_barn = (optional($record->barn)->barn_code ?? '-') . '/' . (optional($bt->pen)->pen_code ?? '-');
                    $record->display_details = 'ยา: ' . ($bt->medicine_code ?? '-') . ', หน่วย: ' . ($bt->unit ?? '-') . ', สถานะ: ' . ($bt->status ?? '-') . ($bt->note ? ', ' . $bt->note : '');
                    $record->display_quantity = $bt->quantity ?? '-';
                }
            }
            // Death
            elseif ($request->type === 'death' || (!$request->filled('type') && $record->pig_deaths->count())) {
                $pd = $record->pig_deaths->first();
                if ($pd) {
                    $record->display_barn = (optional($record->barn)->barn_code ?? '-') . '/' . (optional($pd->pen)->pen_code ?? '-');
                    $record->display_details = 'คอก: ' . (optional($pd->pen)->pen_code ?? '-') . ($pd->note ? ', ' . $pd->note : '');
                    $record->display_quantity = $pd->quantity ?? '-';
                }
            }
            // Fallback
            else {
                $record->display_barn = optional($record->barn)->barn_code ?? '-';
                $record->display_details = $record->note ?? '-';
                $record->display_quantity = '-';
            }
        }

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

            DB::beginTransaction();

            foreach ($sections as $inputName => $section) {
                $rows = $request->input($inputName, []);

                foreach ($rows as $i => $rowInput) {
                    // --- Normalize single to array ---
                    if (isset($rowInput['barn_id']) && !is_array($rowInput['barn_id'])) {
                        $rowInput['barn_id'] = [$rowInput['barn_id']];
                    }

                    // --- แปลง empty string เป็น null ---
                    foreach (['item_code', 'item_name', 'note', 'cause', 'status', 'quantity'] as $field) {
                        if (isset($rowInput[$field]) && $rowInput[$field] === '') {
                            $rowInput[$field] = null;
                        }
                    }

                    // --- Decode barn_pen ---
                    $barnPenList = [];

                    if ($section === 'feed') {
                        // feed_use ใช้ JSON array
                        if (!empty($rowInput['barn_pen'])) {
                            $decoded = json_decode($rowInput['barn_pen'], true);
                            if (is_array($decoded)) {
                                $barnPenList = $decoded; // [{barn_id, pen_id}]
                            }
                        }
                        // fallback ใช้ barn_id ถ้า pen_id ไม่มี
                        if (empty($barnPenList) && !empty($rowInput['barn_id'])) {
                            foreach ((array)$rowInput['barn_id'] as $bid) {
                                $barnPenList[] = ['barn_id' => $bid, 'pen_id' => null];
                            }
                        }
                    } else {
                        // medicine_use / dead_pig ใช้ pen_id เป็น scalar
                        $penId = $rowInput['barn_pen'] ?? null;
                        $barnId = $rowInput['barn_id'] ?? null;

                        if ($penId) {
                            $pen = Pen::find($penId);
                            $barnId = $pen ? $pen->barn_id : $barnId;
                            $barnPenList[] = ['barn_id' => $barnId, 'pen_id' => $penId];
                        } elseif ($barnId) {
                            $barnPenList[] = ['barn_id' => $barnId, 'pen_id' => null];
                        } else {
                            $barnPenList[] = ['barn_id' => null, 'pen_id' => null];
                        }
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

                    // --- หา barn_id ตัวแทน ---
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

                    // --- สร้าง DairyRecord ---
                    $dairy = DairyRecord::create([
                        'batch_id' => $validated['batch_id'],
                        'barn_id'  => $representBarnId,
                        'date'     => $formattedDate,
                        'note'     => $validated['note'] ?? null,
                    ]);

                    $dairyId = $dairy->id;

                    // --- Loop ทุก barn/pen ที่เลือก ---
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

                        // --- Storehouse / Feed / Medicine / PigDeath ---
                        $storehouse = null;
                        if (in_array($section, ['feed', 'medicine']) && !empty($validated['item_code'])) {
                            $storehouse = Storehouse::where('farm_id', $validated['farm_id'])
                                ->where('item_code', $validated['item_code'])
                                ->first();

                            if (!$storehouse) {
                                throw new \Exception("ไม่พบสินค้าในคลังสำหรับรหัส {$validated['item_code']} ที่ฟาร์ม {$validated['farm_id']}");
                            }
                        }

                        // --- Feed ---
                        if ($section === 'feed' && !empty($validated['item_code'])) {
                            $usedQuantity = (int)($validated['quantity'] ?? 0);

                            DairyStorehouseUse::create([
                                'dairy_record_id' => $dairyId,
                                'storehouse_id'   => $storehouse->id,
                                'barn_id'         => $barnId,
                                'quantity'        => $usedQuantity,
                                'date'            => $formattedDate,
                                'note'            => $validated['note'] ?? null,
                            ]);

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

                            DairyStorehouseUse::create([
                                'dairy_record_id' => $dairyId,
                                'storehouse_id'   => $storehouse->id,
                                'barn_id'         => $barnId,
                                'quantity'        => $usedQuantity,
                                'date'            => $formattedDate,
                                'note'            => $validated['note'] ?? null,
                            ]);

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
                            if (!$batch || $batch->status === 'cancelled') continue;

                            $batch->total_death += $deadQuantity;
                            $batch->current_quantity = max(($batch->current_quantity ?? 0) - $deadQuantity, 0);

                            $currentAmount = $batch->current_quantity + $deadQuantity;
                            $avgWeightPerPig = ($currentAmount > 0 && ($batch->total_pig_weight ?? 0) > 0)
                                ? $batch->total_pig_weight / $currentAmount
                                : 0;
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
                                        // Use helper to reduce inventory where possible
                                        $availableInAllocation = $allocation->current_quantity ?? $allocation->allocated_pigs;
                                        $reduce = min($remainingDead, $availableInAllocation);

                                        $result = PigInventoryHelper::reducePigInventory(
                                            $batch->id,
                                            $allocation->pen_id,
                                            $reduce,
                                            'death'
                                        );

                                        if (!$result['success']) {
                                            // fallback to direct update: adjust current_quantity if present, else allocated_pigs
                                            if (property_exists($allocation, 'current_quantity')) {
                                                $newCurrent = max(($allocation->current_quantity ?? $allocation->allocated_pigs) - $reduce, 0);
                                                DB::table('batch_pen_allocations')
                                                    ->where('id', $allocation->id)
                                                    ->update([
                                                        'current_quantity' => $newCurrent,
                                                        'updated_at'     => now(),
                                                    ]);
                                            } else {
                                                DB::table('batch_pen_allocations')
                                                    ->where('id', $allocation->id)
                                                    ->update([
                                                        'allocated_pigs' => max($allocation->allocated_pigs - $reduce, 0),
                                                        'updated_at'     => now(),
                                                    ]);
                                            }
                                        }
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
                    }
                }
            }

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
    //edit_feed_treatment

    // --- Generic Update for All Types ---
    public function updateDairy(Request $request, $id)
    {
        $dairy = DairyRecord::with(['dairy_storehouse_uses', 'batch_treatments', 'pig_deaths'])->findOrFail($id);

        // Determine which type to update based on relationships
        if ($dairy->dairy_storehouse_uses->count()) {
            // Feed type
            $use = $dairy->dairy_storehouse_uses->first();
            return $this->updateFeed($request, $id, $use->id, 'food');
        } elseif ($dairy->batch_treatments->count()) {
            // Medicine type
            $bt = $dairy->batch_treatments->first();
            return $this->updateMedicine($request, $id, $bt->id, 'treatment');
        } elseif ($dairy->pig_deaths->count()) {
            // Death type
            $death = $dairy->pig_deaths->first();
            return $this->updatePigDeath($request, $death->id);
        } else {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลที่สัมพันธ์กับบันทึกนี้');
        }
    }

    public function updateFeed(Request $request, $dairyId, $useId, $type)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'note'     => 'nullable|string',
            'date'     => 'required|string', // เปลี่ยนจาก date เป็น string
        ]);

        try {
            $dt = Carbon::createFromFormat('d/m/Y H:i', $validated['date']);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['date' => 'วันที่ไม่ถูกต้อง']);
        }
        $formattedDate = $dt->format('Y-m-d H:i:s');

        DB::beginTransaction();
        try {
            $dairy = DairyRecord::findOrFail($dairyId);
            $use = DairyStorehouseUse::findOrFail($useId);
            $oldQuantity = $use->quantity;
            $newQuantity = $validated['quantity'];

            $use->update(['quantity' => $newQuantity]);
            $this->updateNoteAndDate($use, $validated['note'], 'date', $formattedDate);
            $this->updateNoteAndDate($dairy, $validated['note'], 'date', $formattedDate);

            $storehouse = $use->storehouse;
            if (!$storehouse) throw new \Exception('ไม่พบสินค้าจาก DairyStorehouseUse');
            $diff = $newQuantity - $oldQuantity;
            if ($diff > 0) {
                if ($storehouse->stock < $diff) throw new \Exception("สต็อกสินค้าไม่เพียงพอ");
                $storehouse->decrement('stock', $diff);
            } elseif ($diff < 0) {
                $storehouse->increment('stock', abs($diff));
            }

            InventoryMovement::where('storehouse_id', $storehouse->id)
                ->where('batch_id', $dairy->batch_id)
                ->where('change_type', 'out')
                ->latest('id')
                ->first()?->update([
                    'quantity' => $newQuantity,
                    'note'     => 'ปรับแก้การใช้สินค้า (Batch: ' . $dairy->batch_id . ')',
                    'date'     => $formattedDate,
                ]);

            DB::commit();
            return redirect()->route('dairy_records.index')->with('success', 'แก้ไขอาหารเรียบร้อย');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('dairy_records.index')->with('error', $e->getMessage());
        }
    }

    // --- updateMedicine ---
    public function updateMedicine(Request $request, $dairyId, $btId, $type)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'status'   => 'nullable|string',
            'note'     => 'nullable|string',
            'date'     => 'required|string',
        ]);

        try {
            $dt = Carbon::createFromFormat('d/m/Y H:i', $validated['date']);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['date' => 'วันที่ไม่ถูกต้อง']);
        }
        $formattedDate = $dt->format('Y-m-d H:i:s');

        DB::beginTransaction();
        try {
            $bt = BatchTreatment::findOrFail($btId);
            $dairy = DairyRecord::findOrFail($bt->dairy_record_id);
            $oldQuantity = $bt->quantity;
            $newQuantity = $validated['quantity'];

            $bt->update([
                'quantity' => $newQuantity,
                'status'   => $validated['status'] ?? $bt->status,
            ]);

            $this->updateNoteAndDate($bt, $validated['note'], 'date', $formattedDate);
            $this->updateNoteAndDate($dairy, $validated['note'], 'date', $formattedDate);

            $storehouse = Storehouse::where('item_code', $bt->medicine_code)->first();
            if ($storehouse) {
                $diff = $newQuantity - $oldQuantity;
                if ($diff > 0 && $storehouse->stock < $diff) throw new \Exception("สต็อกสินค้าไม่เพียงพอ");
                $storehouse->decrement('stock', $diff);

                InventoryMovement::where('storehouse_id', $storehouse->id)
                    ->where('batch_id', $dairy->batch_id)
                    ->where('change_type', 'out')
                    ->latest('id')
                    ->first()?->update([
                        'quantity' => $newQuantity,
                        'movement_note' => 'ปรับแก้การใช้ยา (Batch: ' . $dairy->batch_id . ')',
                        'movement_date' => $formattedDate,
                    ]);
            }

            DB::commit();
            return redirect()->route('dairy_records.index')->with('success', 'แก้ไขยารักษาเรียบร้อย');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('dairy_records.index')->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    // --- updatePigDeath ---
    public function updatePigDeath(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'cause'    => 'nullable|string',
            'note'     => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $pigDeath = PigDeath::findOrFail($id);
            $batch = $pigDeath->batch;
            if (!$batch) throw new \Exception("ไม่พบ Batch ที่เกี่ยวข้อง");
            $oldQuantity = $pigDeath->quantity;
            $newQuantity = $validated['quantity'];
            $diffQuantity = $newQuantity - $oldQuantity;

            $pigDeath->update([
                'quantity' => $newQuantity,
                'cause'    => $validated['cause'] ?? $pigDeath->cause,
            ]);
            $this->updateNoteAndDate($pigDeath, $validated['note'], 'updated_at', now());

            $batch->total_death += $diffQuantity;
            $batch->current_quantity = max(($batch->current_quantity ?? 0) - $diffQuantity, 0);
            $avgWeightPerPig = ($batch->current_quantity + $diffQuantity > 0 && ($batch->total_pig_weight ?? 0) > 0)
                ? $batch->total_pig_weight / ($batch->current_quantity + $diffQuantity)
                : 0;
            $batch->total_pig_weight = max(($batch->total_pig_weight ?? 0) - ($avgWeightPerPig * $diffQuantity), 0);
            $batch->save();

            if ($pigDeath->pen_id) {
                $allocation = DB::table('batch_pen_allocations')
                    ->where('batch_id', $batch->id)
                    ->where('pen_id', $pigDeath->pen_id)
                    ->first();
                if ($allocation) {
                    $availableInAllocation = $allocation->current_quantity ?? $allocation->allocated_pigs;
                    $reduce = min($diffQuantity, $availableInAllocation);

                    $result = PigInventoryHelper::reducePigInventory(
                        $batch->id,
                        $allocation->pen_id,
                        $reduce,
                        'death'
                    );

                    if (!$result['success']) {
                        // fallback to direct update: adjust current_quantity if present, else allocated_pigs
                        if (property_exists($allocation, 'current_quantity')) {
                            $newCurrent = max(($allocation->current_quantity ?? $allocation->allocated_pigs) - $diffQuantity, 0);
                            DB::table('batch_pen_allocations')
                                ->where('id', $allocation->id)
                                ->update(['current_quantity' => $newCurrent, 'updated_at' => now()]);
                        } else {
                            $newAllocated = max($allocation->allocated_pigs - $diffQuantity, 0);
                            DB::table('batch_pen_allocations')
                                ->where('id', $allocation->id)
                                ->update(['allocated_pigs' => $newAllocated, 'updated_at' => now()]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('dairy_records.index')->with('success', 'แก้ไขข้อมูลหมูตายเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('dairy_records.index')->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }





    //--------------------------------------- DELETE ------------------------------------------//

    public function destroyFeed($id)
    {
        DB::beginTransaction();
        try {
            $use = DairyStorehouseUse::with(['storehouse', 'dairy_record'])->findOrFail($id);

            // คืน stock
            if ($use->storehouse) {
                $use->storehouse->stock += $use->quantity;
                $use->storehouse->save();
            }

            // ลบ InventoryMovements
            InventoryMovement::where('storehouse_id', $use->storehouse_id)
                ->where('batch_id', $use->dairy_record->batch_id)
                ->where('quantity', $use->quantity)
                ->delete();

            // เก็บ DairyRecord ไว้เพื่อลบทีหลัง
            $record = $use->dairy_record;

            // ลบ dairy_storehouse_use
            $use->delete();

            // ถ้า DairyRecord ไม่มีความสัมพันธ์อื่นแล้ว ค่อยลบ
            if (
                $record && $record->dairy_storehouse_uses()->count() == 0 &&
                $record->batch_treatments()->count() == 0 &&
                $record->pig_deaths()->count() == 0
            ) {
                $record->delete();
            }

            DB::commit();
            return back()->with('success', 'ลบข้อมูลอาหารเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function destroyMedicine($id)
    {
        DB::beginTransaction();
        try {
            $treatment = BatchTreatment::with(['dairy_record', 'storehouse'])->findOrFail($id);

            // คืน stock
            if ($treatment->storehouse) {
                $treatment->storehouse->stock += $treatment->quantity;
                $treatment->storehouse->save();
            }

            // ลบ InventoryMovements
            InventoryMovement::where('storehouse_id', $treatment->storehouse_id)
                ->where('batch_id', $treatment->dairy_record->batch_id)
                ->where('quantity', $treatment->quantity)
                ->delete();

            $record = $treatment->dairy_record;

            // ลบ treatment
            $treatment->delete();

            // ถ้า DairyRecord ไม่มีอย่างอื่นเหลือ ให้ลบด้วย
            if (
                $record && $record->dairy_storehouse_uses()->count() == 0 &&
                $record->batch_treatments()->count() == 0 &&
                $record->pig_deaths()->count() == 0
            ) {
                $record->delete();
            }

            DB::commit();
            return back()->with('success', 'ลบข้อมูลการรักษาเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function destroyPigDeath($id)
    {
        DB::beginTransaction();
        try {
            $death = PigDeath::with(['dairy_record', 'batch'])->findOrFail($id);
            $record = $death->dairy_record;
            $batch  = $death->batch;

            // คืนจำนวนหมูให้ batch
            if ($batch) {
                $batch->current_quantity += $death->quantity;
                if ($death->weight) {
                    $batch->total_pig_weight += $death->weight;
                }
                $batch->save();
            }

            // ลบ pig death
            $death->delete();

            // ถ้า DairyRecord ไม่มีอย่างอื่นเหลือ ให้ลบด้วย
            if (
                $record && $record->dairy_storehouse_uses()->count() == 0 &&
                $record->batch_treatments()->count() == 0 &&
                $record->pig_deaths()->count() == 0
            ) {
                $record->delete();
            }

            DB::commit();
            return back()->with('success', 'ลบข้อมูลหมูตายเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }


    //--------------------------------------- EXPORT ------------------------------------------//

    public function exportPdf()
    {
        $dairyRecords = DairyRecord::with([
            'batch.farm',
            'barn',
            'dairy_storehouse_uses.storehouse',
            'dairy_storehouse_uses.barn',
            'batch_treatments.pen',
            'pig_deaths.pen'
        ])->orderBy('id', 'desc')->get();

        $pdf = Pdf::loadView('admin.dairy_records.pdf', [
            'dairyRecords' => $dairyRecords
        ])->setPaper('a4', 'landscape');

        return $pdf->download('dairy_records_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }


    public function exportCsv()
    {
        $filename = 'dairy_records_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $records = DairyRecord::with([
            'batch.farm',
            'barn',
            'dairy_storehouse_uses.storehouse',
            'dairy_storehouse_uses.barn',
            'batch_treatments.pen',
            'pig_deaths.pen'
        ])->orderBy('id', 'desc')->get();

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');

            // Header (Thai)
            fputcsv($handle, ['ฟาร์ม', 'รุ่น', 'เล้า/คอก', 'ประเภท', 'รายละเอียด', 'จำนวน', 'วันที่', 'โน๊ต']);

            foreach ($records as $record) {
                // ให้เป็น string (format) ถ้ามี updated_at
                $updatedAt = $record->updated_at ? $record->updated_at->format('Y-m-d H:i:s') : '';

                // ---------- Feed ----------
                foreach ($record->dairy_storehouse_uses as $use) {
                    fputcsv($handle, [
                        optional(optional($record->batch)->farm)->farm_name ?: '-',
                        optional($record->batch)->batch_code ?: '-',
                        optional($use->barn)->barn_code ?: '-',
                        'อาหาร',
                        'รหัส: ' . (optional($use->storehouse)->item_code ?: '-') . ', หน่วย: ' . (optional($use->storehouse)->unit ?: '-'),
                        $use->quantity,
                        $updatedAt,
                        $record->note ?: '-',
                    ]);
                }

                // ---------- Treatment ----------
                foreach ($record->batch_treatments as $bt) {
                    fputcsv($handle, [
                        optional(optional($record->batch)->farm)->farm_name ?: '-',
                        optional($record->batch)->batch_code ?: '-',
                        (optional($record->barn)->barn_code ?: '-') . '/' . (optional($bt->pen)->pen_code ?: '-'),
                        'การรักษา',
                        'ยา: ' . ($bt->medicine_code ?: '-') . ', หน่วย: ' . ($bt->unit ?: '-') . ', สถานะ: ' . ($bt->status ?: '-'),
                        $bt->quantity,
                        $updatedAt,
                        $record->note ?: '-',
                    ]);
                }

                // ---------- Death ----------
                foreach ($record->pig_deaths as $pd) {
                    fputcsv($handle, [
                        optional(optional($record->batch)->farm)->farm_name ?: '-',
                        optional($record->batch)->batch_code ?: '-',
                        (optional($record->barn)->barn_code ?: '-') . '/' . (optional($pd->pen)->pen_code ?: '-'),
                        'หมูตาย',
                        'คอก: ' . (optional($pd->pen)->pen_code ?: '-'),
                        $pd->quantity,
                        $updatedAt,
                        $record->note ?: '-',
                    ]);
                }

                // หากกรณีแถวใดไม่มี sub-record (เช่น ไม่มี feed/treatment/death) คุณอาจต้องการบันทึกแถว summary ของ DairyRecord เดี่ยวๆ
                // (ผมไม่เพิ่มตรงนี้ตามที่คุณต้องการแสดงเหมือน index — แต่ถ้าต้องการให้มีแถว summary ด้วย แจ้งได้)
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
