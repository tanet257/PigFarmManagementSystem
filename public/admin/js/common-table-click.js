/**
 * Common table click handler for all clickable rows
 * ใช้ร่วมกับ class="clickable-row" data-row-click="#modalId"
 */

document.addEventListener('DOMContentLoaded', function() {
    setupClickableRows();
});

function setupClickableRows() {
    const rows = document.querySelectorAll(".clickable-row");
    console.log('Found clickable rows:', rows.length);

    rows.forEach(function(row) {
        row.style.cursor = 'pointer';

        row.addEventListener("click", function(e) {
            // ไม่ทำการคลิก button, a, input
            const targetTag = e.target.tagName.toUpperCase();
            if (targetTag === 'BUTTON' || targetTag === 'A' || targetTag === 'INPUT' || targetTag === 'I') {
                console.log('Clicked on:', targetTag, 'ignoring...');
                return;
            }

            const modalId = row.getAttribute("data-row-click");
            console.log('Row clicked, looking for modal:', modalId);

            if (modalId) {
                const modal = document.querySelector(modalId);
                console.log('Modal element:', modal);

                if (modal) {
                    try {
                        const bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                        console.log('Modal shown successfully');
                    } catch (err) {
                        console.error('Error showing modal:', err);
                    }
                } else {
                    console.warn('Modal not found with selector:', modalId);
                    console.log('Available modals:', document.querySelectorAll('.modal'));
                }
            }
        });
    });
}


