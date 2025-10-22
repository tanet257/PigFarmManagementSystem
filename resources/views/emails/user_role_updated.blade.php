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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .role-box {
            background-color: #f3f4f6;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
        }
        .role-box strong {
            color: #667eea;
        }
        .old-role {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 10px 15px;
            margin: 10px 0;
        }
        .new-role {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 10px 15px;
            margin: 10px 0;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666666;
        }
        .role-badge {
            background-color: #667eea;
            color: #ffffff;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 บทบาท (Role) ถูกเปลี่ยนแล้ว</h1>
        </div>
        <div class="content">
            <p>สวัสดี {{ $user->name }},</p>

            <p>
                บทบาท (Role) ของคุณได้ถูกเปลี่ยนแล้ว โดย <strong>{{ $updatedBy->name }}</strong>
            </p>

            @if ($oldRole)
                <div class="role-box">
                    <p style="margin: 0 0 10px 0;"><strong>บทบาทเดิม:</strong></p>
                    <div class="old-role">
                        <i style="color: #ef4444;">❌</i> {{ $oldRole }}
                    </div>

                    <p style="margin: 15px 0 10px 0; text-align: center;"><strong>↓ เปลี่ยนเป็น ↓</strong></p>

                    <p style="margin: 10px 0 0 0;"><strong>บทบาทใหม่:</strong></p>
                    <div class="new-role">
                        <i style="color: #10b981;">✅</i> {{ $newRole }}
                    </div>
                </div>
            @else
                <div class="role-box">
                    <strong>บทบาทใหม่:</strong><br>
                    <span class="role-badge">{{ $newRole }}</span>
                </div>
            @endif

            <p>
                <strong>วันที่เปลี่ยน:</strong> {{ date('d/m/Y H:i') }} น.<br>
                <strong>เปลี่ยนโดย:</strong> {{ $updatedBy->name }}
            </p>

            <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                หากคุณมีคำถามเกี่ยวกับการเปลี่ยนแปลง สามารถติดต่อผู้ดูแลระบบได้
            </p>
        </div>
        <div class="footer">
            <p>ระบบจัดการฟาร์มหมู | © {{ date('Y') }} All Rights Reserved</p>
            <p>กรุณาอย่าตอบกลับอีเมลนี้ เนื่องจากเป็นอีเมลอัตโนมัติ</p>
        </div>
    </div>
</body>
</html>
