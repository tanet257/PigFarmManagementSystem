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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
        .reason-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .reason-box strong {
            color: #856404;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666666;
        }
        .reject-badge {
            background-color: #ef4444;
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
            <h1>❌ คำขอลงทะเบียนถูกปฏิเสธ</h1>
        </div>
        <div class="content">
            <p>สวัสดี {{ $user->name }},</p>

            <p>
                ขออภัย แต่คำขอลงทะเบียนของคุณถูกปฏิเสธ <span class="reject-badge">ปฏิเสธ</span>
            </p>

            <div class="reason-box">
                <strong>เหตุผลในการปฏิเสธ:</strong><br>
                {{ $reason }}
            </div>

            <p>
                ผู้ปฏิเสธ: <strong>{{ $rejectedBy->name }}</strong><br>
                วันที่ปฏิเสธ: <strong>{{ date('d/m/Y H:i') }} น.</strong>
            </p>

            <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                หากคุณมีคำถามหรือต้องการทำคำขอใหม่ สามารถติดต่อผู้ดูแลระบบได้
            </p>
        </div>
        <div class="footer">
            <p>ระบบจัดการฟาร์มหมู | © {{ date('Y') }} All Rights Reserved</p>
            <p>กรุณาอย่าตอบกลับอีเมลนี้ เนื่องจากเป็นอีเมลอัตโนมัติ</p>
        </div>
    </div>
</body>
</html>
