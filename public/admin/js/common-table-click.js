// clickable-row.js
document.addEventListener("DOMContentLoaded", function () {
    setupClickableRows();
});

function setupClickableRows() {
    const rows = document.querySelectorAll(".clickable-row");
    console.log("Found clickable rows:", rows.length);

    rows.forEach(row => {
        row.style.cursor = "pointer";

        row.addEventListener("click", function (e) {
            const ignoreTags = ["BUTTON", "A", "INPUT", "I"];
            if (ignoreTags.includes(e.target.tagName)) {
                console.log("Ignore click on:", e.target.tagName);
                return;
            }

            const modalSelector = row.getAttribute("data-row-click");
            console.log("Row clicked:", modalSelector);

            if (!modalSelector) return;

            const modalEl = document.querySelector(modalSelector);
            if (!modalEl) {
                console.warn("Modal not found for selector:", modalSelector);
                return;
            }

            window.handleModal.openModal(modalEl.id);
        });
    });
}
