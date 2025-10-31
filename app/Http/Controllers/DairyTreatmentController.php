<?php

namespace App\Http\Controllers;

use App\Models\DairyTreatment;
use App\Models\Farm;
use App\Models\Batch;
use App\Models\Barn;
use App\Models\Pen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DairyTreatmentController extends Controller
{
    /**
     * Display a listing of dairy treatments
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $farm = $user->farm ?? Farm::first();

        $query = DairyTreatment::where('farm_id', $farm->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by treatment type
        if ($request->filled('treatment_type')) {
            $query->where('treatment_type', $request->treatment_type);
        }

        // Filter by barn
        if ($request->filled('barn_id')) {
            $query->where('barn_id', $request->barn_id);
        }

        // Filter by batch
        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        // Search by disease name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('treatment_name', 'like', "%{$search}%")
                  ->orWhere('disease_description', 'like', "%{$search}%")
                  ->orWhere('medicine_name', 'like', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->get('sort', 'start_date');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        $treatments = $query->paginate($request->get('per_page', 15));

        // Load relationships
        $barns = $farm->barns()->get();
        $batches = $farm->batches()->get();

        return view('admin.dairy_treatment.index', compact('treatments', 'barns', 'batches', 'farm'));
    }

    /**
     * Show the form for creating a new treatment
     */
    public function create()
    {
        $user = Auth::user();
        $farm = $user->farm ?? Farm::first();

        $barns = $farm->barns()->get();
        $batches = $farm->batches()->where('status', 'raising')->get();
        $pens = Pen::all();

        return view('admin.dairy_treatment.create', compact('farm', 'barns', 'batches', 'pens'));
    }

    /**
     * Store a newly created treatment in storage
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $farm = $user->farm ?? Farm::first();

        $validated = $request->validate([
            'treatment_name' => 'required|string|max:255',
            'disease_description' => 'nullable|string',
            'treatment_type' => 'required|in:medicine,vaccine,treatment',
            'affected_pigs_count' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'duration_days' => 'required|integer|min:1|max:365',
            'day1_dosage' => 'required|numeric|min:0',
            'daily_dosage' => 'nullable|numeric|min:0',
            'dosage_notes' => 'nullable|string',
            'medicine_name' => 'required|string|max:255',
            'medicine_batch_number' => 'nullable|string|max:100',
            'medicine_expiry_date' => 'nullable|date|after:today',
            'barn_id' => 'nullable|exists:barns,id',
            'pen_id' => 'nullable|exists:pens,id',
            'batch_id' => 'nullable|exists:batches,id',
            'storehouse_id' => 'nullable|exists:storehouses,id',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['farm_id'] = $farm->id;
        $validated['created_by'] = $user->id;
        $validated['updated_by'] = $user->id;
        $validated['status'] = 'pending';

        // Auto-calculate end_date
        if (!isset($validated['end_date'])) {
            $startDate = Carbon::parse($validated['start_date']);
            $validated['end_date'] = $startDate->clone()->addDays($validated['duration_days'] - 1)->toDateString();
        }

        // Auto-calculate total_cost
        if (isset($validated['unit_cost']) && $validated['unit_cost'] > 0) {
            $validated['total_cost'] = $validated['unit_cost'] * $validated['duration_days'];
        }

        $treatment = DairyTreatment::create($validated);

        return redirect()->route('dairy_treatment.show', $treatment)
                        ->with('success', 'บันทึกการรักษาสำเร็จแล้ว');
    }

    /**
     * Display the specified treatment
     */
    public function show(DairyTreatment $dairy_treatment)
    {
        return view('admin.dairy_treatment.show', compact('dairy_treatment'));
    }

    /**
     * Show the form for editing the specified treatment
     */
    public function edit(DairyTreatment $dairy_treatment)
    {
        $user = Auth::user();
        $farm = $dairy_treatment->farm;

        $barns = $farm->barns()->get();
        $batches = $farm->batches()->get();
        $pens = Pen::all();

        return view('admin.dairy_treatment.edit', compact('dairy_treatment', 'farm', 'barns', 'batches', 'pens'));
    }

    /**
     * Update the specified treatment in storage
     */
    public function update(Request $request, DairyTreatment $dairy_treatment)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'treatment_name' => 'required|string|max:255',
            'disease_description' => 'nullable|string',
            'treatment_type' => 'required|in:medicine,vaccine,treatment',
            'affected_pigs_count' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'duration_days' => 'required|integer|min:1|max:365',
            'day1_dosage' => 'required|numeric|min:0',
            'daily_dosage' => 'nullable|numeric|min:0',
            'dosage_notes' => 'nullable|string',
            'medicine_name' => 'required|string|max:255',
            'medicine_batch_number' => 'nullable|string|max:100',
            'medicine_expiry_date' => 'nullable|date',
            'barn_id' => 'nullable|exists:barns,id',
            'pen_id' => 'nullable|exists:pens,id',
            'batch_id' => 'nullable|exists:batches,id',
            'status' => 'in:pending,ongoing,completed,stopped',
            'stop_reason' => 'nullable|string',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = $user->id;

        // Auto-calculate end_date
        if (!isset($validated['end_date'])) {
            $startDate = Carbon::parse($validated['start_date']);
            $validated['end_date'] = $startDate->clone()->addDays($validated['duration_days'] - 1)->toDateString();
        }

        // Auto-calculate total_cost
        if (isset($validated['unit_cost']) && $validated['unit_cost'] > 0) {
            $validated['total_cost'] = $validated['unit_cost'] * $validated['duration_days'];
        }

        $dairy_treatment->update($validated);

        return redirect()->route('dairy_treatment.show', $dairy_treatment)
                        ->with('success', 'อัพเดตการรักษาสำเร็จแล้ว');
    }

    /**
     * Change treatment status to ongoing
     */
    public function startTreatment(DairyTreatment $dairy_treatment)
    {
        $dairy_treatment->markAsOngoing();

        return back()->with('success', 'เริ่มการรักษาแล้ว');
    }

    /**
     * Change treatment status to completed
     */
    public function completeTreatment(DairyTreatment $dairy_treatment)
    {
        $dairy_treatment->markAsCompleted();

        return back()->with('success', 'จบการรักษาแล้ว');
    }

    /**
     * Stop treatment
     */
    public function stopTreatment(Request $request, DairyTreatment $dairy_treatment)
    {
        $request->validate([
            'stop_reason' => 'nullable|string',
        ]);

        $dairy_treatment->stop($request->stop_reason);

        return back()->with('success', 'หยุดการรักษาแล้ว');
    }

    /**
     * Delete treatment
     */
    public function destroy(DairyTreatment $dairy_treatment)
    {
        $dairy_treatment->delete();

        return redirect()->route('dairy_treatment.index')
                        ->with('success', 'ลบการรักษาแล้ว');
    }
}
