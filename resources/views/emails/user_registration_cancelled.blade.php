<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
            color: #333333;
        }
        .content p {
            margin: 0 0 15px 0;
            line-height: 1.6;
        }
        .info-box {
            background-color: #f3f4f6;
            border-left: 4px solid #6366f1;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666666;
        }
        .cancel-badge {
            background-color: #8b5cf6;
            color: #ffffff;
            padding: 3px 10px;
            border-radius: 20px;
            display: inline-block;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⛔ บัญชีของคุณถูกยกเลิกแล้ว</h1>
        </div>
        <div class="content">
            <p>สวัสดี {{ $user->name }},</p>

            <p>
                บัญชีของคุณได้ถูกยกเลิกลงทะเบียน <span class="cancel-badge">ยกเลิก</span>
            </p>

            <div class="info-box">
                <strong>สถานะ:</strong> บัญชีปิดใช้งาน<br>
                <strong>วันที่ยกเลิก:</strong> {{ date('d/m/Y H:i') }} น.
            </div>

            <p>
                บัญชีของคุณจะไม่สามารถเข้าสู่ระบบได้อีก ข้อมูลส่วนตัวของคุณจะถูกเก็บไว้ตามนโยบายความเป็นส่วนตัว
            </p>

            <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                หากคุณต้องการสอบถามเพิ่มเติม สามารถติดต่อผู้ดูแลระบบได้
            </p>
        </div>
        <div class="footer">
            <p>ระบบจัดการฟาร์มหมู | © {{ date('Y') }} All Rights Reserved</p>
            <p>กรุณาอย่าตอบกลับอีเมลนี้ เนื่องจากเป็นอีเมลอัตโนมัติ</p>
        </div>
    </div>
</body>
</html>
