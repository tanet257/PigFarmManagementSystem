<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PigPriceService
{
    /**
     * ดึงราคาหมูล่าสุดจากเว็บ CPF
     *
     * @return array|null
     */
    public static function getLatestPrice()
    {
        try {
            // Cache ข้อมูล 1 ชั่วโมง (เพราะราคาไม่ได้อัพเดทบ่อย)
            return Cache::remember('cpf_pig_price', 3600, function () {
                $url = 'https://www.cpffeed.com/pet6/';
                $response = Http::timeout(10)->get($url);

                if (!$response->successful()) {
                    Log::error('Failed to fetch CPF pig price', ['status' => $response->status()]);
                    return null;
                }

                $html = $response->body();

                // ใช้ Regular Expression ดึงข้อมูลจากตาราง HTML
                // ตัวอย่างแถวในตาราง: | ลูกสุกร ซี.พี. ขุนเล็ก | 41 | 2025-10-06 | 1400.00 | 56.00 | บาท/ตัว | ซีพีเอฟ(ประเทศไทย) |

                // Pattern: หาแถวแรกของตาราง (ราคาล่าสุด)
                $pattern = '/ลูกสุกร\s+ซี\.พี\.\s+ขุนเล็ก.*?(\d{4}-\d{2}-\d{2}).*?(\d+\.\d+)\s*<\/td>.*?(\d+\.\d+)\s*<\/td>/s';

                if (preg_match($pattern, $html, $matches)) {
                    return [
                        'date' => $matches[1],           // วันที่
                        'price_per_pig' => (float) $matches[2],  // ราคาต่อตัว
                        'price_per_kg' => (float) $matches[3],   // ราคาต่อกก.
                        'source' => 'CPF',
                        'updated_at' => now(),
                    ];
                }

                // ถ้า pattern แรกไม่ได้ ใช้ pattern สำรอง
                $alternativePattern = '/(\d{4}-\d{2}-\d{2})\s*<\/td>\s*<td[^>]*>\s*(\d+\.\d+)\s*<\/td>\s*<td[^>]*>\s*(\d+\.\d+)\s*<\/td>/';

                if (preg_match($alternativePattern, $html, $matches)) {
                    return [
                        'date' => $matches[1],
                        'price_per_pig' => (float) $matches[2],
                        'price_per_kg' => (float) $matches[3],
                        'source' => 'CPF',
                        'updated_at' => now(),
                    ];
                }

                Log::warning('Could not parse CPF pig price from HTML');
                return null;
            });
        } catch (\Exception $e) {
            Log::error('Error fetching CPF pig price', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * ดึงประวัติราคา (ถ้าต้องการ)
     *
     * @return array
     */
    public static function getPriceHistory()
    {
        try {
            $url = 'https://www.cpffeed.com/pet6/';
            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) {
                return [];
            }

            $html = $response->body();
            $prices = [];

            // ดึงทุกแถวในตาราง
            $pattern = '/(\d{4}-\d{2}-\d{2})\s*<\/td>\s*<td[^>]*>\s*(\d+\.\d+)\s*<\/td>\s*<td[^>]*>\s*(\d+\.\d+)\s*<\/td>/';

            if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $prices[] = [
                        'date' => $match[1],
                        'price_per_pig' => (float) $match[2],
                        'price_per_kg' => (float) $match[3],
                    ];
                }
            }

            return $prices;
        } catch (\Exception $e) {
            Log::error('Error fetching CPF pig price history', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * ล้าง cache
     */
    public static function clearCache()
    {
        Cache::forget('cpf_pig_price');
    }
}
