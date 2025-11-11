<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Farm;
use App\Models\Batch;
use App\Models\Barn;
use App\Models\Pen;
use App\Models\DairyRecord;
use App\Models\DairyRecordItem;
use App\Models\StoreHouse;
use App\Models\PigDeath;
use App\Models\BatchTreatment;
use App\Models\Notification;
use App\Models\DairyStorehouseUse;
use App\Models\InventoryMovement;
use App\Helpers\RevenueHelper;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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

        // ✅ NEW: penAllocations - เฉพาะ pen ที่มี current_quantity > 0
        // ใช้ batch_pen_allocations เพื่อให้รู้จำนวนหมูคงเหลือจริงๆ
        $penAllocations = DB::table('batch_pen_allocations')
            ->join('pens', 'batch_pen_allocations.pen_id', '=', 'pens.id')
            ->join('barns', 'batch_pen_allocations.barn_id', '=', 'barns.id')
            ->join('batches', 'batch_pen_allocations.batch_id', '=', 'batches.id')
            ->where('batch_pen_allocations.current_quantity', '>', 0) // เฉพาะที่มีหมูเหลือ
            ->where('batches.status', '!=', 'cancelled')
            ->select(
                'batch_pen_allocations.id as allocation_id',
                'batch_pen_allocations.batch_id',
                'batch_pen_allocations.pen_id',
                'batch_pen_allocations.barn_id',
                'batch_pen_allocations.current_quantity',
                'pens.pen_code',
                'barns.barn_code'
            )
            ->get()
            ->groupBy('batch_id')
            ->map(function ($batchAllocations) {
                return $batchAllocations->map(function ($alloc) {
                    return [
                        'allocation_id'   => $alloc->allocation_id,
                        'pen_id'          => $alloc->pen_id,
                        'barn_id'         => $alloc->barn_id,
                        'current_quantity' => $alloc->current_quantity,
                        'pen_code'        => $alloc->pen_code,
                        'barn_code'       => $alloc->barn_code,
                        'display_text'    => "{$alloc->barn_code}/{$alloc->pen_code} (มีหมู {$alloc->current_quantity} ตัว)",
                    ];
                })->values();
            });

        // pens (ผูกกับ barn_id เอาไว้ filter) - เฉพาะที่มีหมู
        $pens = Pen::select('id', 'barn_id', 'pen_code')
            ->whereIn('id', DB::table('batch_pen_allocations')
                ->where('current_quantity', '>', 0)
                ->distinct('pen_id')
                ->pluck('pen_id'))
            ->get();

        // storehouses (ใช้เป็น dropdown feed/medicine)
        $storehouses = StoreHouse::select('id', 'item_code', 'item_name', 'item_type', 'unit')->get();
        //dd($farms, $batches, $barns, $pens, $storehouses);

        /**
         * สร้าง storehousesByTypeAndBatch เหมือนกับ storehouse pattern
         * แต่ group ตาม type -> batch (เพราะ dairy ต้องแยกตาม batch)
         *
         * ดึงจาก StoreHouse โดยตรง แล้ว join ไปยัง Batch เพื่อให้ได้ batch_id
         * หลังจากนั้น JavaScript จะ filter ตาม batch_id ที่เลือก
         */
        $storehousesByTypeAndBatch = DB::table('storehouses')
            ->join('batches', 'storehouses.farm_id', '=', 'batches.farm_id')
            ->select('storehouses.item_type', 'batches.id as batch_id', 'storehouses.item_code', 'storehouses.item_name')
            ->where('batches.status', '!=', 'เสร็จสิ้น')
            ->where('batches.status', '!=', 'cancelled')
            ->distinct('storehouses.item_code')
            ->orderBy('storehouses.item_type')
            ->orderBy('batches.id')
            ->get()
            ->groupBy('item_type')
            ->map(function ($typeGroup) {
                return $typeGroup->groupBy('batch_id')->map(function ($batchGroup) {
                    return $batchGroup->mapWithKeys(function ($item) {
                        return [$item->item_code => [
                            'item_code' => $item->item_code,
                            'item_name' => $item->item_name,
                        ]];
                    });
                });
            });

        return view(
            'admin.dairy_records.record.dairy_record',
            compact('farms', 'batches', 'barns', 'pens', 'storehouses', 'storehousesByTypeAndBatch', 'penAllocations')
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
            'items', // Load DairyRecordItem
            'items.storehouse',
            'items.pen',
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
        $typesByRecord = []; // เก็บ display_types ในอาร์เรย์แยก

        foreach ($dairyRecords as $record) {
            // Default values
            $record->display_barn = '-';
            $record->display_details = '-';
            $record->display_quantity = '-';

            // ✅ NEW: ใช้ DairyRecordItem แทน (ข้อมูลล่าสุด)
            $items = $record->items ?? collect();

            // Feed items
            $feedItems = $items->filter(fn($item) => $item->item_type === 'feed');

            // Medicine items
            $medicineItems = $items->filter(fn($item) => $item->item_type === 'medicine');

            // Death items
            $deathItems = $items->filter(fn($item) => $item->item_type === 'death');

            // เก็บประเภททั้งหมด
            $displayTypes = [];
            $detailsArray = [];
            $barnCodes = [];
            $totalQuantity = 0;

            // ===== FEED ITEMS =====
            if ($feedItems->count()) {
                $displayTypes[] = 'feed';
                foreach ($feedItems as $item) {
                    $storehouse = $item->storehouse;
                    $barn = $item->barn;
                    $detail = 'รหัส: ' . ($storehouse->item_code ?? '-')
                            . ', หน่วย: ' . ($item->unit ?? $storehouse->unit ?? '-')
                            . ($item->note ? ', ' . $item->note : '')
                            . ' (' . $item->quantity . ')';
                    $detailsArray[] = $detail;

                    if ($barn && !in_array($barn->barn_code, $barnCodes)) {
                        $barnCodes[] = $barn->barn_code;
                    }
                    $totalQuantity += $item->quantity;
                }
            }

            // ===== MEDICINE ITEMS =====
            if ($medicineItems->count()) {
                $displayTypes[] = 'medicine';
                foreach ($medicineItems as $item) {
                    $pen = $item->pen;
                    $barn = $item->barn;
                    $detail = 'ยา: ' . ($item->medicine_code ?? '-')
                            . ', หน่วย: ' . ($item->unit ?? '-')
                            . ', สถานะ: ' . ($item->treatment_status ?? '-')
                            . ($item->note ? ', ' . $item->note : '')
                            . ' (' . $item->quantity . ')';
                    $detailsArray[] = $detail;

                    // เก็บ barn/pen code
                    if ($barn) {
                        $barnCode = $barn->barn_code;
                        if ($pen) {
                            $barnCode .= '/' . $pen->pen_code;
                        }
                        if (!in_array($barnCode, $barnCodes)) {
                            $barnCodes[] = $barnCode;
                        }
                    }
                    $totalQuantity += $item->quantity;
                }
            }

            // ===== DEATH ITEMS =====
            if ($deathItems->count()) {
                $displayTypes[] = 'death';
                foreach ($deathItems as $item) {
                    $pen = $item->pen;
                    $barn = $item->barn;
                    $detail = 'คอก: ' . (optional($pen)->pen_code ?? '-')
                            . ($item->note ? ', ' . $item->note : '')
                            . ' (' . $item->quantity . ')';
                    $detailsArray[] = $detail;

                    if ($barn && $pen) {
                        $barnPenCode = $barn->barn_code . '/' . $pen->pen_code;
                        if (!in_array($barnPenCode, $barnCodes)) {
                            $barnCodes[] = $barnPenCode;
                        }
                    }
                    $totalQuantity += $item->quantity;
                }
            }

            // เก็บประเภทสำหรับ filter badges
            $typesByRecord[$record->id] = $displayTypes;

            // Set display values
            if (empty($displayTypes)) {
                // ถ้าไม่มี items เลย ใช้ข้อมูลเก่า (dairy_storehouse_uses, batch_treatments, pig_deaths)
                $feedUses = $record->dairy_storehouse_uses->filter(function($use) {
                    return $use->storehouse && $use->storehouse->item_type === 'food';
                });
                $medicines = $record->batch_treatments;
                $deaths = $record->pig_deaths;

                if ($feedUses->count()) {
                    $use = $feedUses->first();
                    $record->display_barn = optional($use->barn)->barn_code ?? optional($record->barn)->barn_code ?? '-';
                    $record->display_quantity = $feedUses->sum('quantity');
                    $record->display_details = $feedUses->map(fn($u) => 'รหัส: ' . (optional($u->storehouse)->item_code ?? '-') . ' (' . $u->quantity . ')')->implode(' | ');
                } elseif ($medicines->count()) {
                    $bt = $medicines->first();
                    $record->display_barn = (optional($record->barn)->barn_code ?? '-') . '/' . (optional($bt->pen)->pen_code ?? '-');
                    $record->display_quantity = $medicines->sum('quantity');
                    $record->display_details = $medicines->map(fn($m) => 'ยา: ' . ($m->medicine_code ?? '-') . ' (' . $m->quantity . ')')->implode(' | ');
                } elseif ($deaths->count()) {
                    $pd = $deaths->first();
                    $record->display_barn = (optional($record->barn)->barn_code ?? '-') . '/' . (optional($pd->pen)->pen_code ?? '-');
                    $record->display_quantity = $deaths->sum('quantity');
                    $record->display_details = $deaths->map(fn($d) => 'คอก: ' . (optional($d->pen)->pen_code ?? '-') . ' (' . $d->quantity . ')')->implode(' | ');
                } else {
                    $record->display_barn = optional($record->barn)->barn_code ?? '-';
                    $record->display_details = $record->note ?? '-';
                    $record->display_quantity = '-';
                }
            } else {
                // มี items จาก DairyRecordItem
                $record->display_barn = !empty($barnCodes) ? implode(' | ', $barnCodes) : optional($record->barn)->barn_code ?? '-';
                $record->display_details = !empty($detailsArray) ? implode(' | ', $detailsArray) : '-';
                $record->display_quantity = $totalQuantity > 0 ? $totalQuantity : '-';
            }
        }

        // Map feed quantity จาก dairy_storehouse_uses (ใช้ logic เดียวกับ loop แรก: item_type = 'food')
        foreach ($dairyRecords as $record) {
            $record->feed_uses = $record->dairy_storehouse_uses
                ->filter(function($dsu) {
                    return $dsu->storehouse && $dsu->storehouse->item_type === 'food';
                })
                ->map(function ($dsu) {
                    // ใช้ quantity ที่บันทึกตรง ๆ ใน dairy_storehouse_uses
                    $dsu->quantity_from_dsu = $dsu->quantity ?? 0;
                    return $dsu;
                })
                ->values();
        }

        return view('admin.dairy_records.index', compact('farms', 'batches', 'barns', 'dairyRecords', 'typesByRecord'));
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
            // ✅ FIX: Feed/Medicine ต้องมี item_code AND barn_id ทั้งคู่ (ใช้ && ไม่ใช่ ||)
            $feedUses = collect($request->feed_use ?? [])
                ->filter(fn($row) => !empty($row['item_code']) && !empty($row['barn_id']))
                ->values()
                ->all();

            $medicineUses = collect($request->medicine_use ?? [])
                ->filter(fn($row) => !empty($row['item_code']) && !empty($row['barn_id']))
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
                            // Get storehouse for this farm and item_code
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

                            // ✅ NEW: สร้าง DairyRecordItem เพื่อ unified display
                            DairyRecordItem::create([
                                'dairy_record_id' => $dairyId,
                                'item_type'       => 'feed',
                                'storehouse_id'   => $storehouse->id,
                                'barn_id'         => $barnId,
                                'quantity'        => $usedQuantity,
                                'unit'            => $storehouse->unit ?? $validated['unit'] ?? null,
                                'note'            => $validated['note'] ?? null,
                            ]);

                            // ✅ ทำให้ InventoryMovement ถูกสร้างทุกครั้ง (ไม่ว่า quantity เป็นเท่าไหร่)
                            // เพราะ Observer ต้องใช้ change_type='out' เพื่อ trigger KPI calculation
                            $invMovement = InventoryMovement::create([
                                'storehouse_id' => $storehouse->id,
                                'batch_id'      => $validated['batch_id'],
                                'change_type'   => 'out',
                                'quantity'      => max($usedQuantity, 1), // ✅ Minimum 1 เพื่อ trigger Observer
                                'note'          => 'ใช้สินค้า (Batch: ' . $validated['batch_id'] . ')',
                                'date'          => $dt->format('Y-m-d H:i:s'),
                            ]);

                            // ✅ Link InventoryMovement กับ DairyRecordItem
                            $lastItem = DairyRecordItem::where('dairy_record_id', $dairyId)
                                ->where('item_type', 'feed')
                                ->latest('id')
                                ->first();
                            if ($lastItem) {
                                $invMovement->update(['dairy_record_item_id' => $lastItem->id]);
                            }

                            // ✅ ลดสต็อกเฉพาะเมื่อ usedQuantity > 0
                            if ($usedQuantity > 0) {
                                if ($storehouse->stock < $usedQuantity) {
                                    throw new \Exception("สินค้า {$validated['item_name']} ({$validated['item_code']}) สต็อกไม่พอ (เหลือ {$storehouse->stock}, ต้องการ {$usedQuantity})");
                                }
                                $storehouse->stock -= $usedQuantity;
                                $storehouse->save();
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

                            // ✅ NEW: สร้าง DairyRecordItem เพื่อ unified display
                            DairyRecordItem::create([
                                'dairy_record_id'   => $dairyId,
                                'item_type'         => 'medicine',
                                'medicine_code'     => $validated['item_code'],
                                'batch_id'          => $validated['batch_id'],
                                'pen_id'            => $penId,
                                'barn_id'           => $barnId,
                                'quantity'          => $usedQuantity,
                                'unit'              => $storehouse->unit ?? null,
                                'treatment_status'  => $validated['status'] ?? 'วางแผนว่าจะให้ยา',
                                'treatment_date'    => $dt->toDateString(),
                                'note'              => $validated['note'] ?? null,
                            ]);

                            DairyStorehouseUse::create([
                                'dairy_record_id' => $dairyId,
                                'storehouse_id'   => $storehouse->id,
                                'barn_id'         => $barnId,
                                'quantity'        => $usedQuantity,
                                'date'            => $formattedDate,
                                'note'            => $validated['note'] ?? null,
                            ]);

                            // ✅ ทำให้ InventoryMovement ถูกสร้างทุกครั้ง (ไม่ว่า quantity เป็นเท่าไหร่)
                            // เพราะ Observer ต้องใช้ change_type='out' เพื่อ trigger KPI calculation
                            InventoryMovement::create([
                                'storehouse_id' => $storehouse->id,
                                'batch_id'      => $validated['batch_id'],
                                'change_type'   => 'out',
                                'quantity'      => max($usedQuantity, 1), // ✅ Minimum 1 เพื่อ trigger Observer
                                'note'          => 'ใช้สินค้า (Batch: ' . $validated['batch_id'] . ')',
                                'date'          => $formattedDate,
                            ]);

                            // ✅ ลดสต็อกเฉพาะเมื่อ usedQuantity > 0
                            if ($usedQuantity > 0) {
                                if ($storehouse->stock < $usedQuantity) {
                                    throw new \Exception("สินค้า {$validated['item_name']} ({$validated['item_code']}) สต็อกไม่พอ (เหลือ {$storehouse->stock}, ต้องการ {$usedQuantity})");
                                }
                                $storehouse->stock -= $usedQuantity;
                                $storehouse->save();
                            }
                        }

                        // --- PigDeath ---
                        if ($section === 'pigdeath') {
                            $deadQuantity = $validated['quantity'] ?? 0;
                            if ($deadQuantity <= 0) continue;

                            $batch = Batch::find($validated['batch_id']);
                            if (!$batch || $batch->status === 'cancelled') continue;

                            // ✅ คำนวน weight ก่อนเพราะต้องใช้ current_quantity เดิม
                            $currentAmount = $batch->current_quantity ?? $batch->total_pig_amount;
                            $avgWeightPerPig = ($currentAmount > 0 && ($batch->total_pig_weight ?? 0) > 0)
                                ? $batch->total_pig_weight / $currentAmount
                                : 0;

                            // ✅ เพิ่ม total_death แต่ไม่ลด current_quantity ที่นี่ (จะลดใน reduceCurrentQuantityOnly)
                            $batch->total_death += $deadQuantity;
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
                                        // ✅ ใช้ reduceCurrentQuantityOnly เพราะ allocated_pigs ไม่ควรเปลี่ยน
                                        $availableInAllocation = $allocation->current_quantity ?? $allocation->allocated_pigs;
                                        $reduce = min($remainingDead, $availableInAllocation);

                                        $result = PigInventoryHelper::reduceCurrentQuantityOnly(
                                            $batch->id,
                                            $allocation->pen_id,
                                            $reduce
                                        );

                                        if (!$result['success']) {
                                            // fallback: ลดเฉพาะ current_quantity เท่านั้น
                                            $newCurrent = max(($allocation->current_quantity ?? $allocation->allocated_pigs) - $reduce, 0);
                                            DB::table('batch_pen_allocations')
                                                ->where('id', $allocation->id)
                                                ->update([
                                                    'current_quantity' => $newCurrent,
                                                    'updated_at'     => now(),
                                                ]);

                                            // ✅ FIX: ต้องลด batch->current_quantity ใน fallback ด้วย
                                            $batch->current_quantity = max(($batch->current_quantity ?? 0) - $reduce, 0);
                                            $batch->save();
                                        }
                                    }
                                }

                                $pigDeathData = [
                                    'dairy_record_id' => $dairyId,
                                    'batch_id'        => $batch->id,
                                    'pen_id'          => $penId2,
                                    'quantity'        => $reduce,
                                    'cause'           => $validated['cause'] ?? null,
                                    'note'            => $validated['note'] ?? null,
                                    'date'            => $formattedDate,
                                    'status'          => 'recorded', // ✅ NEW: บันทึกแล้ว
                                    'recorded_by'     => Auth::id(), // ✅ FIX: Auth::id()
                                ];

                                Log::info('Creating PigDeath', $pigDeathData);
                                $createdDeath = PigDeath::create($pigDeathData);
                                Log::info('PigDeath Created', ['id' => $createdDeath->id, 'batch_id' => $createdDeath->batch_id]);

                                // ✅ NEW: สร้าง DairyRecordItem เพื่อ unified display
                                DairyRecordItem::create([
                                    'dairy_record_id' => $dairyId,
                                    'item_type'       => 'death',
                                    'batch_id'        => $batch->id,
                                    'pen_id'          => $penId2,
                                    'barn_id'         => $barnId2,
                                    'quantity'        => $reduce,
                                    'death_date'      => $dt->toDateString(),
                                    'note'            => $validated['note'] ?? null,
                                ]);

                                // ✅ NEW: สร้าง notification ให้ admin ทั้งหมด
                                try {
                                    $recordedByName = $createdDeath->recordedBy?->name ?? 'ไม่ระบุ';
                                    $message = "บันทึกหมูตาย {$createdDeath->quantity} ตัว (รุ่น: {$batch->batch_code}, ผู้บันทึก: {$recordedByName})";

                                    // หา admin ทั้งหมด
                                    $admins = \App\Models\User::whereHas('roles', function ($query) {
                                        $query->where('name', 'admin');
                                    })->get();

                                    foreach ($admins as $admin) {
                                        Notification::create([
                                            'user_id'   => $admin->id, // ✅ FIX: ส่งให้ admin แต่ละคน
                                            'title'     => 'บันทึกหมูตาย',
                                            'message'   => $message,
                                            'type'      => 'pig_death',
                                            'related_model' => 'PigDeath',
                                            'related_model_id' => $createdDeath->id,
                                            'is_read'    => false,
                                        ]);
                                    }
                                    Log::info('Notification created for PigDeath', ['pig_death_id' => $createdDeath->id, 'admin_count' => count($admins)]);
                                } catch (\Exception $notifError) {
                                    Log::error('Failed to create notification', [
                                        'pig_death_id' => $createdDeath->id,
                                        'error' => $notifError->getMessage()
                                    ]);
                                }

                                $remainingDead -= $reduce;
                                if ($remainingDead <= 0) break;
                            }
                        }
                    }
                }
            }

            // ✅ NEW: อัปเดท profit หลังบันทึกหมูตายเสร็จ
            // ✅ FIX: ใช้ batch_id จาก first row ของ request (ทุก row มี batch_id เดียวกัน)
            try {
                if (!empty($deadPigs) && !empty($deadPigs[0]['batch_id'])) {
                    $batchId = $deadPigs[0]['batch_id'];
                    RevenueHelper::calculateAndRecordProfit($batchId);
                    Log::info('Profit recalculated for batch', ['batch_id' => $batchId]);
                }
            } catch (\Exception $profitError) {
                Log::error('Failed to recalculate profit', [
                    'error' => $profitError->getMessage()
                ]);
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

            // ✅ เพิ่ม total_death แต่ไม่ลด current_quantity ที่นี่ (จะลดใน reduceCurrentQuantityOnly)
            $batch->total_death += $diffQuantity;
            $avgWeightPerPig = (($batch->current_quantity ?? 0) + $diffQuantity > 0 && ($batch->total_pig_weight ?? 0) > 0)
                ? $batch->total_pig_weight / (($batch->current_quantity ?? 0) + $diffQuantity)
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

                    // ✅ ใช้ reduceCurrentQuantityOnly เพราะ allocated_pigs ไม่ควรเปลี่ยน
                    $result = PigInventoryHelper::reduceCurrentQuantityOnly(
                        $batch->id,
                        $allocation->pen_id,
                        $reduce
                    );

                    if (!$result['success']) {
                        // fallback: ลดเฉพาะ current_quantity เท่านั้น
                        $newCurrent = max(($allocation->current_quantity ?? $allocation->allocated_pigs) - $diffQuantity, 0);
                        DB::table('batch_pen_allocations')
                            ->where('id', $allocation->id)
                            ->update(['current_quantity' => $newCurrent, 'updated_at' => now()]);
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


    public function exportCsv(Request $request)
    {
        $filename = 'dairy_records_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $query = DairyRecord::with([
            'batch.farm',
            'barn',
            'dairy_storehouse_uses.storehouse',
            'dairy_storehouse_uses.barn',
            'batch_treatments.pen',
            'pig_deaths.pen'
        ]);

        // Apply export-specific date range filter
        if ($request->filled('export_date_from') && $request->filled('export_date_to')) {
            $query->whereBetween('created_at', [$request->export_date_from . ' 00:00:00', $request->export_date_to . ' 23:59:59']);
        }

        $records = $query->orderBy('id', 'desc')->get();

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
