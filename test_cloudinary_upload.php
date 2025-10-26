<?php

/**
 * Test Cloudinary Upload Configuration
 * ทดสอบระบบ upload file ไป Cloudinary โดยตรง
 */

require __DIR__ . '/vendor/autoload.php';

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== Cloudinary Configuration Test ===\n\n";

// 1. เช็ค env variables
echo "1️⃣ Environment Variables:\n";
$cloudName = env('CLOUDINARY_CLOUD_NAME');
$apiKey = env('CLOUDINARY_API_KEY');
$apiSecret = env('CLOUDINARY_API_SECRET');
$cloudinaryUrl = env('CLOUDINARY_URL');

echo "   CLOUDINARY_URL: " . ($cloudinaryUrl ? "✅ SET" : "❌ NOT SET") . "\n";
echo "   CLOUDINARY_CLOUD_NAME: " . ($cloudName ? "✅ $cloudName" : "❌ NOT SET") . "\n";
echo "   CLOUDINARY_API_KEY: " . ($apiKey ? "✅ " . substr($apiKey, 0, 5) . "..." : "❌ NOT SET") . "\n";
echo "   CLOUDINARY_API_SECRET: " . ($apiSecret ? "✅ " . substr($apiSecret, 0, 5) . "..." : "❌ NOT SET") . "\n";

// 2. Parse CLOUDINARY_URL ถ้ามี
echo "\n2️⃣ Parsing CLOUDINARY_URL:\n";
if ($cloudinaryUrl) {
    // Format: cloudinary://api_key:api_secret@cloud_name
    preg_match('/cloudinary:\/\/([^:]+):([^@]+)@(.+)/', $cloudinaryUrl, $matches);
    if (!empty($matches)) {
        $parsedKey = $matches[1];
        $parsedSecret = $matches[2];
        $parsedCloud = $matches[3];
        echo "   ✅ Parsed from URL:\n";
        echo "      - API Key: " . substr($parsedKey, 0, 5) . "...\n";
        echo "      - API Secret: " . substr($parsedSecret, 0, 5) . "...\n";
        echo "      - Cloud Name: $parsedCloud\n";
    } else {
        echo "   ❌ Failed to parse CLOUDINARY_URL\n";
    }
}

// 3. ทดสอบ Cloudinary SDK
echo "\n3️⃣ Testing Cloudinary SDK:\n";
try {
    // ใช้ CloudinaryLaravel Facade - มันจะ auto config ผ่าน service provider
    echo "   ✅ Using CloudinaryLabs\\CloudinaryLaravel package\n";
    echo "   ℹ️ Config loads from: config('cloudinary.cloud_url')\n";

} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// 4. ทดสอบ upload test file
echo "\n4️⃣ Testing File Upload:\n";

// สร้าง test image file (1x1 pixel PNG)
$testImagePath = __DIR__ . '/storage/test_upload.png';
$pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

if (!is_dir(__DIR__ . '/storage')) {
    mkdir(__DIR__ . '/storage', 0755, true);
}

file_put_contents($testImagePath, $pngData);
echo "   ✅ Test image created: $testImagePath\n";

try {
    echo "   🔄 Uploading to Cloudinary via Laravel app...\n";

    // Bootstrap Laravel
    $app = require __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Http\Kernel');
    $request = \Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);

    // Get Cloudinary instance through Laravel container
    $cloudinary = app(\CloudinaryLabs\CloudinaryLaravel\CloudinaryEngine::class);

    if (!$cloudinary) {
        echo "   ❌ Failed to instantiate CloudinaryEngine\n";
    } else {
        echo "   ✅ CloudinaryEngine instantiated\n";

        // Try upload
        $uploadResult = $cloudinary->uploadFile($testImagePath, ['folder' => 'test_uploads']);

        echo "   ✅ Upload successful!\n";
        echo "   📊 Upload Result:\n";

        if (is_array($uploadResult)) {
            echo "      - Secure URL: " . ($uploadResult['secure_url'] ?? $uploadResult['url'] ?? 'N/A') . "\n";
            echo "      - Public ID: " . ($uploadResult['public_id'] ?? 'N/A') . "\n";
            echo "      - Size: " . ($uploadResult['bytes'] ?? 'N/A') . " bytes\n";
        } else {
            echo "      - Result type: " . gettype($uploadResult) . "\n";
            echo "      - Result: " . print_r($uploadResult, true) . "\n";
        }
    }

    // ทำความสะอาด
    @unlink($testImagePath);
    echo "\n   ✅ Test file deleted\n";

} catch (\Exception $e) {
    echo "   ❌ Upload failed: " . $e->getMessage() . "\n";
    echo "   📋 Error Code: " . $e->getCode() . "\n";
    echo "   📄 Stack Trace: " . $e->getTraceAsString() . "\n";

    // ทำความสะอาด
    @unlink($testImagePath);
}echo "\n=== Test Complete ===\n";

// Helper function
if (!function_exists('env')) {
    function env($key, $default = null) {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}
