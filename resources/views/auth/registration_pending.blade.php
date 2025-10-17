<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div style="text-align:center; padding:20px; font-family: Arial, sans-serif;">

            <!-- Icon -->
            <div style="margin-bottom:20px; font-size:70px; color: #28a745;">
                <i class="fas fa-check-circle"></i>
            </div>

            <!-- Heading -->
            <h2 style="margin-bottom:15px; color:#FF5B22; font-weight:600; font-size:1.8rem;">
                ลงทะเบียนสำเร็จ!
            </h2>

            <!-- Info alert -->
            <div style="display:inline-block; background-color:#d1ecf1; color:#0c5460; padding:5px 10px; border-radius:5px; margin-bottom:15px; font-size:0.9rem;">
                <i class="fas fa-info-circle" style="margin-right:5px;"></i>
                บัญชีของคุณรอการอนุมัติจาก Admin
            </div>

            <!-- Status -->
            <p style="color:#6c757d; font-size:0.9rem; margin-bottom:15px;">
                บัญชีของคุณอยู่ในสถานะ
                <span style="background-color:#f39c12; color:#fff; padding:2px 8px; border-radius:10px; font-size:0.8rem;">
                    รอการอนุมัติ
                </span>
                <br>
                กรุณารอผู้ดูแลระบบอนุมัติ
            </p>

            <!-- Steps -->
            <div style="background-color:#fff8f0; padding:10px 15px; border-radius:5px; text-align:left; font-size:0.88rem; margin-bottom:15px;">
                <h5 style="margin-bottom:5px; font-weight:500;">ขั้นตอนต่อไป:</h5>
                <ol style="padding-left:20px; margin:0;">
                    <li>Admin ตรวจสอบข้อมูลและกำหนดบทบาท</li>
                    <li>หลังอนุมัติคุณสามารถเข้าสู่ระบบได้</li>
                    <li>จะได้รับแจ้งเตือนทางอีเมล (ถ้ามี)</li>
                </ol>
            </div>

            <!-- Note -->
            <p style="color:#6c757d; font-size:0.85rem; margin-bottom:15px;">
                <i class="fas fa-clock" style="margin-right:5px;"></i>
                โดยปกติการอนุมัติใช้เวลา 24-48 ชั่วโมง
            </p>

            <!-- Button -->
            <a href="{{ route('login') }}"
               style="display:inline-block; width:100%; background-color:#FF5B22; color:#fff; font-weight:500; text-decoration:none; padding:10px 0; border-radius:5px; font-size:1rem;">
                <i class="fas fa-home" style="margin-right:5px;"></i>กลับสู่หน้าหลัก
            </a>

        </div>
    </x-authentication-card>
</x-guest-layout>
