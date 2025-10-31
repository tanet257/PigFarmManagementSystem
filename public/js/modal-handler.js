// modal-handler.js
window.handleModal = {
    openModal(modalId) {
        console.log("Opening modal:", modalId);

        const modalEl = document.getElementById(modalId);
        if (!modalEl) {
            console.error("Modal not found:", modalId);
            return;
        }

        // ใช้ Bootstrap ควบคุมทั้งหมด
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        setTimeout(() => {
            const backdrop = document.querySelector(".modal-backdrop");
            console.log("Modal State:", modalId);
            console.log("- Backdrop exists:", !!backdrop);
            console.log("- Body classes:", document.body.className);
        }, 100);
    }
};
