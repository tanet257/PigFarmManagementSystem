<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Farm;
use App\Models\Batch;
use App\Models\BatchPenAllocation;
use App\Models\Barn;
use App\Models\Pen;
use App\Models\PigSale;
use App\Models\PigSaleDetail;
use App\Models\PigDeath;
use App\Services\PigPriceService;
use App\Helpers\PigInventoryHelper;
use App\Helpers\NotificationHelper;

class PigSaleController extends Controller
{
    //--------------------------------------- AJAX Helpers ------------------------------------------//

    /**
     * ดึงรายการเล้า-คอกที่มีหมูของ batch นั้นๆ
     */
    public function getPensByBatch($batchId)
    {
        try {
            $pens = PigInventoryHelper::getPigsByBatch($batchId);

            if (!isset($pens['allocations']) || empty($pens['allocations'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบหมูในรุ่นนี้',
                    'data' => []
                ]);
            }

            // จัดรูปแบบข้อมูลสำหรับ dropdown
            $penOptions = collect($pens['allocations'])->map(function ($allocation) {
                return [
                    'pen_id' => $allocation['pen_id'],
                    'barn_name' => $allocation['barn_name'],
                    'pen_name' => $allocation['pen_name'],
                    'current_quantity' => $allocation['current_quantity'],
                    'display_text' => "{$allocation['barn_name']} - {$allocation['pen_name']} (มีหมู {$allocation['current_quantity']} ตัว)"
                ];
            })->filter(function ($pen) {
                // แสดงเฉพาะเล้า-คอกที่มีหมู
                return $pen['current_quantity'] > 0;
            })->values();

            return response()->json([
                'success' => true,
                'data' => $penOptions,
                'summary' => $pens['summary'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * ดึงรายการเล้า-คอกของฟาร์มพร้อมจำนวนหมูที่เหลือจาก batch_pen_allocations
     */
    public function getPensByFarm($farmId)
    {
        try {
            // ดึงข้อมูลจาก batch_pen_allocations ที่มีหมูเหลืออยู่ในฟาร์มนี้
            $penAllocations = DB::table('batch_pen_allocations')
                ->join('pens', 'batch_pen_allocations.pen_id', '=', 'pens.id')
                ->join('barns', 'batch_pen_allocations.barn_id', '=', 'barns.id')
                ->join('batches', 'batch_pen_allocations.batch_id', '=', 'batches.id')
                ->where('barns.farm_id', $farmId)
                ->where('batch_pen_allocations.current_quantity', '>', 0) // เฉพาะที่มีหมูเหลือ
                ->where('batches.status', '!=', 'เสร็จสิ้น') // เฉพาะรุ่นที่ยังไม่เสร็จ
                ->select(
                    'pens.id as pen_id',
                    'barns.barn_code',
                    'pens.pen_code',
                    'batch_pen_allocations.current_quantity',
                    'batches.batch_code'
                )
                ->get();

            if ($penAllocations->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบเล้า-คอกที่มีหมูในฟาร์มนี้',
                    'data' => []
                ]);
            }

            // จัดรูปแบบข้อมูลสำหรับ dropdown
            $penOptions = $penAllocations->map(function ($allocation) {
                return [
                    'pen_id' => $allocation->pen_id,
                    'barn_name' => $allocation->barn_code,
                    'pen_name' => $allocation->pen_code,
                    'current_quantity' => $allocation->current_quantity,
                    'batch_code' => $allocation->batch_code,
                    'display_text' => "{$allocation->barn_code} - {$allocation->pen_code} (มีหมู {$allocation->current_quantity} ตัว)"
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $penOptions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * ดึงรายการเล้า (Barns) ของฟาร์มที่มีหมู
     */
    public function getBarnsByFarm($farmId)
    {
        try {
            // ดึงเล้าที่มีหมูจาก batch_pen_allocations
            $barns = DB::table('batch_pen_allocations')
                ->join('barns', 'batch_pen_allocations.barn_id', '=', 'barns.id')
                ->join('batches', 'batch_pen_allocations.batch_id', '=', 'batches.id')
                ->where('barns.farm_id', $farmId)
                ->where('batch_pen_allocations.current_quantity', '>', 0)
                ->where('batches.status', '!=', 'เสร็จสิ้น')
                ->select('barns.id as barn_id', 'barns.barn_code')
                ->groupBy('barns.id', 'barns.barn_code')
                ->get();

            if ($barns->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบเล้าที่มีหมูในฟาร์มนี้',
                    'data' => []
                ]);
            }

            // นับจำนวนหมูในแต่ละเล้า
            $barnOptions = $barns->map(function ($barn) {
                $totalPigs = DB::table('batch_pen_allocations')
                    ->join('batches', 'batch_pen_allocations.batch_id', '=', 'batches.id')
                    ->where('batch_pen_allocations.barn_id', $barn->barn_id)
                    ->where('batches.status', '!=', 'เสร็จสิ้น')
                    ->sum('batch_pen_allocations.current_quantity');

                return [
                    'barn_id' => $barn->barn_id,
                    'barn_code' => $barn->barn_code,
                    'total_pigs' => $totalPigs,
                    'display_text' => "{$barn->barn_code} (มีหมู {$totalPigs} ตัว)"
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $barnOptions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * ดึงรายการคอก (Pens) ในเล้าที่มีหมู
     */
    public function getPensByBarn($barnId)
    {
        try {
            // ดึงคอกที่มีหมูในเล้านี้
            $pens = DB::table('batch_pen_allocations')
                ->join('pens', 'batch_pen_allocations.pen_id', '=', 'pens.id')
                ->join('batches', 'batch_pen_allocations.batch_id', '=', 'batches.id')
                ->where('batch_pen_allocations.barn_id', $barnId)
                ->where('batch_pen_allocations.current_quantity', '>', 0)
                ->where('batches.status', '!=', 'เสร็จสิ้น')
                ->select(
                    'pens.id as pen_id',
                    'pens.pen_code',
                    'batch_pen_allocations.current_quantity',
                    'batches.batch_code'
                )
                ->get();

            if ($pens->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบคอกที่มีหมูในเล้านี้',
                    'data' => []
                ]);
            }

            $penOptions = $pens->map(function ($pen) {
                return [
                    'pen_id' => $pen->pen_id,
                    'pen_code' => $pen->pen_code,
                    'current_quantity' => $pen->current_quantity,
                    'batch_code' => $pen->batch_code,
                    'display_text' => "{$pen->pen_code} (มีหมู {$pen->current_quantity} ตัว - รุ่น {$pen->batch_code})"
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $penOptions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    //--------------------------------------- Index View ------------------------------------------//

    public function index(Request $request)
    {
        $farms = Farm::all();
        // กรองเฉพาะรุ่นที่ยังไม่เสร็จสิ้น
        $batches = Batch::select('id', 'batch_code', 'farm_id')
            ->where('status', '!=', 'เสร็จสิ้น')
            ->get();
        $barns = Barn::all();
        $pens = Pen::all();
        $pigDeaths = PigDeath::all();

        // Eager load relationships เหมือนกับ PigEntryRecord
        $query = PigSale::with([
            'farm',
            'batch',
            'pen' => function ($query) {
                $query->with('barn'); // Load barn ผ่าน pen
            },
            'pigLoss',
            'customer',
            'createdBy',
            'approvedBy'
        ]);

        // Filter by farm
        if ($request->filled('farm_id')) {
            $query->where('farm_id', $request->farm_id);
        }

        // Filter by batch
        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        // Filter by date range
        if ($request->filled('selected_date')) {
            $now = Carbon::now();
            switch ($request->selected_date) {
                case 'today':
                    $query->whereDate('sell_date', $now->toDateString());
                    break;
                case 'this_week':
                    $query->whereBetween('sell_date', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereYear('sell_date', $now->year)->whereMonth('sell_date', $now->month);
                    break;
                case 'this_year':
                    $query->whereYear('sell_date', $now->year);
                    break;
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'sell_date');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['sell_date', 'quantity', 'total_price', 'net_total', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('sell_date', 'desc');
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $pigSales = $query->paginate($perPage);

        // ดึงราคาหมูล่าสุดจาก CPF
        $latestPrice = PigPriceService::getLatestPrice();

        return view('admin.pig_sales.index', compact('farms', 'batches', 'barns', 'pens', 'pigDeaths', 'pigSales', 'latestPrice'));
    }

    //--------------------------------------- Create ------------------------------------------//

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'farm_id' => 'required|exists:farms,id',
                'batch_id' => 'required|exists:batches,id',
                'selected_pens' => 'required|array|min:1',
                'selected_pens.*' => 'required|exists:pens,id',
                'quantities' => 'required|array',
                'quantities.*' => 'required|integer|min:1',
                'pig_loss_id' => 'nullable|exists:pig_deaths,id',
                'sell_date' => 'required|date',
                'sell_type' => 'required|string',
                'total_quantity' => 'required|integer|min:1',
                'total_weight' => 'required|numeric|min:0',
                'price_per_kg' => 'required|numeric|min:0',
                'total_price' => 'required|numeric|min:0',
                'buyer_name' => 'required|string|max:255',
                'discount' => 'nullable|numeric|min:0',
                'shipping_cost' => 'nullable|numeric|min:0',
                'net_total' => 'required|numeric',
                'cpf_reference_price' => 'nullable|numeric',
                'cpf_reference_date' => 'nullable|date',
                'note' => 'nullable|string',
            ]);

            // ลดจำนวนหมูจากหลายคอก และบันทึกรายละเอียด
            $detailsData = [];
            foreach ($validated['selected_pens'] as $penId) {
                $quantity = $validated['quantities'][$penId] ?? 0;

                if ($quantity > 0) {
                    $result = PigInventoryHelper::reducePigInventory(
                        $validated['batch_id'],
                        $penId,
                        $quantity,
                        'sale'
                    );

                    if (!$result['success']) {
                        throw new \Exception($result['message']);
                    }

                    // เก็บข้อมูลรายละเอียดไว้สร้างทีหลัง
                    $detailsData[] = [
                        'pen_id' => $penId,
                        'quantity' => $quantity,
                    ];
                }
            }

            // สร้างการขาย (ใช้คอกแรกเป็นตัวแทน)
            $validated['date'] = $validated['sell_date'];
            $validated['pen_id'] = $validated['selected_pens'][0]; // ใช้คอกแรก
            $validated['quantity'] = $validated['total_quantity'];
            $validated['payment_status'] = 'รอชำระ';
            $validated['paid_amount'] = 0;
            $validated['balance'] = $validated['net_total'];

            // Generate sale_number: PS-YYYYMMDD-XXX
            $date = date('Ymd', strtotime($validated['sell_date']));
            $prefix = 'PS-' . $date . '-';

            // หา running number ล่าสุดของวันนี้
            $lastSale = PigSale::where('sale_number', 'LIKE', $prefix . '%')
                ->orderBy('sale_number', 'desc')
                ->first();

            if ($lastSale && $lastSale->sale_number) {
                // ดึงเลขท้ายจาก sale_number ล่าสุด
                $lastNumber = intval(substr($lastSale->sale_number, -3));
                $runningNumber = $lastNumber + 1;
            } else {
                $runningNumber = 1;
            }

            $validated['sale_number'] = $prefix . str_pad($runningNumber, 3, '0', STR_PAD_LEFT);

            // คำนวณ price_per_pig (ราคาต่อตัว)
            // ใช้ net_total หารด้วย quantity
            if ($validated['quantity'] > 0) {
                $validated['price_per_pig'] = $validated['net_total'] / $validated['quantity'];
            } else {
                $validated['price_per_pig'] = 0;
            }

            // บันทึกชื่อผู้ใช้ที่ล็อกอินอยู่
            $validated['created_by'] = auth()->user()->name;

            $pigSale = PigSale::create($validated);

            // บันทึกรายละเอียดแต่ละคอก
            foreach ($detailsData as $detail) {
                PigSaleDetail::create([
                    'pig_sale_id' => $pigSale->id,
                    'pen_id' => $detail['pen_id'],
                    'quantity' => $detail['quantity'],
                ]);
            }

            // แจ้งเตือน Admin เมื่อมีการขายหมู
            NotificationHelper::notifyAdminsPigSale($pigSale, auth()->user());

            DB::commit();

            return redirect()->route('pig_sale.index')->with('success', 'บันทึกการขายหมูสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PigSell Create Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    //--------------------------------------- Approve Sale ------------------------------------------//

    public function approve(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $pigSale = PigSale::findOrFail($id);

            // ตรวจสอบว่าอนุมัติแล้วหรือยัง
            if ($pigSale->approved_at) {
                return redirect()->back()->with('error', 'การขายนี้ได้รับการอนุมัติแล้ว');
            }

            // ตรวจสอบไม่ให้อนุมัติการขายที่ตัวเองสร้าง (ยกเว้น Admin)
            $user = auth()->user();
            if ($pigSale->created_by === $user->name && !$user->hasRole('admin')) {
                return redirect()->back()->with('error', 'คุณไม่สามารถอนุมัติการขายที่ตัวเองสร้างได้');
            }

            // บันทึกข้อมูลการอนุมัติ
            $pigSale->approved_by = $user->name;
            $pigSale->approved_at = now();
            $pigSale->save();

            DB::commit();

            return redirect()->route('pig_sale.index')->with('success', 'อนุมัติการขายเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PigSale Approve Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    //--------------------------------------- Upload Receipt ------------------------------------------//

    public function uploadReceipt(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $pigSale = PigSale::findOrFail($id);

            $validated = $request->validate([
                'paid_amount' => 'required|numeric|min:0',
                'payment_method' => 'required|string',
                'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            ]);

            // ตรวจสอบว่าจำนวนเงินที่ชำระไม่เกินยอดคงเหลือ
            if ($validated['paid_amount'] > $pigSale->balance) {
                return redirect()->back()->with('error', 'จำนวนเงินที่ชำระเกินยอดคงเหลือ');
            }

            // อัปโหลดไฟล์ (ถ้ามี) หลังจาก validation ผ่านแล้ว
            $uploadedFileUrl = null;
            if ($request->hasFile('receipt_file') && $request->file('receipt_file')->isValid()) {
                $uploadedFileUrl = Cloudinary::upload(
                    $request->file('receipt_file')->getRealPath(),
                    ['folder' => 'receipt_files']
                )->getSecurePath();
            }

            // อัปเดทข้อมูลการชำระเงิน
            $pigSale->paid_amount += $validated['paid_amount'];
            $pigSale->balance = $pigSale->net_total - $pigSale->paid_amount;

            // อัปเดทสถานะการชำระเงิน
            if ($pigSale->balance <= 0) {
                $pigSale->payment_status = 'ชำระแล้ว';
                $pigSale->balance = 0;
            } elseif ($pigSale->paid_amount > 0 && $pigSale->balance > 0) {
                $pigSale->payment_status = 'ชำระบางส่วน';
            }

            // บันทึก receipt file (ถ้ามี)
            if ($uploadedFileUrl) {
                $pigSale->receipt_file = $uploadedFileUrl;
            }

            // บันทึกวิธีชำระเงิน
            $pigSale->payment_method = $validated['payment_method'];

            $pigSale->save();

            DB::commit();

            $message = 'บันทึกการชำระเงินสำเร็จ - ';
            $message .= $pigSale->payment_status === 'ชำระแล้ว'
                ? 'ชำระครบแล้ว'
                : 'ชำระแล้ว ' . number_format($pigSale->paid_amount, 2) . ' บาท คงเหลือ ' . number_format($pigSale->balance, 2) . ' บาท';

            return redirect()->route('pig_sale.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PigSell Upload Receipt Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    //--------------------------------------- Cancel (Delete) ------------------------------------------//

    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            $pigSale = PigSale::findOrFail($id);

            // คืนจำนวนหมูกลับทุกคอกตามรายละเอียดที่บันทึกไว้
            $details = PigSaleDetail::where('pig_sale_id', $pigSale->id)->get();

            if ($details->isEmpty()) {
                // ถ้าไม่มีรายละเอียด (ข้อมูลเก่า) คืนหมูแบบ manual โดยไม่ validation
                Log::warning("ยกเลิกการขาย ID {$pigSale->id} (ข้อมูลเก่า - ไม่มี pig_sale_details) - คืนหมูแบบ manual");

                if ($pigSale->pen_id && $pigSale->quantity > 0) {
                    // คืนหมูกลับคอกโดยตรง (ไม่ผ่าน Helper เพื่อหลีกเลี่ยง validation)
                    $allocation = BatchPenAllocation::where('batch_id', $pigSale->batch_id)
                        ->where('pen_id', $pigSale->pen_id)
                        ->lockForUpdate()
                        ->first();

                    if ($allocation) {
                        $allocation->current_quantity = ($allocation->current_quantity ?? 0) + $pigSale->quantity;
                        $allocation->save();
                    }

                    // คืนหมูกลับรุ่น
                    $batch = Batch::lockForUpdate()->find($pigSale->batch_id);
                    if ($batch) {
                        $batch->current_quantity = ($batch->current_quantity ?? 0) + $pigSale->quantity;
                        $batch->save();
                    }
                }
            } else {
                // คืนหมูกลับแต่ละคอกตามจำนวนที่ขายไป (ข้อมูลใหม่)
                foreach ($details as $detail) {
                    $result = PigInventoryHelper::increasePigInventory(
                        $pigSale->batch_id,
                        $detail->pen_id,
                        $detail->quantity
                    );

                    if (!$result['success']) {
                        throw new \Exception('ไม่สามารถคืนหมูกลับคอก #' . $detail->pen_id . ' ได้: ' . $result['message']);
                    }
                }
            }

            // ลบไฟล์จาก Cloudinary ถ้ามี
            if ($pigSale->receipt_file) {
                $publicId = $this->getPublicIdFromUrl($pigSale->receipt_file);
                if ($publicId) {
                    Cloudinary::destroy($publicId);
                }
            }

            $pigSale->delete();

            DB::commit();

            return redirect()->route('pig_sale.index')->with('success', 'ยกเลิกการขายสำเร็จ (คืนหมูกลับเล้า-คอกแล้ว)');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PigSell Cancel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    //--------------------------------------- EXPORT ------------------------------------------//

    // Export PDF
    public function exportPdf()
    {
        $pigSales = PigSale::with(['farm', 'batch', 'pigLoss'])->get();

        $pdf = Pdf::loadView('admin.pig_sale.exports.pdf', compact('pigSales'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Sarabun',
            ]);

        $filename = "pig_sales_" . date('Y-m-d_H-i-s') . ".pdf";
        return $pdf->download($filename);
    }

    // Export CSV
    public function exportCsv()
    {
        $pigSales = PigSale::with(['farm', 'batch', 'pigLoss'])->get();
        $filename = "pig_sales_" . date('Y-m-d_H-i-s') . ".csv";

        return response()->streamDownload(function () use ($pigSales) {
            $handle = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID',
                'วันที่ขาย',
                'ฟาร์ม',
                'รหัสรุ่น',
                'ประเภทการขาย',
                'จำนวน',
                'น้ำหนักรวม (kg)',
                'ราคาต่อ kg',
                'ราคาต่อตัว',
                'ราคารวม',
                'ชื่อผู้ซื้อ',
                'หมายเหตุ',
                'สถานะใบเสร็จ',
                'สร้างเมื่อ'
            ]);

            foreach ($pigSales as $sell) {
                fputcsv($handle, [
                    $sell->id,
                    $sell->sell_date ? Carbon::parse($sell->sell_date)->format('d/m/Y') : '-',
                    $sell->farm->farm_name ?? '-',
                    $sell->batch->batch_code ?? '-',
                    $sell->sell_type,
                    $sell->quantity,
                    $sell->total_weight,
                    $sell->price_per_kg,
                    $sell->price_per_pig,
                    $sell->total_price,
                    $sell->buyer_name,
                    $sell->note ?? '-',
                    $sell->receipt_file ? 'มีใบเสร็จ' : 'รอชำระเงิน',
                    $sell->created_at->format('d/m/Y H:i')
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    //--------------------------------------- Helper Functions ------------------------------------------//

    private function getPublicIdFromUrl($url)
    {
        // Extract public_id from Cloudinary URL
        // Example: https://res.cloudinary.com/xxx/image/upload/v123/receipt_files/abc.jpg
        // Return: receipt_files/abc

        if (preg_match('/\/v\d+\/(.+)\.\w+$/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
