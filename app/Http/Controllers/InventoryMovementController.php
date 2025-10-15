<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Farm;
use App\Models\Batch;
use App\Models\StoreHouse;
use App\Models\InventoryMovement;
use App\Models\Barn;
use App\Models\DairyRecord;

class InventoryMovementController extends Controller
{
    //--------------------------------------- Index View ------------------------------------------//

    public function index(Request $request)
    {
        $farms = Farm::all();
        $batches = Batch::select('id', 'batch_code', 'farm_id')->get();
        $barns = Barn::all();
        $dairy_records = DairyRecord::all();

        // join ความสัมพันธ์: inventory_movements -> storehouse -> farm
        $query = InventoryMovement::with(['storehouse.farm', 'batch', 'barn']);

        // search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                    ->orWhereHas('storehouse', function ($sq) use ($search) {
                        $sq->where('item_name', 'like', "%{$search}%")
                            ->orWhere('item_code', 'like', "%{$search}%");
                    });
            });
        }

        // Date Filter
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

        // filter farm
        if ($request->filled('farm_id')) {
            $farmId = $request->farm_id;
            $query->whereHas('storehouse', function ($q) use ($farmId) {
                $q->where('farm_id', $farmId);
            });
        }

        // filter batch
        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        // sort
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['date', 'quantity', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('date', 'desc');
        }

        // pagination
        $perPage = $request->get('per_page', 10);
        $movements = $query->paginate($perPage);

        return view('admin.inventory_movements.index', compact('farms', 'batches', 'barns', 'movements', 'dairy_records'));
    }

    //--------------------------------------- EXPORT ------------------------------------------//

    // Export PDF
    public function exportPdf()
    {
        $movements = InventoryMovement::with(['storehouse.farm', 'batch', 'barn', 'dairy_record'])->get();

        $pdf = Pdf::loadView('admin.inventory_movements.exports.pdf', compact('movements'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Sarabun',
            ]);

        $filename = "inventory_movements_" . date('Y-m-d_H-i-s') . ".pdf";
        return $pdf->download($filename);
    }

    // Export CSV
    public function exportCsv()
    {
        $movements = InventoryMovement::with(['storehouse.farm', 'batch', 'barn', 'dairy_record'])->get();
        $filename = "inventory_movements_" . date('Y-m-d_H-i-s') . ".csv";

        return response()->streamDownload(function () use ($movements) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'Farm',
                'Storehouse Item Code',
                'Storehouse Item Name',
                'Batch',
                'Barn',
                'Dairy record',
                'Change Type',
                'Quantity',
                'Note',
                'Date',
                'Created At'
            ]);

            foreach ($movements as $m) {
                fputcsv($handle, [
                    $m->id,
                    $m->storehouse->farm->name ?? '-',
                    $m->storehouse->item_code ?? '-',
                    $m->storehouse->item_name ?? '-',
                    $m->batch->batch_code ?? '-',
                    $m->barn->barn_code ?? '-',
                    $m->dairy_record->id ?? '-',
                    $m->change_type,
                    $m->quantity,
                    $m->note,
                    $m->date,
                    $m->created_at,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
