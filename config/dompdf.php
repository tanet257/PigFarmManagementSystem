<?php

return [

    /*
    |--------------------------------------------------------------------------
    | DOMPDF Settings
    |--------------------------------------------------------------------------
    |
    | ตั้งค่าพื้นฐานสำหรับ dompdf เช่น path เก็บ font, cache, temp, default font
    |
    */

    'show_warnings' => false,

    'public_path' => null,

    'convert_entities' => true,

    'options' => [
        // 📌 โฟลเดอร์เก็บฟอนต์ (ใช้ resources/fonts เพื่อความเป็นระเบียบ)
        "font_dir" => resource_path('fonts/'),

        // 📌 cache ฟอนต์ เก็บไว้ใน storage/fonts
        "font_cache" => storage_path('fonts/'),

        // 📌 temp directory
        "temp_dir" => sys_get_temp_dir(),

        // 📌 จำกัด dompdf ให้เข้าถึงได้แค่ project directory
        "chroot" => realpath(base_path()),

        // อนุญาต protocol ที่ใช้ได้
        'allowed_protocols' => [
            "file://" => ["rules" => []],
            "http://" => ["rules" => []],
            "https://" => ["rules" => []],
        ],

        'log_output_file' => null,

        // 📌 ไม่ต้อง subset font (ถ้าอยากให้ PDF เบาลงให้เปิด true)
        "enable_font_subsetting" => false,

        // backend ใช้ CPDF (default)
        "pdf_backend" => "CPDF",

        // 📄 paper size
        "default_paper_size" => "a4",

        // 📄 orientation
        'default_paper_orientation' => "portrait",

        // 📌 ฟอนต์ default (ตั้งเป็น sarabun เพื่อรองรับภาษาไทย)
        "default_font" => "sarabun",

        // DPI
        "dpi" => 96,

        // ปิด inline php เพื่อความปลอดภัย
        "enable_php" => false,

        // อนุญาต javascript
        "enable_javascript" => true,

        // อนุญาตโหลดไฟล์จาก remote เช่น รูปจาก url
        "enable_remote" => true,

        // ปรับ line height
        "font_height_ratio" => 1.1,

        // เปิด HTML5 parser
        "enable_html5_parser" => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Fonts
    |--------------------------------------------------------------------------
    |
    | เพิ่มฟอนต์ที่ต้องการใช้กับ dompdf
    | ตัวอย่างนี้ใช้ Sarabun (รองรับภาษาไทยเต็มรูปแบบ)
    |
    */

    'fonts' => [
        'sarabun' => [
            'R'  => 'Sarabun-Regular.ttf',
            'B'  => 'Sarabun-Bold.ttf',
            'I'  => 'Sarabun-Italic.ttf',
            'BI' => 'Sarabun-BoldItalic.ttf',
        ],
    ],

];
