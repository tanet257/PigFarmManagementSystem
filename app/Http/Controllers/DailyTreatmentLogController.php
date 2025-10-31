<?php

namespace App\Http\Controllers;

use App\Models\BatchTreatment;
use App\Models\DailyTreatmentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyTreatmentLogController extends Controller
{
    /**
     * Store a new daily treatment log entry
     * POST: /daily-treatment-logs
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_treatment_id' => 'required|exists:batch_treatments,id',
            'treatment_date' => 'required|date',
            'quantity_given' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'note' => 'nullable|string',
        ]);

        // Set the user who recorded this log
        $validated['recorded_by'] = Auth::id();

        $dailyLog = DailyTreatmentLog::create($validated);

        return back()->with('success', 'บันทึกการรักษารายวันสำเร็จแล้ว');
    }

    /**
     * Update daily treatment log entry
     * PATCH: /daily-treatment-logs/{log}
     */
    public function update(Request $request, DailyTreatmentLog $dailyTreatmentLog)
    {
        $validated = $request->validate([
            'treatment_date' => 'required|date',
            'quantity_given' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'note' => 'nullable|string',
        ]);

        $dailyTreatmentLog->update($validated);

        return back()->with('success', 'อัพเดตการรักษารายวันแล้ว');
    }

    /**
     * Delete daily treatment log entry
     * DELETE: /daily-treatment-logs/{log}
     */
    public function destroy(DailyTreatmentLog $dailyTreatmentLog)
    {
        $dailyTreatmentLog->delete();

        return back()->with('success', 'ลบการรักษารายวันแล้ว');
    }

    /**
     * Get all daily logs for a treatment
     * GET: /batch-treatments/{treatment}/daily-logs
     */
    public function getByTreatment(BatchTreatment $batchTreatment)
    {
        $dailyLogs = $batchTreatment->dailyLogs()
            ->orderBy('treatment_date', 'asc')
            ->with('recordedBy')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dailyLogs,
        ]);
    }

    /**
     * Get summary of daily logs for a treatment
     * GET: /batch-treatments/{treatment}/daily-logs/summary
     */
    public function getSummary(BatchTreatment $batchTreatment)
    {
        $dailyLogs = $batchTreatment->dailyLogs()->get();

        $summary = [
            'total_logs' => $dailyLogs->count(),
            'total_quantity' => $dailyLogs->sum('quantity_given'),
            'unit' => $dailyLogs->first()?->unit ?? 'N/A',
            'logs' => $dailyLogs,
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
