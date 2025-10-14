<?php

// ทดสอบ Password Validation Rules
// รันด้วย: php test_password_validation.php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Validator;

echo "🔐 ทดสอบ Password Policy\n";
echo "========================\n\n";

$testCases = [
    // [password, expected_result, description]
    ['12345678', false, 'ตัวเลขอย่างเดียว 8 ตัว'],
    ['password', false, 'ตัวพิมพ์เล็กอย่างเดียว (ไม่มีตัวเลข)'],
    ['PASSWORD', false, 'ตัวพิมพ์ใหญ่อย่างเดียว (ไม่มีตัวเลข)'],
    ['Pass1', false, 'สั้นเกินไป (5 ตัว)'],
    ['password123', false, 'ไม่มีตัวพิมพ์ใหญ่'],
    ['PASSWORD123', false, 'ไม่มีตัวพิมพ์เล็ก'],
    ['Password', false, 'ไม่มีตัวเลข'],
    ['Password123', true, 'ถูกต้อง! (มีครบ)'],
    ['MyPass2024', true, 'ถูกต้อง! (มีครบ)'],
    ['FarmPig99', true, 'ถูกต้อง! (มีครบ)'],
    ['Admin@123', true, 'ถูกต้อง! (มีครบ + อักขระพิเศษ)'],
];

$passCount = 0;
$failCount = 0;

foreach ($testCases as $index => $testCase) {
    [$password, $shouldPass, $description] = $testCase;

    $validator = Validator::make(
        ['password' => $password],
        [
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'not_regex:/^[0-9]+$/',
            ]
        ],
        [
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.regex' => 'รหัสผ่านต้องประกอบด้วย ตัวพิมพ์เล็ก (a-z), ตัวพิมพ์ใหญ่ (A-Z) และตัวเลข (0-9)',
            'password.not_regex' => 'รหัสผ่านไม่สามารถเป็นตัวเลขอย่างเดียวได้',
        ]
    );

    $passed = $validator->passes();
    $result = $passed === $shouldPass;

    echo sprintf(
        "%d. [%s] %s - %s\n",
        $index + 1,
        $result ? '✅' : '❌',
        str_pad($password, 15),
        $description
    );

    if (!$passed) {
        echo "   ❌ Error: " . $validator->errors()->first('password') . "\n";
    }

    if ($result) {
        $passCount++;
    } else {
        $failCount++;
    }

    echo "\n";
}

echo "========================\n";
echo "สรุปผลการทดสอบ:\n";
echo "✅ ผ่าน: $passCount/" . count($testCases) . "\n";
echo "❌ ไม่ผ่าน: $failCount/" . count($testCases) . "\n";
echo "========================\n";

if ($failCount === 0) {
    echo "🎉 ผ่านทุกเทสต์!\n";
} else {
    echo "⚠️  มีบางเทสต์ที่ไม่ผ่าน กรุณาตรวจสอบ\n";
}
