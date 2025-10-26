@component('mail::message')
# รีเซตรหัสผ่าน

สวัสดี {{ $userName }},

เราได้รับคำขอจากคุณเพื่อรีเซตรหัสผ่านของบัญชี Pig Farm Management System

@component('mail::button', ['url' => $actionUrl, 'color' => 'primary'])
รีเซตรหัสผ่าน
@endcomponent

ลิงก์นี้จะหมดอายุในวันที่ {{ $expiresAt->format('d/m/Y H:i') }} น.

---

**ข้อมูลสำคัญ:**
- ลิงก์นี้จะหมดอายุในวันที่ {{ $expiresAt->format('d/m/Y H:i') }} น.
- ถ้าคุณไม่ได้ขอรีเซตรหัสผ่าน ให้ละเว้นอีเมลนี้
- ห้ามบอกลิงก์นี้ให้ใครรู้

หากมีปัญหาใด ๆ โปรดติดต่อ Admin

ขอบคุณ,<br>
{{ config('app.name') }}
@endcomponent
