<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class UploadService
{
    /**
     * Upload file to Cloudinary
     * @param mixed $file ไฟล์ที่ต้องการอัปโหลด (จาก $request->file())
     * @param string $folder โฟลเดอร์ใน Cloudinary
     * @return array ['success' => bool, 'url' => string|null, 'error' => string|null]
     */
    public static function uploadToCloudinary($file, $folder = 'receipt_files')
    {
        try {
            if (!$file || !$file->isValid()) {
                return [
                    'success' => false,
                    'url' => null,
                    'error' => 'ไฟล์ไม่ถูกต้องหรือเสียหาย'
                ];
            }

            $uploadResult = Cloudinary::upload(
                $file->getRealPath(),
                ['folder' => $folder]
            );

            $secureUrl = $uploadResult->getSecurePath();
            
            return [
                'success' => true,
                'url' => $secureUrl,
                'error' => null
            ];
        } catch (\Exception $e) {
            Log::error('Cloudinary upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'url' => null,
                'error' => $e->getMessage()
            ];
        }
    }
}
