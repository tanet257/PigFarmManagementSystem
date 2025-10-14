<?php

namespace App\Http\Controllers;

use App\Services\PigPriceService;
use Illuminate\Http\Request;

class PigPriceController extends Controller
{
    /**
     * แสดงราคาหมูล่าสุด (API)
     */
    public function getLatestPrice()
    {
        $price = PigPriceService::getLatestPrice();

        if (!$price) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถดึงข้อมูลราคาได้',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $price,
        ]);
    }

    /**
     * แสดงประวัติราคา (API)
     */
    public function getPriceHistory()
    {
        $prices = PigPriceService::getPriceHistory();

        return response()->json([
            'success' => true,
            'data' => $prices,
            'count' => count($prices),
        ]);
    }

    /**
     * ล้าง cache และดึงข้อมูลใหม่
     */
    public function refreshPrice()
    {
        PigPriceService::clearCache();
        $price = PigPriceService::getLatestPrice();

        return response()->json([
            'success' => true,
            'message' => 'อัพเดทราคาสำเร็จ',
            'data' => $price,
        ]);
    }
}
