/**
 * Common table click handler for all clickable rows
 * ใช้ร่วมกับ class="clickable-row" data-row-click="#modalId"
 */

function setupClickableRows() {
    // จัดการคลิกแถว
    document.querySelectorAll(".clickable-row").forEach(function(row) {
        row.addEventListener("click", function(e) {
            const modalTarget = row.getAttribute("data-row-click");
            if (modalTarget) {
                const modal = document.querySelector(modalTarget);
                if (modal) new bootstrap.Modal(modal).show();
            }
        });
    });

    // ป้องกันคลิกปุ่ม/ฟอร์ม/ลิงก์ ภายในแถวไปกระตุ้น modal หลัก
    document.querySelectorAll(".clickable-row button, .clickable-row a, .clickable-row form").forEach(
        el => {
            el.addEventListener("click", e => e.stopImmediatePropagation());
        }
    );
}

// เรียกใช้เมื่อ DOM ready
document.addEventListener('DOMContentLoaded', setupClickableRows);
