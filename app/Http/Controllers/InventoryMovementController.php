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
        $batches = Batch::select('id', 'batch_code', 'farm_id')
            ->where('status', '!=', 'เสร็จสิ้น')
            ->where('status', '!=', 'cancelled')  // ✅ ยกเว้น cancelled
            ->get();
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

        // ✅ Filter cancelled batches (unless show_cancelled is true)
        if (!$request->has('show_cancelled') || !$request->show_cancelled) {
            $query->whereHas('batch', function ($q) {
                $q->where('status', '!=', 'cancelled');
            });
        }

        // ✅ APPLY SORT
        $sort = $request->get('sort', 'date_desc');  // ดึง sort parameter จาก view

        // Join storehouse เพื่อเรียงตามชื่อสินค้า
        if (strpos($sort, 'name_') === 0) {
            $query->join('storehouses', 'inventory_movements.storehouse_id', '=', 'storehouses.id');
        }

        switch ($sort) {
            case 'name_asc':
                $query->orderBy('storehouses.item_name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('storehouses.item_name', 'desc');
                break;
            case 'quantity_asc':
                $query->orderBy('inventory_movements.quantity', 'asc');
                break;
            case 'quantity_desc':
                $query->orderBy('inventory_movements.quantity', 'desc');
                break;
            case 'date_asc':
                $query->orderBy('inventory_movements.date', 'asc');
                break;
            default: // date_desc
                $query->orderBy('inventory_movements.date', 'desc');
                break;
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
        $filename = "ความเคลื่อนไหวของสต็อก_" . date('Y-m-d') . ".csv";

        return response()->streamDownload(function () use ($movements) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Thai character support in Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, [
                'ลำดับ',
                'ฟาร์ม',
                'รหัสสินค้า',
                'ชื่อสินค้า',
                'รุ่น',
                'เล้า',
                'บันทึกประจำวัน',
                'ประเภทการเปลี่ยนแปลง',
                'จำนวน',
                'หมายเหตุ',
                'วันที่',
                'สร้างเมื่อ'
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
        }, $filename, ['Content-Type' => 'text/csv;charset=utf-8']);
    }
}
