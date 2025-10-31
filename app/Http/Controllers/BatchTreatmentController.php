<?php

namespace App\Http\Controllers;

use App\Models\BatchTreatment;
use App\Models\DairyRecord;
use App\Models\Batch;
use App\Models\Pen;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BatchTreatmentController extends Controller
{
    /**
     * Store a newly created treatment for dairy record
     * POST: /dairy-records/{dairy_record}/treatments
     */
    public function store(Request $request, DairyRecord $dairy_record)
    {
        $validated = $request->validate([
            'disease_name' => 'required|string|max:255',
            'medicine_name' => 'required|string|max:255',
            'medicine_code' => 'nullable|string|max:100',
            'treatment_start_date' => 'required|date',
            'duration_days' => 'required|integer|min:1|max:365',
            'batch_id' => 'nullable|exists:batches,id',
            'pen_id' => 'nullable|exists:pens,id',
            'note' => 'nullable|string',
        ]);

        // Set status to pending (daily logs will be added incrementally)
        $validated['status'] = 'วางแผนว่าจะให้ยา'; // Default status
        $validated['treatment_status'] = 'pending';
        $validated['dairy_record_id'] = $dairy_record->id;
        $validated['date'] = $dairy_record->date; // Use dairy record date

        $treatment = BatchTreatment::create($validated);

        return back()->with('success', 'บันทึกการให้ยา/วัคซีนสำเร็จแล้ว');
    }

    /**
     * Update treatment status (ongoing/completed/stopped)
     * PATCH: /batch-treatments/{treatment}/update-status
     */
    public function updateStatus(Request $request, BatchTreatment $batch_treatment)
    {
        $validated = $request->validate([
            'treatment_status' => 'required|in:pending,ongoing,completed,stopped',
        ]);

        $batch_treatment->update($validated);

        return back()->with('success', 'อัพเดตสถานะการรักษาแล้ว');
    }

    /**
     * Mark treatment as ongoing
     * POST: /batch-treatments/{treatment}/start
     */
    public function start(BatchTreatment $batch_treatment)
    {
        $batch_treatment->markAsOngoing();

        return back()->with('success', 'เริ่มการรักษาแล้ว');
    }

    /**
     * Mark treatment as completed
     * POST: /batch-treatments/{treatment}/complete
     */
    public function complete(BatchTreatment $batch_treatment)
    {
        $batch_treatment->markAsCompleted();

        return back()->with('success', 'จบการรักษาแล้ว');
    }

    /**
     * Mark treatment as stopped
     * POST: /batch-treatments/{treatment}/stop
     */
    public function stop(BatchTreatment $batch_treatment)
    {
        $batch_treatment->markAsStopped();

        return back()->with('success', 'หยุดการรักษาแล้ว');
    }

    /**
     * Update treatment (edit)
     * PATCH: /batch-treatments/{treatment}
     */
    public function update(Request $request, BatchTreatment $batch_treatment)
    {
        $validated = $request->validate([
            'disease_name' => 'required|string|max:255',
            'medicine_name' => 'required|string|max:255',
            'medicine_code' => 'nullable|string|max:100',
            'treatment_start_date' => 'required|date',
            'duration_days' => 'required|integer|min:1|max:365',
            'batch_id' => 'nullable|exists:batches,id',
            'pen_id' => 'nullable|exists:pens,id',
            'note' => 'nullable|string',
            'treatment_status' => 'nullable|in:pending,ongoing,completed,stopped',
        ]);

        $batch_treatment->update($validated);

        return back()->with('success', 'อัพเดตการรักษาแล้ว');
    }

    /**
     * Delete treatment
     * DELETE: /batch-treatments/{treatment}
     */
    public function destroy(BatchTreatment $batch_treatment)
    {
        $dairyRecordId = $batch_treatment->dairy_record_id;
        $batch_treatment->delete();

        return back()->with('success', 'ลบการรักษาแล้ว');
    }

    /**
     * Get treatment summary for batch
     * GET: /batch-treatments/summary/{batch}
     */
    public function summary(Batch $batch)
    {
        $treatments = BatchTreatment::where('batch_id', $batch->id)
            ->orderBy('treatment_start_date', 'desc')
            ->get();

        $summary = [
            'total_treatments' => $treatments->count(),
            'ongoing' => $treatments->where('treatment_status', 'ongoing')->count(),
            'completed' => $treatments->where('treatment_status', 'completed')->count(),
            'stopped' => $treatments->where('treatment_status', 'stopped')->count(),
            'treatments' => $treatments,
        ];

        return view('admin.batch_treatments.summary', compact('batch', 'summary'));
    }
}
