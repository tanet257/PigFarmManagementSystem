<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="card">
            <div class="card-body text-center p-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
                </div>
                
                <h2 class="mb-3" style="color: #FF5B22;">ลงทะเบียนสำเร็จ!</h2>
                
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>บัญชีของคุณรอการอนุมัติจาก Admin</strong>
                </div>

                <p class="text-muted mb-4">
                    ขอบคุณที่ลงทะเบียนเข้าใช้งานระบบ Pig Farm Management System<br>
                    บัญชีของคุณอยู่ในสถานะ <span class="badge bg-warning">รอการอนุมัติ</span><br>
                    กรุณารอให้ผู้ดูแลระบบอนุมัติบัญชีของคุณก่อน
                </p>

                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">ขั้นตอนต่อไป:</h5>
                        <ol class="text-start">
                            <li class="mb-2">Admin จะตรวจสอบข้อมูลของคุณ</li>
                            <li class="mb-2">Admin จะกำหนดบทบาท (Role) ให้กับบัญชีของคุณ</li>
                            <li class="mb-2">เมื่อได้รับการอนุมัติแล้ว คุณจะสามารถเข้าสู่ระบบได้</li>
                            <li>คุณจะได้รับการแจ้งเตือนทางอีเมล (ถ้ามี)</li>
                        </ol>
                    </div>
                </div>

                <p class="text-muted mb-4">
                    <i class="fas fa-clock me-2"></i>
                    โดยปกติการอนุมัติจะใช้เวลาประมาณ 24-48 ชั่วโมง
                </p>

                <div class="d-grid gap-2">
                    <a href="{{ route('home') }}" class="btn btn-lg" style="background-color: #FF5B22; border-color: #FF5B22; color: white;">
                        <i class="fas fa-home me-2"></i>กลับสู่หน้าหลัก
                    </a>
                </div>
            </div>
        </div>
    </x-authentication-card>
</x-guest-layout>
