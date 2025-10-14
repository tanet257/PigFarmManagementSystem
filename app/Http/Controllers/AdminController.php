<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Barn;
use App\Models\Pen;
use App\Models\Farm;
use App\Models\Batch;
use App\Models\BatchTreatment;
use App\Models\Cost;
use App\Models\PigSale;
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


}
