/**
 * ป้องกันการ back button หลัง logout
 * และตรวจสอบการ login ใหม่
 */

(function() {
    // บันทึก state ปัจจุบัน
    history.pushState(null, null, location.href);

    // ตรวจสอบ back button
    window.addEventListener('popstate', function() {
        // ถ้า user กด back button
        history.pushState(null, null, location.href);

        // ตรวจสอบ session โดยการขอ AJAX
        fetch('/check-session', {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (!data.authenticated) {
                // ถ้า session หมดแล้ว ให้ redirect ไปที่ login
                console.log('Session expired - redirecting to login');
                window.location.href = '/login';
            }
        })
        .catch(error => {
            console.error('Error checking session:', error);
            // ในกรณี error ให้ redirect ไปที่ login เพื่อความปลอดภัย
            window.location.href = '/login';
        });
    });

    // ตรวจสอบ session ทั้งหมด 30 วินาที
    setInterval(function() {
        // ข้ามการตรวจสอบถ้า page ไม่ใช่ authenticated pages
        const currentUrl = window.location.pathname;
        const authenticatedRoutes = ['/home', '/admin_index', '/batches', '/dairy_records', '/pig_sales', '/pig_entry_records', '/storehouses', '/inventory_movements', '/batch_pen_allocations', '/notifications', '/user_management'];

        const isAuthenticatedRoute = authenticatedRoutes.some(route => currentUrl.includes(route));

        if (isAuthenticatedRoute) {
            fetch('/check-session', {
                method: 'GET',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (!data.authenticated) {
                    console.log('Session expired - redirecting to login');
                    window.location.href = '/login';
                }
            })
            .catch(error => {
                console.error('Error checking session:', error);
            });
        }
    }, 30000); // ตรวจสอบทุก 30 วินาที
})();
