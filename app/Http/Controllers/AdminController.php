<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use Carbon\Carbon;

use App\Models\Barn;
use App\Models\Pen;
use App\Models\Farm;
use App\Models\Batch;
use App\Models\BatchTreatments;
use App\Models\Cost;
use App\Models\PigSell;
use App\Models\Feeding;
use App\Models\PigDeath;



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
        return view('admin.add.add_barn');
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
        return view('admin.add.add_batch_treatment');
    }

    //add_cost
    public function add_cost()
    {
        return view('admin.add.add_cost');
    }

    //add_pig_sell
    public function add_pig_sell()
    {
        return view('admin.add.add_pig_sell');
    }

    //add_feeding
    public function add_feeding()
    {
        return view('admin.add.add_feeding');
    }

    //add_pig_death
    public function add_pig_death()
    {
        return view('admin.add.add_pig_death');
    }

    //--------------------------------------- UPLOAD ------------------------------------------//

    //upload_barn
    public function upload_barn(Request $request)
    {
        try{
        //validate
        $request->validate([
            'barn_code' => 'required|unique:barns,barn_code',
            'pig_capacity' => 'required|integer',
            'pen_capacity' => 'required|integer',
            'note' => 'nullable|string',
        ]);

        $data = new Barn;

        //unique
        $data->barn_code = $request->barn_code;

        $data->pig_capacity = $request->pig_capacity;
        $data->pen_capacity = $request->pen_capacity;
        $data->note = $request->note ?? null;

        $data->save();

        return redirect()->back()->with('success', 'เพิ่มเล้าเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มเล้า'.$e->getMessage());
        }
    }

    //upload_pen
    public function upload_pen(Request $request)
    {
        try {
            //validate
            $request->validate([
            'barn_id' => 'required|exists:barns,id',

            'pen_code' => 'required|unique:pens,pen_code',
            'pig_capacity' => 'required|integer',
            'note' => 'nullable|string',
        ]);

        $data = new Pen;

        //fk
        $data->barn_id = $request->barn_id;

        //unique
        $data->pen_code = $request->pen_code;

        $data->pig_capacity = $request->pig_capacity;
        $data->note = $request->note ?? null;

        $data->save();

        return redirect()->back()->with('success', 'เพิ่มคอกเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มคอก'.$e->getMessage());
        }
    }

    //upload_farm
    public function upload_farm(Request $request)
    {
        try {
            //validate
            $request->validate([
                'farm_name' => 'required|unique:farms,farm_name',

        ]);

        $data = new Farm;

        //unique
        $data->farm_name = $request->farm_name;

        $data->save();

        return redirect()->back()->with('success', 'เพิ่มฟาร์มเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มฟาร์ม'.$e->getMessage());
        }
    }

    //upload_batch
    public function upload_batch(Request $request)
    {
    try{
    //validate
    $request->validate([
        'farm_id' => 'required|exists:farms,id',
        'barn_id' => 'required|exists:barns,id',
        'pen_id' => 'required|exists:pens,id',

        'batch_code' => 'required|unique:batches,batch_code',

        'total_pig_weight' => 'required|numeric|min:0',
        'total_pig_amount' => 'required|numeric|min:0',
        'initial_pig_amount' => 'required|numeric|min:0',
        'total_pig_price' => 'required|numeric|min:0',

        //'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',


    ]);

    $data = new Batch;

    //fk
    $data->farm_id = $request->farm_id;
    $data->barn_id = $request->barn_id;
    $data->pen_id = $request->pen_id;

    //unique
    $data->batch_code = $request->batch_code;

    $data->total_pig_weight = $request->total_pig_weight;
    $data->total_pig_amount = $request->total_pig_amount;
    $data->initial_pig_amount = $request->initial_pig_amount;
    $data->total_pig_price = $request->total_pig_price;

    $data->status = $request->status ?? 'กำลังเลี้ยง';

    $data->note = $request->note ?? null;

    $data->start_date = Carbon::now(); // เวลาปัจจุบัน
    $data->end_date = $request->end_date;

    $data->save();

    return redirect()->back()->with('success', 'เพิ่มรุ่นเรียบร้อย');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มรุ่น'.$e->getMessage());
    }
    }

    //upload_batch_treatment
    public function upload_batch_treatment(Request $request)
    {
        try{
        //validate
        $request->validate([
            'barn_id' => 'required|exists:barns,id',
            'pen_id' => 'required|exists:pens,id',
            'batch_id' => 'required|exists:batches,id',
            'farm_id' => 'required|exists:farms,id',

            'medicine_name' => 'required|string',
            'dosage' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'note' => 'nullable|string',
        ]);

        $data = new BatchTreatments;

        //fk
        $data->barn_id = $request->barn_id;
        $data->pen_id = $request->pen_id;
        $data->batch_id = $request->batch_id;
        $data->farm_id = $request->farm_id;

        $data->medicine_name = $request->medicine_name;
        $data->dosage = $request->dosage;
        $data->status = $request->status ?? 'วางแผนว่าจะให้ยา';
        $data->unit = $request->unit;
        $data->note = $request->note ?? null;

        $data->save();

        return redirect()->back()->with('success', 'เพิ่มการรักษาเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มการรักษา'.$e->getMessage());
        }
    }

    //upload_cost
    public function upload_cost(Request $request)
    {
        try {
            //validate
            $request->validate([
                'farm_id' => 'required|exists:farms,id',
                'batch_id' => 'required|exists:batches,id',

            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|integer',
            'price_per_unit' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $data = new Cost;

        //fk
        $data->farm_id = $request->farm_id;
        $data->batch_id = $request->batch_id;

        //unique
        $data->cost_type = $request->cost_type;

        $data->amount = $request->amount;
        $data->quantity = $request->quantity;
        $data->total_price = $request->quantity * $request->price_per_unit;
        $data->total_price = $request->total_price;
        $data->note = $request->note ?? null;

        $data->save();

        return redirect()->back()->with('success', 'เพิ่มค่าใช้จ่ายเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มค่าใช้จ่าย'.$e->getMessage());
        }
    }

    //upload_pig_sell
    public function upload_pig_sell(Request $request)
    {
        try {
            //validate
            $request->validate([
                'farm_id' => 'required|exists:farms,id',
                'batch_id' => 'required|exists:batches,id',

            'sell_type' => 'required|string',
            'quantity' => 'required|integer',
            'total_weight' => 'required|numeric|min:0',
            'price_per_kg' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'buyer_name' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $data = new PigSell;

        //fk
        $data->farm_id = $request->farm_id;
        $data->batch_id = $request->batch_id;
        $data->pig_death_id = $request->pig_death_id ?? null;

        $data->sell_type = $request->sell_type;
        $data->quantity = $request->quantity;
        $data->total_weight = $request->total_weight;
        $data->price_per_kg = $request->price_per_kg;
        $data->total_price = $request->total_weight * $request->price_per_kg;
        $data->buyer_name = $request->buyer_name ?? null;
        $data->note = $request->note ?? null;

        $data->save();

        return redirect()->back()->with('success', 'เพิ่มการขายหมูเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มการขายหมู'.$e->getMessage());
        }
    }

    //upload_feeding
    public function upload_feeding(Request $request)
    {
        try {
            //validate
            $request->validate([
                'farm_id' => 'required|exists:farms,id',
                'batch_id' => 'required|exists:batches,id',

            'feed_type' => 'required|string',
            'quantity' => 'required|integer',
            'unit' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'price_per_unit' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $data = new Feeding;

        //fk
        $data->farm_id = $request->farm_id;
        $data->batch_id = $request->batch_id;

        $data->feed_type = $request->feed_type;
        $data->quantity = $request->quantity;
        $data->unit = $request->unit;
        $data->amount = $request->amount;
        $data->price_per_unit = $request->price_per_unit;
        $data->total = $request->total;
        $data->note = $request->note ?? null;

        $data->save();

        return redirect()->back()->with('success', 'เพิ่มการให้อาหารเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มการให้อาหาร'.$e->getMessage());
        }
    }

    //upload_pig_death
    public function upload_pig_death(Request $request)
    {
        try {
            //validate
            $request->validate([
                'farm_id' => 'required|exists:farms,id',
                'batch_id' => 'required|exists:batches,id',
                'pen_id' => 'required|exists:pens,id',

            'amount' => 'required|integer',
            'cause' => 'required|string',
            'note' => 'nullable|string',
        ]);

        $data = new PigDeath;

        //fk
        $data->farm_id = $request->farm_id;
        $data->batch_id = $request->batch_id;
        $data->pen_id = $request->pen_id;

        $data->amount = $request->amount;
        $data->cause = $request->cause ?? null;
        $data->note = $request->note ?? null;

        $data->save();

        return redirect()->back()->with('success', 'เพิ่มการตายของหมูเรียบร้อย');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการเพิ่มการตายของหมู'.$e->getMessage());
        }
    }

//--------------------------------------- VIEW ------------------------------------------//


    public function view_batch()
    {
    $batches = Batch::all();
    return view('admin.view.view_batch',compact('batches'));
    }

    public function view_farm()
    {
    $farms = Farm::all();
    return view('admin.view.view_farm',compact('farms'));
    }

    public function view_barn()
    {
    $barns = Barn::all();
    return view('admin.view.view_barn',compact('barns'));
    }

    public function view_pen()
    {
    $pens = Pen::all();
    return view('admin.view.view_pen',compact('pens'));
    }

    public function view_batch_treatment()
    {
    $batch_treatments = BatchTreatments::all();
    return view('admin.view.view_batch_treatment',compact('batch_treatments'));
    }

    public function view_cost()
    {
    $costs = Cost::all();
    return view('admin.view.view_cost',compact('costs'));
    }

    public function view_pig_sell()
    {
    $pig_sells = PigSell::all();
    return view('admin.view.view_pig_sell',compact('pig_sells'));
    }

    public function view_feeding()
    {
    $feedings = Feeding::all();
    return view('admin.view.view_feeding',compact('feedings'));
    }

    public function view_pig_death()
    {
    $pig_deaths = PigDeath::all();
    return view('admin.view.view_pig_death',compact('pig_deaths'));
    }

}
