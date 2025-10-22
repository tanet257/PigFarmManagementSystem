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
use App\Models\Cost;
use App\Models\Payment;
use App\Models\Notification;
use App\Services\PigPriceService;
use App\Helpers\PigInventoryHelper;
use App\Helpers\NotificationHelper;
use App\Helpers\RevenueHelper;

class PigSaleController extends Controller
{
    //--------------------------------------- AJAX Helpers ------------------------------------------//

    /**
     * ดึงรายการรุ่นที่มีหมูของฟาร์ม
     */
    public function getBatchesByFarm($farmId)
    {
        try {
            $batches = DB::table('batches')
                ->join('batch_pen_allocations', 'batches.id', '=', 'batch_pen_allocations.batch_id')
                ->where('batches.farm_id', $farmId)
                ->where('batch_pen_allocations.current_quantity', '>', 0)
                ->where('batches.status', '!=', 'เสร็จสิ้น')
                ->select('batches.id', 'batches.batch_code', DB::raw('SUM(batch_pen_allocations.current_quantity) as total_pigs'))
                ->groupBy('batches.id', 'batches.batch_code')
                ->get();

            if ($batches->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบรุ่นที่มีหมูในฟาร์มนี้',
                    'batches' => []
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'พบรุ่นที่มีหมู ' . $batches->count() . ' รุ่น',
                'batches' => $batches
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
                'batches' => []
            ], 500);
        }
    }

    /**
     * ดึงรายการเล้า-คอกที่มีหมูของ batch นั้นๆ
     */
    public function getPensByBatch($batchId)
    {
        try {
            $pens = PigInventoryHelper::getPigsByBatch($batchId);

            if (!isset($pens['pigs']) || empty($pens['pigs'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบหมูในรุ่นนี้',
                    'data' => []
                ]);
            }

            // จัดรูปแบบข้อมูลสำหรับ table
            $penOptions = collect($pens['pigs'])->map(function ($allocation) {
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
                'summary' => [
                    'total_available' => $pens['total_available'] ?? 0,
                    'total_pens' => $pens['total_pens'] ?? 0
                ]
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

    /**
     * ✅ API: ดึงสถานะการชำระเงินและอนุมัติของหลาย pig sales (สำหรับ auto-refresh)
     */
    public function getStatusBatch(Request $request)
    {
        try {
            $pigSaleIds = $request->input('pig_sale_ids', []);

            if (empty($pigSaleIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่มี pig sale IDs ที่ส่งมา',
                    'statuses' => []
                ]);
            }

            // ดึงสถานะปัจจุบัน
            $statuses = PigSale::whereIn('id', $pigSaleIds)
                ->select(
                    'id',
                    'payment_status',
                    'approved_at',
                    'approved_by',
                    'balance'
                )
                ->get()
                ->keyBy('id')
                ->map(function ($sale) {
                    return [
                        'payment_status' => $sale->payment_status,
                        'approved_at' => $sale->approved_at,
                        'approved_by' => $sale->approved_by,
                        'balance' => $sale->balance
                    ];
                });

            return response()->json([
                'success' => true,
                'statuses' => $statuses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
                'statuses' => []
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

        // ✅ Exclude cancelled sales (soft delete) - unless show_cancelled is true
        if (!$request->has('show_cancelled') || !$request->show_cancelled) {
            $query->where('status', '!=', 'ยกเลิกการขาย');
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('sale_number', 'like', '%' . $request->search . '%')
                    ->orWhere('note', 'like', '%' . $request->search . '%')
                    ->orWhereHas('customer', function ($sq) use ($request) {
                        $sq->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

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
                    $query->whereDate('date', $now->toDateString());
                    break;
                case 'this_week':
                    $query->whereBetween('date', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereYear('date', $now->year)->whereMonth('date', $now->month);
                    break;
                case 'this_year':
                    $query->whereYear('date', $now->year);
                    break;
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['date', 'quantity', 'total_price', 'net_total', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('date', 'desc');
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $pigSales = $query->paginate($perPage);

        // ดึงราคาหมูล่าสุดจาก CPF
        $latestPrice = PigPriceService::getLatestPrice();

        return view('admin.pig_sales.index', compact('farms', 'batches', 'barns', 'pens', 'pigDeaths', 'pigSales', 'latestPrice'));
    }

    //--------------------------------------- Create ------------------------------------------//

    //--------------------------------------- Store Sale ------------------------------------------//

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->create($request);
    }

    //--------------------------------------- Create Sale ------------------------------------------//

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
                'quantities.*' => 'required|numeric|min:1', // Changed to numeric and min:1
                'date' => 'required|date',
                'sell_type' => 'required|string',
                'total_quantity' => 'required|integer|min:1',
                'total_weight' => 'required|numeric|min:0.01',
                'price_per_kg' => 'required|numeric|min:0',
                'total_price' => 'required|numeric|min:0',
                'net_total' => 'required|numeric',
                'buyer_name' => 'required|string|max:255',
                'shipping_cost' => 'nullable|numeric|min:0',
                'cpf_reference_price' => 'nullable|numeric',
                'cpf_reference_date' => 'nullable|date',
                'note' => 'nullable|string',
                'pig_loss_id' => 'nullable|exists:pig_deaths,id',
            ]);

            // บันทึกรายละเอียดการขายจากหลายคอก (ลด current_quantity แต่ไม่ลด allocated_pigs)
            $detailsData = [];
            foreach ($validated['selected_pens'] as $penId) {
                $quantity = $validated['quantities'][$penId] ?? 0;

                if ($quantity > 0) {
                    $result = PigInventoryHelper::reduceCurrentQuantityOnly(
                        $validated['batch_id'],
                        $penId,
                        $quantity
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
            // เตรียมข้อมูลสำหรับบันทึก - เลือกเฉพาะ column ที่มีใน table
            $saleData = [
                'farm_id' => $validated['farm_id'],
                'batch_id' => $validated['batch_id'],
                'pen_id' => $validated['selected_pens'][0], // ใช้คอกแรก
                'quantity' => $validated['total_quantity'],
                'total_weight' => $validated['total_weight'],
                'price_per_kg' => $validated['price_per_kg'],
                'total_price' => $validated['total_price'],
                'net_total' => $validated['net_total'],
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'cpf_reference_price' => $validated['cpf_reference_price'] ?? null,
                'cpf_reference_date' => $validated['cpf_reference_date'] ?? null,
                'payment_status' => 'รอชำระ',
                'paid_amount' => 0,
                'balance' => $validated['net_total'],
                'buyer_name' => $validated['buyer_name'],
                'note' => $validated['note'] ?? null,
                'date' => $validated['date'],
                'sell_type' => $validated['sell_type'],
                'created_by' => auth()->id(),
                'status' => 'completed',
            ];

            // Generate sale_number: PS-YYYYMMDD-XXX
            $date = date('Ymd', strtotime($saleData['date']));
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

            $saleData['sale_number'] = $prefix . str_pad($runningNumber, 3, '0', STR_PAD_LEFT);

            // คำนวณ price_per_pig (ราคาต่อตัว)
            // ใช้ net_total หารด้วย quantity
            if ($saleData['quantity'] > 0) {
                $saleData['price_per_pig'] = $saleData['net_total'] / $saleData['quantity'];
            } else {
                $saleData['price_per_pig'] = 0;
            }

            $pigSale = PigSale::create($saleData);

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

            // บันทึกค่าขนส่งลงใน costs table (ถ้ามี)
            if ($validated['shipping_cost'] && $validated['shipping_cost'] > 0) {
                Cost::create([
                    'farm_id'        => $validated['farm_id'],
                    'batch_id'       => $validated['batch_id'],
                    'date'           => $validated['date'],
                    'cost_type'      => 'shipping', // ค่าขนส่ง
                    'item_code'      => 'PS-' . $pigSale->sale_number, // อ้างอิงจากเลขที่ขายหมู
                    'quantity'       => 1,
                    'unit'           => 'ครั้ง',
                    'transport_cost' => $validated['shipping_cost'],
                    'total_price'    => $validated['shipping_cost'],
                    'note'           => 'ค่าขนส่งจากการขายหมู (ขาย ' . $validated['total_quantity'] . ' ตัว)',
                ]);
            }

            DB::commit();

            return redirect()->route('pig_sales.index')->with('success', 'บันทึกการขายหมูสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PigSale Create Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    //--------------------------------------- Approve Sale ------------------------------------------//

    public function show($id)
    {
        try {
            $pigSale = PigSale::with(['farm', 'batch', 'payments'])->findOrFail($id);

            // คำนวณ total paid และ remaining amount
            $totalPaid = Payment::where('pig_sale_id', $pigSale->id)
                ->where('status', 'approved')
                ->sum('amount');

            $remainingAmount = $pigSale->net_total - $totalPaid;

            return view('admin.pig_sales.show', [
                'pigSale' => $pigSale,
                'totalPaid' => $totalPaid,
                'remainingAmount' => max(0, $remainingAmount),
            ]);
        } catch (\Exception $e) {
            Log::error('PigSaleController - show Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'ไม่พบข้อมูลการขายหมู');
        }
    }

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
            $isAdmin = $user && $user->roles && $user->roles->contains('name', 'admin');
            if ($pigSale->created_by === $user->name && !$isAdmin) {
                return redirect()->back()->with('error', 'คุณไม่สามารถอนุมัติการขายที่ตัวเองสร้างได้');
            }

            // บันทึกข้อมูลการอนุมัติ
            $pigSale->approved_by = $user->name;
            $pigSale->approved_at = now();
            $pigSale->save();

            // ✅ บันทึกรายได้จากการขายหมู (เมื่อสำเร็จการอนุมัติ)
            $revenueResult = RevenueHelper::recordPigSaleRevenue($pigSale);

            if (!$revenueResult['success']) {
                Log::warning('PigSale Approve - Revenue recording failed: ' . $revenueResult['message']);
            }

            // ✅ คำนวณกำไรและบันทึกลง profit table
            $profitResult = RevenueHelper::calculateAndRecordProfit($pigSale->batch_id);

            if (!$profitResult['success']) {
                Log::warning('PigSale Approve - Profit calculation failed: ' . $profitResult['message']);
            }

            // ✅ แจ้งเตือนผู้สร้างการขายว่าได้รับการอนุมัติแล้ว
            \App\Helpers\NotificationHelper::notifyUserPigSaleApproved($pigSale, $user);

            DB::commit();

            return redirect()->route('pig_sales.index')->with('success', 'อนุมัติการขายเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PigSale Approve Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    //--------------------------------------- Reject Sale ------------------------------------------//

    public function reject(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $pigSale = PigSale::findOrFail($id);

            // ตรวจสอบว่าอนุมัติแล้วหรือไม่
            if ($pigSale->approved_at) {
                return redirect()->back()->with('error', 'ไม่สามารถปฏิเสธการขายที่อนุมัติแล้ว');
            }

            // ตรวจสอบไม่ให้ปฏิเสธการขายที่ตัวเองสร้าง (ยกเว้น Admin)
            $user = auth()->user();
            $isAdmin = $user && $user->roles && $user->roles->contains('name', 'admin');
            if ($pigSale->created_by === $user->name && !$isAdmin) {
                return redirect()->back()->with('error', 'คุณไม่สามารถปฏิเสธการขายที่ตัวเองสร้างได้');
            }

            // ตรวจสอบว่ามีเหตุผลในการปฏิเสธหรือไม่
            $validated = $request->validate([
                'rejection_reason' => 'nullable|string|max:500',
            ]);

            // บันทึกข้อมูลการปฏิเสธ
            $pigSale->status = 'rejected';
            $pigSale->rejection_reason = $validated['rejection_reason'] ?? 'ไม่ระบุเหตุผล';
            $pigSale->rejected_by = $user->name;
            $pigSale->rejected_at = now();
            $pigSale->save();

            DB::commit();

            return redirect()->route('pig_sales.index')->with('success', 'ปฏิเสธการขายเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PigSale Reject Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Upload Receipt for pig sale payment
     */
    public function uploadReceipt(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $pigSale = PigSale::findOrFail($id);

            $validated = $request->validate([
                'paid_amount' => 'required|numeric|min:0',
                'payment_method' => 'required|string',
                'receipt_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max - ต้องมีไฟล์
            ]);

            // ตรวจสอบว่าจำนวนเงินที่ชำระไม่เกินยอดคงเหลือ
            if ($validated['paid_amount'] > $pigSale->balance) {
                return redirect()->back()->with('error', 'จำนวนเงินที่ชำระเกินยอดคงเหลือ');
            }

            // อัปโหลดไฟล์ (ต้องมี)
            $uploadedFileUrl = null;
            if ($request->hasFile('receipt_file') && $request->file('receipt_file')->isValid()) {
                try {
                    $uploadResult = Cloudinary::upload(
                        $request->file('receipt_file')->getRealPath(),
                        ['folder' => 'receipt_files']
                    );
                    // CloudinaryEngine::upload() returns the engine instance
                    $uploadedFileUrl = $uploadResult->getSecurePath();
                } catch (\Exception $e) {
                    Log::error('Cloudinary upload error in PigSale: ' . $e->getMessage());
                    return redirect()->back()->with('error', 'ไม่สามารถอัปโหลดไฟล์สลิปได้ (' . $e->getMessage() . ')');
                }
            }

            // ตรวจสอบว่าอัปโหลดสำเร็จ
            if (!$uploadedFileUrl) {
                return redirect()->back()->with('error', 'ไม่สามารถอัปโหลดไฟล์สลิปได้ กรุณาลองใหม่');
            }

            // อัปเดทข้อมูลการชำระเงิน
            $pigSale->paid_amount += $validated['paid_amount'];
            $pigSale->balance = $pigSale->net_total - $pigSale->paid_amount;

            // บันทึกสถานะเดิมเพื่อตรวจสอบการเปลี่ยนแปลง
            $oldPaymentStatus = $pigSale->payment_status;

            // อัปเดทสถานะการชำระเงิน
            if ($pigSale->balance <= 0) {
                $pigSale->payment_status = 'ชำระแล้ว';
                $pigSale->balance = 0;
            } elseif ($pigSale->paid_amount > 0 && $pigSale->balance > 0) {
                $pigSale->payment_status = 'ชำระบางส่วน';
            }

            // บันทึก receipt file (ต้องมี)
            $pigSale->receipt_file = $uploadedFileUrl;

            // บันทึกวิธีชำระเงิน
            $pigSale->payment_method = $validated['payment_method'];

            $pigSale->save();

            // ส่งแจ้งเตือนให้ Admin อนุมัติการชำระเงิน
            NotificationHelper::notifyAdminsPigSalePaymentRecorded($pigSale, auth()->user());

            // ✅ ส่งแจ้งเตือนให้ผู้สร้างเมื่อสถานะการชำระเปลี่ยน
            if ($oldPaymentStatus !== $pigSale->payment_status) {
                NotificationHelper::notifyUserPigSalePaymentStatusChanged($pigSale, $oldPaymentStatus, $pigSale->payment_status);
            }

            DB::commit();

            $message = 'บันทึกการชำระเงินสำเร็จ - ';
            $message .= $pigSale->payment_status === 'ชำระแล้ว'
                ? 'ชำระครบแล้ว'
                : 'ชำระแล้ว ' . number_format((float)$pigSale->paid_amount, 2) . ' บาท คงเหลือ ' . number_format((float)$pigSale->balance, 2) . ' บาท (รอ admin อนุมัติ)';

            return redirect()->route('pig_sales.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PigSale Upload Receipt Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    //--------------------------------------- Cancel (Delete) ------------------------------------------//

    /**
     * ยกเลิกการขายหมู (Require Admin Approval)
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pigSale = PigSale::findOrFail($id);

            // สร้าง Notification สำหรับ Admin approval
            Notification::create([
                'user_id' => auth()->id(),
                'type' => 'cancel_pig_sale',
                'title' => 'ขอยกเลิกการขายหมู',
                'message' => "ขอยกเลิกการขาย {$pigSale->quantity} ตัว (ฟาร์ม: {$pigSale->farm->farm_name}, รุ่น: {$pigSale->batch->batch_code})",
                'related_model' => 'PigSale',
                'related_model_id' => $pigSale->id,
                'approval_status' => 'pending',
                'url' => route('payment_approvals.index'),
                'is_read' => false,
            ]);

            DB::commit();

            return redirect()->route('pig_sales.index')
                ->with('success', 'ขอยกเลิกการขายสำเร็จ (รอ Admin อนุมัติ)');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PigSale Cancel Request Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Admin อนุมัติยกเลิกการขาย (ใช้จาก PaymentApprovalController)
     */
    public function confirmCancel($id)
    {
        DB::beginTransaction();
        try {
            $pigSale = PigSale::findOrFail($id);
            $batchId = $pigSale->batch_id;

            // คืนจำนวนหมู current_quantity กลับทุกคอกตามรายละเอียดที่บันทึกไว้
            $details = PigSaleDetail::where('pig_sale_id', $pigSale->id)->get();

            if ($details->isEmpty()) {
                // ถ้าไม่มีรายละเอียด (ข้อมูลเก่า)
                if ($pigSale->pen_id && $pigSale->quantity > 0) {
                    $allocation = BatchPenAllocation::where('batch_id', $pigSale->batch_id)
                        ->where('pen_id', $pigSale->pen_id)
                        ->lockForUpdate()
                        ->first();

                    if ($allocation) {
                        $allocation->current_quantity = ($allocation->current_quantity ?? 0) + $pigSale->quantity;
                        $allocation->save();
                    }

                    $batch = Batch::lockForUpdate()->find($pigSale->batch_id);
                    if ($batch) {
                        $batch->current_quantity = ($batch->current_quantity ?? 0) + $pigSale->quantity;
                        $batch->save();
                    }
                }
            } else {
                // คืนหมูแต่ละคอก
                foreach ($details as $detail) {
                    $allocation = BatchPenAllocation::where('batch_id', $pigSale->batch_id)
                        ->where('pen_id', $detail->pen_id)
                        ->lockForUpdate()
                        ->first();

                    if ($allocation) {
                        $allocation->current_quantity = ($allocation->current_quantity ?? 0) + $detail->quantity;
                        $allocation->save();
                    }

                    $batch = Batch::lockForUpdate()->find($pigSale->batch_id);
                    if ($batch) {
                        $batch->current_quantity = ($batch->current_quantity ?? 0) + $detail->quantity;
                        $batch->save();
                    }
                }
            }

            // Soft Delete
            $pigSale->update([
                'status' => 'ยกเลิกการขาย',
                'payment_status' => 'ยกเลิกการขาย',
            ]);

            // ✅ แจ้งเตือนผู้สร้างการขายว่าถูกยกเลิก
            NotificationHelper::notifyUserPigSaleCancelled($pigSale);

            // ✅ อัปเดตแจ้งเตือนเก่าให้ mark ว่า "ยกเลิกแล้ว"
            NotificationHelper::markPigSaleNotificationsAsCancelled($pigSale->id);

            // Recalculate profit
            RevenueHelper::calculateAndRecordProfit($batchId);

            DB::commit();

            return redirect()->route('payment_approvals.index')
                ->with('success', 'ยกเลิกการขายสำเร็จ (คืนหมูกลับเล้า-คอกแล้ว)');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PigSale Confirm Cancel Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    //--------------------------------------- EXPORT ------------------------------------------//

    // Export PDF
    public function exportPdf()
    {
        $pigSales = PigSale::with(['farm', 'batch', 'pigLoss'])->get();

        $pdf = Pdf::loadView('admin.pig_sales.exports.pdf', compact('pigSales'))
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
                    $sell->date ? Carbon::parse($sell->date)->format('d/m/Y') : '-',
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
