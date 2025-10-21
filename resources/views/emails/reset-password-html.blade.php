<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .header {
            background-color: #FF5B22;
            color: white;
            padding: 20px;
            border-radius: 6px 6px 0 0;
            text-align: center;
        }

        .content {
            background-color: white;
            padding: 30px;
        }

        .button {
            display: inline-block;
            background-color: #FF5B22;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }

        .button-container {
            text-align: center;
        }

        .footer {
            font-size: 12px;
            color: #777;
            padding: 20px;
            text-align: center;
        }

        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }

        .warning strong {
            color: #856404;
        }

        .warning li {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>🔐 รีเซตรหัสผ่าน</h2>
        </div>

        <div class="content">
            <p>สวัสดี <strong>{{ $userName }}</strong>,</p>

            <p>เราได้รับคำขอจากคุณเพื่อรีเซตรหัสผ่านของบัญชี Pig Farm Management System</p>

            <div class="button-container">
                <a href="{{ $actionUrl }}" class="button">รีเซตรหัสผ่าน</a>
            </div>

            <p>หรือก็อปปี้ลิงก์นี้ไปวางในเบราว์เซอร์:</p>
            <p style="word-break: break-all; background-color: #f5f5f5; padding: 10px; border-radius: 4px;">
                {{ $actionUrl }}</p>

            <div class="warning">
                <strong>⚠️ ข้อมูลสำคัญ:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>ลิงก์นี้จะหมดอายุในวันที่ <strong>{{ $expiresAt->format('d/m/Y H:i') }}</strong> น.</li>
                    <li>ถ้าคุณไม่ได้ขอรีเซตรหัสผ่าน ให้ละเว้นอีเมลนี้</li>
                    <li>ห้ามบอกลิงก์นี้ให้ใครรู้</li>
                </ul>
            </div>

            <p>หากมีปัญหาใด ๆ โปรดติดต่อ Admin</p>

            <p>ขอบคุณ,<br>{{ config('app.name') }}</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
