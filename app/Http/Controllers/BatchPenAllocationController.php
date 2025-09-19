<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BatchPenAllocation;
use App\Models\Batch;
use App\Models\Barn;
use App\Models\Pen;

class BatchPenAllocationController extends Controller
{
    // ---------- Index ----------
    public function index(Request $request)
    {
        $batches = Batch::all();
        $barns = Barn::all();
        $pens = Pen::all();

        $allocations = BatchPenAllocation::with(['batch', 'barn', 'pen'])
            ->orderBy('move_date', 'desc')
            ->paginate($request->get('per_page', 10));

        return view('admin.batch_pen_allocations.index', compact('allocations', 'batches', 'barns', 'pens'));
    }

    // ---------- Create ----------
    public function create(Request $request)
    {
        $validated = $request->validate([
            'batch_id'   => 'required|exists:batches,id',
            'barn_id'    => 'required|exists:barns,id',
            'pen_id'     => 'required|exists:pens,id',
            'pig_amount' => 'required|integer|min:1',
            'move_date'  => 'required|date',
            'note'       => 'nullable|string',
        ]);

        BatchPenAllocation::create($validated);

        return redirect()->back()->with('success', 'บันทึก allocation สำเร็จ');
    }

    // ---------- Edit ----------
    public function edit($id)
    {
        $allocation = BatchPenAllocation::findOrFail($id);
        $batches = Batch::all();
        $barns = Barn::all();
        $pens = Pen::all();

        return view('admin.batch_pen_allocations.edit', compact('allocation', 'batches', 'barns', 'pens'));
    }

    // ---------- Update ----------
    public function update(Request $request, $id)
    {
        $allocation = BatchPenAllocation::findOrFail($id);

        $validated = $request->validate([
            'batch_id'   => 'required|exists:batches,id',
            'barn_id'    => 'required|exists:barns,id',
            'pen_id'     => 'required|exists:pens,id',
            'pig_amount' => 'required|integer|min:1',
            'move_date'  => 'required|date',
            'note'       => 'nullable|string',
        ]);

        $allocation->update($validated);

        return redirect()->back()->with('success', 'แก้ไข allocation สำเร็จ');
    }

    // ---------- Delete ----------
    public function delete($id)
    {
        $allocation = BatchPenAllocation::findOrFail($id);
        $allocation->delete();

        return redirect()->back()->with('success', 'ลบ allocation สำเร็จ');
    }
}
