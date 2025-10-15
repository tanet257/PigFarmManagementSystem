<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BatchPenAllocation;
use App\Models\Farm;
use App\Models\Batch;
use App\Models\Barn;
use App\Models\Pen;

class BatchPenAllocationController extends Controller
{
    //--------------------------------------- Index View ------------------------------------------//
    public function index(Request $request)
    {
        $farms   = Farm::all();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();

        $farmId  = $request->get('farm_id');
        $batchId = $request->get('batch_id');
        $search  = $request->get('search');
        $sortBy  = $request->get('sort_by', 'barn_code');
        $sortOrder = $request->get('sort_order', 'asc');
        $perPage = $request->get('per_page', 10);
        $page    = $request->get('page', 1);

        // base query barns -> pens -> allocations
        $barnsQuery = Barn::with(['pens.batchPenAllocations' => function ($query) use ($request) {
            // Date Filter on BatchPenAllocations
            if ($request->filled('selected_date')) {
                $date = Carbon::now();
                switch ($request->selected_date) {
                    case 'today':
                        $query->whereDate('created_at', $date);
                        break;
                    case 'this_week':
                        $query->whereBetween('created_at', [$date->startOfWeek(), $date->copy()->endOfWeek()]);
                        break;
                    case 'this_month':
                        $query->whereMonth('created_at', $date->month)
                            ->whereYear('created_at', $date->year);
                        break;
                    case 'this_year':
                        $query->whereYear('created_at', $date->year);
                        break;
                }
            }
        }, 'pens.batchPenAllocations.batch.farm']);

        if ($farmId) {
            $barnsQuery->where('farm_id', $farmId);
        }
        $barns = $barnsQuery->get();

        // summary barns
        $barnSummariesCollection = $barns->map(function ($barn) use ($batchId) {
            $pensInfo = $barn->pens->map(function ($pen) use ($batchId) {
                $allocations = $pen->batchPenAllocations;

                if ($batchId) {
                    $allocations = $allocations->filter(fn($a) => $a->batch_id == $batchId);
                }

                return [
                    'pen_code' => $pen->pen_code,
                    'capacity' => $pen->pig_capacity,
                    'allocated' => $allocations->sum('allocated_pigs'),
                    'batches' => $allocations->map(fn($a) => optional($a->batch)->batch_code)->unique()->values()->all()
                ];
            })->values(); // แปลงเป็น array

            $totalAllocated = $pensInfo->sum('allocated');

            $farmNames = $barn->pens->flatMap(function ($pen) {
                return $pen->batchPenAllocations->map(fn($a) => optional($a->batch->farm)->farm_name);
            })->filter()->unique()->values()->all();

            $batchCodes = $barn->pens->flatMap(function ($pen) {
                return $pen->batchPenAllocations->map(fn($a) => optional($a->batch)->batch_code);
            })->filter()->unique()->values()->all();

            return [
                'farm_name' => implode(', ', $farmNames),
                'batch_code' => implode(', ', $batchCodes),
                'barn_code' => $barn->barn_code,
                'capacity' => $barn->pig_capacity,
                'total_allocated' => $totalAllocated,
                'pens' => $pensInfo->toArray(), // แปลงเป็น array
            ];
        })->values(); // แปลง collection เป็น array

        // Pagination
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $barnSummaries = new LengthAwarePaginator(
            $barnSummariesCollection->forPage($page, $perPage),
            $barnSummariesCollection->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.batch_pen_allocations.index', compact('farms', 'batches', 'barnSummaries'));
    }

    //--------------------------------------- EXPORT ------------------------------------------//
    public function exportPdf()
    {
        $barns = Barn::with(['pens.batchPenAllocations.batch.farm'])->get();

        $barnSummaries = $barns->map(function ($barn) {
            $pensInfo = $barn->pens->map(function ($pen) {
                $allocations = $pen->batchPenAllocations;
                return [
                    'pen_code'  => $pen->pen_code,
                    'capacity'  => $pen->pig_capacity,
                    'allocated' => $allocations->sum('allocated_pigs'),
                    'batches'   => $allocations->map(fn($a) => optional($a->batch)->batch_code)->unique()->values()
                ];
            });

            $totalAllocated = $pensInfo->sum('allocated');
            $farmNames = $barn->pens->flatMap(
                fn($pen) =>
                $pen->batchPenAllocations->map(fn($a) => optional($a->batch->farm)->farm_name)
            )->filter()->unique()->values();

            $batchCodes = $barn->pens->flatMap(
                fn($pen) =>
                $pen->batchPenAllocations->map(fn($a) => optional($a->batch)->batch_code)
            )->filter()->unique()->values();

            return [
                'farm_name'      => $farmNames->join(', '),
                'batch_code'     => $batchCodes->join(', '),
                'barn_code'      => $barn->barn_code,
                'capacity'       => $barn->pig_capacity,
                'total_allocated' => $totalAllocated,
                'pens'           => $pensInfo,
            ];
        });

        $pdf = Pdf::loadView('admin.batch_pen_allocations.exports.pdf', compact('barnSummaries'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Sarabun',
            ]);

        $filename = "batch_pen_allocations_" . date('Y-m-d_H-i-s') . ".pdf";
        return $pdf->download($filename);
    }

    public function exportCsv()
    {
        $barns = Barn::with(['pens.batchPenAllocations.batch.farm'])->get();

        $barnSummaries = $barns->map(function ($barn) {
            $pensInfo = $barn->pens->map(function ($pen) {
                $allocations = $pen->batchPenAllocations;
                return [
                    'pen_code'  => $pen->pen_code,
                    'capacity'  => $pen->pig_capacity,
                    'allocated' => $allocations->sum('allocated_pigs'),
                    'batches'   => $allocations->map(fn($a) => optional($a->batch)->batch_code)->unique()->values()
                ];
            });

            $totalAllocated = $pensInfo->sum('allocated');
            $farmNames = $barn->pens->flatMap(
                fn($pen) =>
                $pen->batchPenAllocations->map(fn($a) => optional($a->batch->farm)->farm_name)
            )->filter()->unique()->values();

            $batchCodes = $barn->pens->flatMap(
                fn($pen) =>
                $pen->batchPenAllocations->map(fn($a) => optional($a->batch)->batch_code)
            )->filter()->unique()->values();

            return [
                'farm_name'      => $farmNames->join(', '),
                'batch_code'     => $batchCodes->join(', '),
                'barn_code'      => $barn->barn_code,
                'capacity'       => $barn->pig_capacity,
                'total_allocated' => $totalAllocated,
            ];
        });

        $filename = "batch_pen_allocations_" . date('Y-m-d_H-i-s') . ".csv";

        return response()->streamDownload(function () use ($barnSummaries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Farm', 'Batch', 'Barn', 'Capacity', 'Total Allocated']);
            foreach ($barnSummaries as $summary) {
                fputcsv($handle, [
                    $summary['farm_name'],
                    $summary['batch_code'],
                    $summary['barn_code'],
                    $summary['capacity'],
                    $summary['total_allocated'],
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
