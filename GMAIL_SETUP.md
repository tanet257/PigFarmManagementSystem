# คำแนะนำการตั้งค่า Gmail Forgot Password

## ขั้นตอนการตั้งค่า

### 1. สร้าง Google App Password

1. ไปที่ [Google Account Settings](https://myaccount.google.com/)
2. ไปที่เมนู **Security** (ซ้ายมือ)
3. เลื่อนลงหา **App passwords** (ต้อง enable 2-Step Verification ก่อน)
4. เลือก App: **Mail** และ Device: **Windows Computer** (หรือ Other)
5. Google จะสร้าง password ให้ (รูป: `xxxx xxxx xxxx xxxx`)

### 2. อัปเดต .env file

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="Pig Farm Management"
```

**หมายเหตุ:**
- `MAIL_USERNAME`: ใส่ email ของ Google ของคุณ
- `MAIL_PASSWORD`: ใส่ app password ที่ Google สร้างให้ (ไม่ใช่ password ธรรมชาติ)
- `MAIL_FROM_ADDRESS`: ใส่ email เดียวกับ MAIL_USERNAME
- `MAIL_FROM_NAME`: ชื่อที่จะแสดงในอีเมล

### 3. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

## การทดสอบ

1. ไปที่หน้า Login: `http://localhost:8000/login`
2. คลิก **Forgot your password?**
3. ใส่ email ของบัญชีที่ต้องการรีเซต
4. ตรวจสอบ Gmail Inbox ของคุณ
5. ค่อนข้างจะได้รับ Email พร้อม Reset Password Link
6. คลิก Link เพื่อไปยังหน้า Reset Password
7. ใส่ password ใหม่ 2 ครั้ง
8. ระบบจะทำการรีเซตรหัสผ่าน

## ทั่วไปปัญหา

### ปัญหา 1: "Authentication failed" หรือ "SMTP Error"
**วิธีแก้:** 
- ตรวจสอบว่า 2-Step Verification ถูก enable แล้ว
- ตรวจสอบ app password ถูกต้องหรือไม่
- ลองสร้าง app password ใหม่

### ปัญหา 2: Email ไม่ได้รับ
**วิธีแก้:**
- ตรวจสอบ Spam/Junk folder
- ตรวจสอบว่า MAIL_FROM_ADDRESS ถูกต้อง
- ตรวจสอบ Laravel logs: `storage/logs/laravel.log`

### ปัญหา 3: Link ใน Email ไม่ทำงาน
**วิธีแก้:**
- ตรวจสอบว่า `APP_URL` ใน .env ตรงกับ URL ของเว็บไซต์
- ลิงก์จะหมดอายุใน 60 นาที

## การตั้งค่าอื่นๆ

### ถ้าใช้ Email อื่น (ไม่ใช่ Gmail)

**Mailtrap (สำหรับ Development):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="test@example.com"
```

**Outlook/Office 365:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_USERNAME=your-email@outlook.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@outlook.com"
```

## Files ที่แก้ไข

1. `.env` - Email configuration
2. `app/Mail/ResetPasswordMail.php` - Custom reset password mail class
3. `resources/views/emails/reset_password.blade.php` - Email template
4. `app/Providers/FortifyServiceProvider.php` - Override password reset notification

## Resources

- [Laravel Password Reset Docs](https://laravel.com/docs/passwords)
- [Laravel Mail Docs](https://laravel.com/docs/mail)
- [Gmail App Passwords](https://support.google.com/accounts/answer/185833)
