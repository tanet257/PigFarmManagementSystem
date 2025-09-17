<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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


class AdminController extends Controller
{
    //--------------------------------------- VIEW ------------------------------------------//
    public function admin_index()
    {
        return view('admin.admin_index');
    }

    //-------------------function_add----------------------//

    //add_batch
    public function add_batch()
    {
        $farms = Farm::all();
        $barns = Barn::all();
        $pens = Pen::all();
        return view('admin.add.add_batch', compact('farms', 'barns', 'pens'));
    }

    //add_farm
    public function add_farm()
    {
        return view('admin.add.add_farm');
    }

    //add_barn
    public function add_barn()
    {
        $farms = Farm::all();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();
        return view('admin.add.add_barn', compact('farms', 'batches'));
    }

    //add_pen
    public function add_pen()
    {
        $barns = Barn::all();
        return view('admin.add.add_pen', compact('barns'));
    }

    //add_batch_treatment
    public function add_batch_treatment()
    {
        $barns = Barn::all();
        $pens = Pen::all();
        $farms = Farm::all();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();
        return view('admin.add.add_batch_treatment', compact('farms', 'batches', 'pens', 'barns'));
    }

    //add_cost
    public function add_cost()
    {
        $farms = Farm::all();
        $storehouses = StoreHouse::select('id', 'item_name', 'farm_id')->get();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();
        return view('admin.add.add_cost', compact('farms', 'batches', 'storehouses'));
    }

    //add_feeding
    public function add_feeding()
    {
        $farms = Farm::all();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();
        return view('admin.add.add_feeding', compact('farms', 'batches'));
    }

    //add_pig_death
    public function add_pig_death()
    {
        $farms = Farm::all();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();
        $barns = Barn::all();
        $pens = Pen::all();
        return view('admin.add.add_pig_death', compact('farms', 'batches', 'barns', 'pens'));
    }

    //add_pig_entry_record
    public function pig_entry_record()
    {
        $farms = Farm::all();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();
        return view('admin.record.pig_entry_record', compact('farms', 'batches'));
    }

    //add_dairy_record
    public function dairy_record()
    {
        $farms = Farm::all();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();
        return view('admin.record.dairy_record', compact('farms', 'batches'));
    }


    //add_pig_sell_record
    public function add_pig_sell_record()
    {
        $farms = Farm::all();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();
        return view('admin.add.add_pig_sell_record', compact('farms', 'batches'));
    }

    //--------------------------------------- UPLOAD ------------------------------------------//

    //upload_barn
    public function upload_barn(Request $request)
    {
        try {
            // validate
            $validated = $request->validate([
                'farm_id' => 'required|exists:farms,id',
                'barn_code' => 'required|string|max:255',
                'pig_capacity' => 'required|integer|min:1',
                'pen_capacity' => 'required|integer|min:1',
                'note' => 'nullable|string',
            ]);

            $farm = Farm::findOrFail($validated['farm_id']);

            // ตรวจสอบจำนวน Barn ไม่เกินที่ farm กำหนด
            $existingBarns = $farm->barns()->count();
            if ($existingBarns >= $farm->barn_capacity) {
                return redirect()->back()->with('error', 'สร้างเล้าเกินจำนวนสูงสุดที่กำหนดสำหรับฟาร์มนี้');
            }

            // ตรวจสอบ barn_code ซ้ำภายในฟาร์ม
            $exists = $farm->barns()->where('barn_code', $validated['barn_code'])->exists();
            if ($exists) {
                return redirect()->back()->with('error', 'รหัสเล้านี้มีอยู่แล้วในฟาร์มนี้');
            }

            // สร้าง Barn ใหม่
            $barn = $farm->barns()->create([
                'barn_code' => $validated['barn_code'],
                'pig_capacity' => $validated['pig_capacity'],
                'pen_capacity' => $validated['pen_capacity'],
                'note' => $validated['note'] ?? null,
            ]);

            return redirect()->back()->with('success', 'เพิ่มเล้าเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มเล้า: ' . $e->getMessage());
        }
    }


    //upload_pen
    public function upload_pen(Request $request)
    {
        try {
            //validate
            $validated = $request->validate([
                'barn_id' => 'required|exists:barns,id',

                'pen_code' => 'required|unique:pens,pen_code',
                'pig_capacity' => 'required|integer',
                'note' => 'nullable|string',
            ]);
            $barn = Barn::findOrFail($validated['barn_id']);

            // ตรวจสอบ pen_code ซ้ำภายในฟาร์ม
            $exists = $barn->pens()->where('pen_code', $validated['pen_code'])->exists();
            if ($exists) {
                return redirect()->back()->with('error', 'รหัสคอกนี้มีอยู่แล้วในเล้านี้');
            }


            $data = new Pen;
            $data->barn_id = $request->barn_id;

            //unique
            $data->pen_code = $request->pen_code;

            $data->pig_capacity = $request->pig_capacity;
            $data->note = $request->note ?? null;

            $data->save();

            return redirect()->back()->with('success', 'เพิ่มคอกเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มคอก' . $e->getMessage());
        }
    }

    //upload_farm
    public function upload_farm(Request $request)
    {
        try {
            // validate
            $request->validate([
                'farm_name' => 'required|unique:farms,farm_name',
                'barn_capacity' => 'required|integer|min:1',
            ]);

            // สร้างฟาร์มใหม่
            Farm::create([
                'farm_name' => trim($request->farm_name),
                'barn_capacity' => $request->barn_capacity,
            ]);

            return redirect()->back()->with('success', 'เพิ่มฟาร์มเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มฟาร์ม: ' . $e->getMessage());
        }
    }




    //upload_batch_treatment
    public function upload_batch_treatment(Request $request)
    {
        try {
            $validated = $request->validate([
                // ทำให้ batch_id ไม่ซ้ำกันภายใน farm_id
                'batch_id' => [
                    'required',
                    Rule::exists('batches', 'id')->where(function ($query) use ($request) {
                        return $query->where('status', 'กำลังเลี้ยง')
                            ->where('farm_id', $request->farm_id);
                    }),
                ],
                'barn_id'  => 'required|exists:barns,id',
                'pen_id'   => 'required|exists:pens,id',
                'medicine_name' => 'required|string',
                'dosage'   => 'required|numeric|min:0',
                'unit'     => 'required|string',
                'note'     => 'nullable|string',
            ]);

            $batch = Batch::findOrFail($validated['batch_id']);

            $data = new BatchTreatment;
            $data->farm_id = $batch->farm_id;
            $data->batch_id = $batch->id;
            $data->barn_id = $validated['barn_id'];
            $data->pen_id  = $validated['pen_id'];

            $data->medicine_name = $validated['medicine_name'];
            $data->dosage = $validated['dosage'];
            $data->unit   = $validated['unit'];
            $data->note   = $validated['note'] ?? null;
            $data->status = $request->status ?? 'วางแผนว่าจะให้ยา';

            $data->save();

            return back()->with('success', 'เพิ่มการรักษาเรียบร้อย');
        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }


    //upload_cost
    public function upload_cost(Request $request)
    {
        try {
            //validate
            $validated = $request->validate([
                // ทำให้ batch_id ไม่ซ้ำกันภายใน farm_id
                'batch_id' => [
                    'required',
                    Rule::exists('batches', 'id')->where(function ($query) use ($request) {
                        return $query->where('status', 'กำลังเลี้ยง')
                            ->where('farm_id', $request->farm_id);
                    }),
                ],
                'barn_id'    => 'nullable|exists:barns,id',
                'pen_id'     => 'nullable|exists:pens,id',
                'cost_type'  => 'required|string',
                'quantity'   => 'required|integer|min:1',
                'price_per_unit' => 'required|numeric|min:0',
                'total_price'    => 'required|numeric|min:0',
                'note'       => 'nullable|string',
            ]);

            $batch = Batch::findOrFail($validated['batch_id']);

            $data = new Cost;
            $data->batch_id = $batch->id;
            $data->farm_id  = $batch->farm_id;
            $data->barn_id  = $validated['barn_id'] ?? null;
            $data->pen_id   = $validated['pen_id'] ?? null;

            $data->cost_type = $request->cost_type;
            $data->quantity = $request->quantity;
            $data->price_per_unit = $request->price_per_unit;
            $data->total_price = $request->quantity * $request->price_per_unit;
            $data->note = $request->note ?? null;

            $data->save();

            return redirect()->back()->with('success', 'เพิ่มค่าใช้จ่ายเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มค่าใช้จ่าย: ' . $e->getMessage());
        }
    }

    //upload_feed
    public function upload_feed(Request $request)
    {
        try {
            $validated = $request->validate([
                // ทำให้ batch_id ไม่ซ้ำกันภายใน farm_id
                'batch_id' => [
                    'required',
                    Rule::exists('batches', 'id')->where(function ($query) use ($request) {
                        return $query->where('status', 'กำลังเลี้ยง')
                            ->where('farm_id', $request->farm_id);
                    }),
                ],
                'barn_id'  => 'required|exists:barns,id',
                'pen_id'   => 'required|exists:pens,id',
                'feed_type' => 'required|string',
                'quantity' => 'required|numeric|min:0',
                'unit'     => 'required|string',
                'date'     => 'required|date',
                'note'     => 'nullable|string',
            ]);

            $batch = Batch::findOrFail($validated['batch_id']);

            $data = new Feeding;
            $data->farm_id = $batch->farm_id;
            $data->batch_id = $batch->id;
            $data->barn_id = $validated['barn_id'];
            $data->pen_id = $validated['pen_id'];

            $data->feed_type = $validated['feed_type'];
            $data->quantity = $validated['quantity'];
            $data->unit = $validated['unit'];
            $data->date = $validated['date'];
            $data->note = $validated['note'] ?? null;

            $data->save();

            return back()->with('success', 'บันทึกการให้อาหารเรียบร้อย');
        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    //upload_pig_death
    public function upload_pig_death(Request $request)
    {
        try {
            //validate
            $validated = $request->validate([
                // ทำให้ batch_id ไม่ซ้ำกันภายใน farm_id
                'batch_id' => [
                    'required',
                    Rule::exists('batches', 'id')->where(function ($query) use ($request) {
                        return $query->where('status', 'กำลังเลี้ยง')
                            ->where('farm_id', $request->farm_id);
                    }),
                ],
                'barn_id'   => 'required|exists:barns,id',
                'pen_id'    => 'required|exists:pens,id',
                'amount'    => 'required|integer|min:1',
                'cause'     => 'nullable|string',
                'date'      => 'nullable|date',
                'note'      => 'nullable|string',
            ]);

            $batch = Batch::findOrFail($validated['batch_id']);

            $data = new PigDeath;
            $data->batch_id = $batch->id;
            $data->farm_id  = $batch->farm_id;
            $data->barn_id  = $request->barn_id;
            $data->pen_id   = $request->pen_id;

            $data->amount = $request->amount;
            $data->cause  = $request->cause ?? null;
            $data->date   = $request->date ?? Carbon::now();
            $data->note   = $request->note ?? null;

            $data->save();

            return redirect()->back()->with('success', 'เพิ่มการตายของหมูเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มการตายของหมู: ' . $e->getMessage());
        }
    }


    //upload_pig_entry_record
    public function upload_pig_entry_record(Request $request)
    {
        try {
            // validate
            $validated = $request->validate([
                // ทำให้ batch_id ไม่ซ้ำกันภายใน farm_id
                'batch_id' => [
                    'required',
                    Rule::exists('batches', 'id')->where(function ($query) use ($request) {
                        return $query->where('status', 'กำลังเลี้ยง')
                            ->where('farm_id', $request->farm_id);
                    }),
                ],

                'pig_entry_date' => 'required|date',
                'total_pig_amount' => 'required|numeric|min:1',
                'total_pig_weight' => 'required|numeric|min:0',
                'total_pig_price' => 'required|numeric|min:0',
                'excess_weight_cost' => 'nullable|numeric|min:0',
                'transport_cost' => 'nullable|numeric|min:0',
                'note' => 'nullable|string',
                'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            $batch = Batch::findOrFail($validated['batch_id']);

            // สร้าง PigEntryRecord
            $data = new PigEntryRecord();
            $data->batch_id = $batch->id;
            $data->farm_id = $batch->farm_id;
            $data->pig_entry_date = $validated['pig_entry_date'];
            $data->total_pig_amount = $validated['total_pig_amount'];
            $data->total_pig_weight = $validated['total_pig_weight'];
            $data->total_pig_price = $validated['total_pig_price'];
            $data->note = $validated['note'] ?? null;

            if ($request->hasFile('receipt_file')) {
                $file = $request->file('receipt_file');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('receipt_files'), $filename);
                $data->receipt_file = $filename;
            } else {
                $data->receipt_file = '-';
            }

            $data->save();

            // สร้าง Cost
            Cost::create([
                'farm_id' => $batch->farm_id,
                'batch_id' => $batch->id,
                'cost_type' => 'piglet',
                'quantity' => $validated['total_pig_amount'],
                'price_per_unit' => $validated['total_pig_price'] / $validated['total_pig_amount'],
                'total_price' => $validated['total_pig_price'],
                'note' => 'ค่าลูกหมู',
                'receipt_file' => $data->receipt_file,
            ]);

            if (!empty($validated['excess_weight_cost']) && $validated['excess_weight_cost'] > 0) {
                Cost::create([
                    'farm_id' => $batch->farm_id,
                    'batch_id' => $batch->id,
                    'cost_type' => 'excess_weight',
                    'quantity' => 1,
                    'price_per_unit' => $validated['excess_weight_cost'],
                    'total_price' => $validated['excess_weight_cost'],
                    'note' => 'ค่าน้ำหนักส่วนเกิน',
                ]);
            }

            if (!empty($validated['transport_cost']) && $validated['transport_cost'] > 0) {
                Cost::create([
                    'farm_id' => $batch->farm_id,
                    'batch_id' => $batch->id,
                    'cost_type' => 'transport',
                    'quantity' => 1,
                    'price_per_unit' => $validated['transport_cost'],
                    'total_price' => $validated['transport_cost'],
                    'note' => 'ค่าขนส่ง',
                ]);
            }

            if ($request->hasFile('receipt_file')) {
                $file = $request->file('receipt_file');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('receipt_files'), $filename);
                $data->receipt_file = $filename;
            } else {
                $data->receipt_file = '-';
            }

            // อัปเดต batch totals
            $batch->total_pig_amount = ($batch->total_pig_amount ?? 0) + $validated['total_pig_amount'];
            $batch->total_pig_weight = ($batch->total_pig_weight ?? 0) + $validated['total_pig_weight'];
            $batch->total_pig_price = ($batch->total_pig_price ?? 0) + $validated['total_pig_price'];
            $batch->save();

            return redirect()->back()->with('success', 'เพิ่มหมูเข้า + บันทึกค่าใช้จ่าย + อัปเดตรุ่นเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    //upload_dairy_record
    public function upload_dairy_record(Request $request)
    {
        try {
            // validate
            $validated = $request->validate([
                // ทำให้ batch_id ไม่ซ้ำกันภายใน farm_id
                'batch_id' => [
                    'required',
                    Rule::exists('batches', 'id')->where(function ($query) use ($request) {
                        return $query->where('status', 'กำลังเลี้ยง')
                            ->where('farm_id', $request->farm_id);
                    }),
                ],
                'pig_entry_date' => 'required|date',
                'total_pig_amount' => 'required|numeric|min:1',
                'total_pig_weight' => 'required|numeric|min:0',
                'total_pig_price' => 'required|numeric|min:0',
                'excess_weight_cost' => 'nullable|numeric|min:0',
                'transport_cost' => 'nullable|numeric|min:0',
                'note' => 'nullable|string',
            ]);

            $batch = Batch::findOrFail($request->input('batch_id'));

            // สร้าง DairyRecord
            $data = new DairyRecord();
            $data->batch_id = $batch->id;
            $data->farm_id = $batch->farm_id;
            $data->pig_entry_date = $validated['pig_entry_date'];
            $data->total_pig_amount = $validated['total_pig_amount'];
            $data->total_pig_weight = $validated['total_pig_weight'];
            $data->total_pig_price = $validated['total_pig_price'];
            $data->note = $validated['note'] ?? null;

            $data->save();

            // สร้าง Feeding

            // สร้าง BatchTreatment

            // สร้าง Storehouse


            // อัปเดต stock totals


            return redirect()->back()->with('success', 'เพิ่มหมูเข้า + บันทึกค่าใช้จ่าย + อัปเดตรุ่นเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    //upload_pig_sell_record
    public function upload_pig_sell_record(Request $request)
    {
        try {
            //validate
            $validated = $request->validate([
                // ทำให้ batch_id ไม่ซ้ำกันภายใน farm_id
                'batch_id' => [
                    'required',
                    Rule::exists('batches', 'id')->where(function ($query) use ($request) {
                        return $query->where('status', 'กำลังเลี้ยง')
                            ->where('farm_id', $request->farm_id);
                    }),
                ],
                'sell_date' => 'required|date',
                'sell_type' => 'required|string',
                'quantity' => 'required|integer',
                'total_weight' => 'required|numeric|min:0',
                'price_per_kg' => 'required|numeric|min:0',
                'total_price' => 'required|numeric|min:0',
                'buyer_name' => 'nullable|string',
                'note' => 'nullable|string',
                'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            $batch = Batch::findOrFail($validated['batch_id']);

            $data = new PigSell;
            $data->batch_id = $batch->batch_id;
            $data->farm_id = $batch->farm_id;
            $data->pig_death_id = $request->pig_death_id ?? null;

            $data->sell_date = $request->sell_date;
            $data->sell_type = $request->sell_type;
            $data->quantity = $request->quantity;
            $data->total_weight = $request->total_weight;
            $data->price_per_kg = $request->price_per_kg;
            $data->total_price = $request->total_weight * $request->price_per_kg;
            $data->buyer_name = $request->buyer_name ?? null;
            $data->note = $request->note ?? null;

            if ($request->hasFile('receipt_file')) {
                $file = $request->file('receipt_file');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('receipt_files'), $filename);
                $data->receipt_file = $filename;
            } else {
                $data->receipt_file = '-';
            }

            $data->save();

            return redirect()->back()->with('success', 'เพิ่มการขายหมูเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มการขายหมู' . $e->getMessage());
        }
    }

    










    //--------------------------------------- VIEW ------------------------------------------//

    public function view_farm()
    {
        $farms = Farm::all();
        return view('admin.view.view_farm', compact('farms'));
    }

    public function view_barn()
    {
        $barns = Barn::all();
        return view('admin.view.view_barn', compact('barns'));
    }

    public function view_pen()
    {
        $pens = Pen::all();
        return view('admin.view.view_pen', compact('pens'));
    }
    public function view_batch(Request $request)
    {
        $query = Batch::query()->with(['farm', 'barn', 'pen']);

        // กรองตามฟาร์ม
        if ($request->filled('farm_id')) {
            $query->where('farm_id', $request->farm_id);
        }

        // เรียงลำดับ
        if ($request->filled('sort_by')) {
            $order = $request->sort_order == 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sort_by, $order);
        } else {
            $query->orderBy('id', 'desc');
        }

        $batches = $query->get();
        $farms = Farm::all();

        return view('admin.view.view_batch', compact('batches', 'farms'));
    }

    public function view_batch_treatment()
    {
        $batch_treatments = BatchTreatment::all();
        return view('admin.view.view_batch_treatment', compact('batch_treatments'));
    }

    public function view_cost()
    {
        $costs = Cost::all();
        return view('admin.view.view_cost', compact('costs'));
    }

    public function view_pig_sell_record()
    {
        $pig_sells = PigSell::all();
        return view('admin.view.view_pig_sell_record', compact('pig_sells'));
    }

    public function view_feeding()
    {
        $feedings = Feeding::all();
        return view('admin.view.view_feeding', compact('feedings'));
    }

    public function view_pig_death()
    {
        $pig_deaths = PigDeath::all();
        return view('admin.view.view_pig_death', compact('pig_deaths'));
    }

    public function view_pig_entry_record()
    {
        $pig_entry_records = PigEntryRecord::with(['batch', 'costs'])->get();
        return view('admin.view.view_pig_entry_record', compact('pig_entry_records'));
    }

    //--------------------------------------- EXPORT ------------------------------------------//





}
