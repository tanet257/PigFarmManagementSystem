<?php

namespace App\Helpers;

use App\Models\StoreHouse;
use App\Models\StoreHouseAuditLog;
use App\Models\Cost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class StoreHouseHelper
{
    /**
     * สร้าง StoreHouse record จาก Cost (auto-create with audit)
     *
     * @param Cost $cost - Cost object ที่จะนำมาสร้าง StoreHouse
     * @return array ['success' => bool, 'message' => string, 'store_house' => StoreHouse]
     */
    public static function createFromCost(Cost $cost)
    {
        try {
            // ตรวจสอบ item_code มี StoreHouse แล้วหรือไม่
            $existingStoreHouse = StoreHouse::where('farm_id', $cost->farm_id)
                ->where('item_code', $cost->item_code)
                ->first();

            if ($existingStoreHouse) {
                // ถ้ามีแล้ว เพิ่มจำนวน
                return self::increaseQuantity(
                    $existingStoreHouse,
                    $cost->quantity,
                    'purchase',
                    $cost->id,
                    'เพิ่มจากการซื้อ: ' . $cost->note
                );
            } else {
                // ถ้าไม่มี สร้างใหม่
                $storeHouse = StoreHouse::create([
                    'farm_id' => $cost->farm_id,
                    'item_type' => self::mapCostTypeToItemType($cost->cost_type),
                    'item_code' => $cost->item_code,
                    'item_name' => $cost->item_name ?? self::getItemNameByCostType($cost->cost_type),
                    'stock' => $cost->quantity,
                    'unit' => $cost->unit ?? 'kg',
                    'source' => 'purchase',
                    'created_by' => Auth::id() ?? 1,
                    'cost_id' => $cost->id,
                    'reason' => 'สร้างจากการซื้อ: ' . $cost->note,
                    'date' => $cost->date ?? now(),
                ]);

                // สร้าง audit log
                self::createAuditLog(
                    $storeHouse->id,
                    'create',
                    'quantity',
                    null,
                    $cost->quantity,
                    null,
                    $cost->price_per_unit ?? 0,
                    'สร้างจากการซื้อ'
                );

                Log::info('StoreHouse created from Cost', [
                    'store_house_id' => $storeHouse->id,
                    'cost_id' => $cost->id,
                    'quantity' => $cost->quantity,
                ]);

                return [
                    'success' => true,
                    'message' => 'สร้าง StoreHouse สำเร็จ',
                    'store_house' => $storeHouse,
                ];
            }
        } catch (\Exception $e) {
            Log::error('StoreHouseHelper::createFromCost Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'ไม่สามารถสร้าง StoreHouse: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * เพิ่มจำนวน StoreHouse
     */
    public static function increaseQuantity($storeHouse, $quantity, $source = 'transfer', $costId = null, $reason = null)
    {
        try {
            $oldQuantity = $storeHouse->stock;
            $newQuantity = $oldQuantity + $quantity;

            $storeHouse->update([
                'stock' => $newQuantity,
                'updated_by' => Auth::id() ?? 1,
                'cost_id' => $costId,
            ]);

            // สร้าง audit log
            self::createAuditLog(
                $storeHouse->id,
                'update',
                'quantity',
                $oldQuantity,
                $newQuantity,
                null,
                null,
                $reason
            );

            Log::info('StoreHouse quantity increased', [
                'store_house_id' => $storeHouse->id,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
            ]);

            return [
                'success' => true,
                'message' => 'เพิ่มจำนวน StoreHouse สำเร็จ',
                'store_house' => $storeHouse,
            ];
        } catch (\Exception $e) {
            Log::error('StoreHouseHelper::increaseQuantity Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'ไม่สามารถเพิ่มจำนวน: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * ลดจำนวน StoreHouse
     */
    public static function decreaseQuantity($storeHouse, $quantity, $reason = null)
    {
        try {
            $oldQuantity = $storeHouse->stock;
            $newQuantity = max(0, $oldQuantity - $quantity);

            $storeHouse->update([
                'stock' => $newQuantity,
                'updated_by' => Auth::id() ?? 1,
            ]);

            // สร้าง audit log
            self::createAuditLog(
                $storeHouse->id,
                'update',
                'quantity',
                $oldQuantity,
                $newQuantity,
                null,
                null,
                $reason
            );

            Log::info('StoreHouse quantity decreased', [
                'store_house_id' => $storeHouse->id,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
            ]);

            return [
                'success' => true,
                'message' => 'ลดจำนวน StoreHouse สำเร็จ',
                'store_house' => $storeHouse,
            ];
        } catch (\Exception $e) {
            Log::error('StoreHouseHelper::decreaseQuantity Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'ไม่สามารถลดจำนวน: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * สร้าง audit log
     */
    private static function createAuditLog(
        $storeHouseId,
        $action,
        $changeType,
        $oldQuantity = null,
        $newQuantity = null,
        $oldPrice = null,
        $newPrice = null,
        $reason = null
    ) {
        try {
            StoreHouseAuditLog::create([
                'store_house_id' => $storeHouseId,
                'action' => $action,
                'change_type' => $changeType,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'user_id' => Auth::id() ?? 1,
                'reason' => $reason,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('StoreHouseHelper::createAuditLog Error: ' . $e->getMessage());
        }
    }

    /**
     * Map Cost type → Item type
     */
    private static function mapCostTypeToItemType($costType)
    {
        $mapping = [
            'piglet' => 'animal',
            'feed' => 'feed',
            'medicine' => 'medicine',
            'wage' => 'labor',
            'shipping' => 'service',
            'electric_bill' => 'utility',
            'water_bill' => 'utility',
            'other' => 'other',
        ];

        return $mapping[$costType] ?? 'other';
    }

    /**
     * Get item name by cost type
     */
    private static function getItemNameByCostType($costType)
    {
        $names = [
            'piglet' => 'ลูกหมู',
            'feed' => 'อาหารหมู',
            'medicine' => 'ยา',
            'wage' => 'เงินเดือน',
            'shipping' => 'ค่าขนส่ง',
            'electric_bill' => 'ค่าไฟฟ้า',
            'water_bill' => 'ค่าน้ำ',
            'other' => 'อื่น ๆ',
        ];

        return $names[$costType] ?? $costType;
    }
}
