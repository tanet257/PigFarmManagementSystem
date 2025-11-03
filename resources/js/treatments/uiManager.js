/**
 * ========== UI MANAGEMENT FOR TREATMENTS MODULE ==========
 * ไฟล์นี้จัดการส่วนแสดงผล UI ต่างๆ
 * เช่น อัปเดต dropdown, แสดง/ซ่อน loading, เปิด/ปิด modal เป็นต้น
 */

import { SELECTORS, TEXTS } from './constants.js';
import { debugLog, errorLog, safeGetById } from './utils.js';

/**
 * ========== MODAL MANAGEMENT ==========
 * จัดการการเปิด ปิด และรีเซ็ต modal
 */

/**
 * เปิด modal สำหรับสร้าง treatment ใหม่
 */
export function openCreateModal() {
    try {
        const modal = document.getElementById(SELECTORS.modal.modal.replace('#', ''));
        if (!modal) {
            console.warn('⚠️ Modal not found');
            return;
        }

        // ใช้ Bootstrap Modal API
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        debugLog('Opened create modal');
    } catch (error) {
        errorLog('Failed to open modal', error);
    }
}

/**
 * ปิด modal
 */
export function closeModal() {
    try {
        const modal = document.getElementById(SELECTORS.modal.modal.replace('#', ''));
        if (!modal) return;

        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }

        debugLog('Closed modal');
    } catch (error) {
        errorLog('Failed to close modal', error);
    }
}

/**
 * ========== LOADING INDICATOR ==========
 * แสดง/ซ่อน loading spinner เวลาโหลดข้อมูล
 */

/**
 * แสดง loading indicator
 */
export function showLoading() {
    try {
        const loader = safeGetById(SELECTORS.loading.replace('#', ''));
        if (loader) {
            loader.classList.remove('d-none');
            debugLog('Showing loading indicator');
        }
    } catch (error) {
        errorLog('Failed to show loading', error);
    }
}

/**
 * ซ่อน loading indicator
 */
export function hideLoading() {
    try {
        const loader = safeGetById(SELECTORS.loading.replace('#', ''));
        if (loader) {
            loader.classList.add('d-none');
            debugLog('Hiding loading indicator');
        }
    } catch (error) {
        errorLog('Failed to hide loading', error);
    }
}

/**
 * ========== DROPDOWN UPDATES ==========
 * อัปเดต dropdown button text เมื่อผู้ใช้เลือก
 */

/**
 * อัปเดต farm dropdown button
 *
 * @param {string} farmName - ชื่อฟาร์ม
 */
export function updateFarmDropdown(farmName) {
    try {
        const btn = safeGetById(SELECTORS.dropdowns.farmBtn.replace('#', ''));
        if (btn) {
            btn.textContent = farmName || TEXTS.selectFarm;
            debugLog('Updated farm dropdown', { farmName });
        }
    } catch (error) {
        errorLog('Failed to update farm dropdown', error);
    }
}

/**
 * อัปเดต batch dropdown button
 *
 * @param {string} batchName - ชื่อรุ่น
 */
export function updateBatchDropdown(batchName) {
    try {
        const btn = safeGetById(SELECTORS.dropdowns.batchBtn.replace('#', ''));
        if (btn) {
            // Batch button มี structure: <span><i></i><span>text</span></span>
            btn.innerHTML = `<span><i class="bi bi-diagram-3 me-2"></i><span id="${SELECTORS.dropdowns.batchLabel.replace('#', '')}">${batchName || TEXTS.selectBatch}</span></span>`;
            debugLog('Updated batch dropdown', { batchName });
        }
    } catch (error) {
        errorLog('Failed to update batch dropdown', error);
    }
}

/**
 * อัปเดต medicine dropdown button
 *
 * @param {string} medicineName - ชื่อยา/วัคซีน
 */
export function updateMedicineDropdown(medicineName) {
    try {
        const btn = document.querySelector(SELECTORS.dropdowns.medicineBtn);
        if (btn) {
            btn.textContent = medicineName || TEXTS.selectMedicine;
            debugLog('Updated medicine dropdown', { medicineName });
        }
    } catch (error) {
        errorLog('Failed to update medicine dropdown', error);
    }
}

/**
 * ========== FORM SECTIONS ENABLING/DISABLING ==========
 * เปิดใจ/ปิดใจ form section ตามเหตุการณ์
 *
 * เช่น ถ้ายังไม่เลือกฟาร์ม ไม่ให้กดปุ่มเลือกรุ่น
 */

/**
 * ปิดใจ form section บางส่วน (disable)
 *
 * @param {Array} fieldIds - array ของ element IDs ที่ต้องปิด
 */
export function disableFormSections(fieldIds) {
    try {
        fieldIds.forEach(fieldId => {
            const field = safeGetById(fieldId);
            if (field) {
                field.disabled = true;
                field.style.opacity = '0.5';
                field.style.pointerEvents = 'none';
            }
        });
        debugLog('Disabled form sections', { count: fieldIds.length });
    } catch (error) {
        errorLog('Failed to disable form sections', error);
    }
}

/**
 * เปิดใจ form section บางส่วน (enable)
 *
 * @param {Array} fieldIds - array ของ element IDs ที่ต้องเปิด
 */
export function enableFormSections(fieldIds) {
    try {
        fieldIds.forEach(fieldId => {
            const field = safeGetById(fieldId);
            if (field) {
                field.disabled = false;
                field.style.opacity = '1';
                field.style.pointerEvents = 'auto';
            }
        });
        debugLog('Enabled form sections', { count: fieldIds.length });
    } catch (error) {
        errorLog('Failed to enable form sections', error);
    }
}

/**
 * ========== DROPDOWN MENU POPULATION ==========
 * เติมรายการลงในดรอปดาวน์เมนู
 */

/**
 * เต็มรุ่นเข้าดรอปดาวน์ batch
 *
 * @param {Array} batches - array ของ batch objects
 */
export function populateBatchDropdown(batches) {
    try {
        const menu = safeGetById(SELECTORS.dropdowns.batchMenu.replace('#', ''));
        if (!menu) return;

        menu.innerHTML = '';

        if (!batches || batches.length === 0) {
            menu.innerHTML = '<li><a class="dropdown-item disabled">ไม่มีรุ่นในฟาร์มนี้</a></li>';
            return;
        }

        batches.forEach(batch => {
            const li = document.createElement('li');
            const link = document.createElement('a');
            link.className = 'dropdown-item';
            link.href = '#';
            link.setAttribute('data-batch-id', batch.id);
            link.setAttribute('data-batch-code', batch.batch_code);
            link.textContent = batch.batch_code || `รุ่นที่ ${batch.id}`;

            li.appendChild(link);
            menu.appendChild(li);
        });

        debugLog('Populated batch dropdown', { count: batches.length });
    } catch (error) {
        errorLog('Failed to populate batch dropdown', error);
    }
}

/**
 * เต็มยา/วัคซีนเข้าดรอปดาวน์ medicine
 *
 * @param {Array} medicines - array ของ medicine objects
 */
export function populateMedicineDropdown(medicines) {
    try {
        const menu = document.querySelector(SELECTORS.dropdowns.medicineMenu);
        if (!menu) return;

        menu.innerHTML = '';

        if (!medicines || medicines.length === 0) {
            menu.innerHTML = '<li><a class="dropdown-item disabled">ไม่มียาในสต็อกนี้</a></li>';
            return;
        }

        medicines.forEach(medicine => {
            const li = document.createElement('li');
            const link = document.createElement('a');
            link.className = 'dropdown-item';
            link.href = '#';
            link.setAttribute('data-medicine-id', medicine.id);
            link.setAttribute('data-medicine-code', medicine.medicine_code);
            link.setAttribute('data-medicine-name', medicine.medicine_name);
            link.textContent = `${medicine.medicine_name} (${medicine.medicine_code})`;

            li.appendChild(link);
            menu.appendChild(li);
        });

        debugLog('Populated medicine dropdown', { count: medicines.length });
    } catch (error) {
        errorLog('Failed to populate medicine dropdown', error);
    }
}

/**
 * ========== FORM RESET ==========
 * เคลียร์ฟอร์มให้เหมือนใหม่
 */

/**
 * รีเซ็ตฟอร์มให้ว่างหมด
 */
export function resetForm() {
    try {
        const form = document.getElementById(SELECTORS.modal.formInModal.replace('#', ''));
        if (form) {
            form.reset();
        }

        // รีเซ็ต dropdown labels
        const farmLabel = safeGetById(SELECTORS.dropdowns.farmLabel.replace('#', ''));
        if (farmLabel) farmLabel.textContent = TEXTS.selectFarm;

        const batchLabel = safeGetById(SELECTORS.dropdowns.batchLabel.replace('#', ''));
        if (batchLabel) batchLabel.textContent = TEXTS.selectBatch;

        // รีเซ็ต hidden inputs
        const farmInput = safeGetById(SELECTORS.dropdowns.farmInput.replace('#', ''));
        if (farmInput) farmInput.value = '';

        const batchInput = safeGetById(SELECTORS.dropdowns.batchInput.replace('#', ''));
        if (batchInput) batchInput.value = '';

        debugLog('Reset form');
    } catch (error) {
        errorLog('Failed to reset form', error);
    }
}

/**
 * ========== TABLE UPDATES ==========
 * อัปเดตตารางแสดงข้อมูล
 */

/**
 * เคลียร์ตารางปากการต
 */
export function clearPenTable() {
    try {
        const tbody = safeGetById(SELECTORS.tables.penTableBody.replace('#', ''));
        if (tbody) {
            tbody.innerHTML = '';
            debugLog('Cleared pen table');
        }
    } catch (error) {
        errorLog('Failed to clear pen table', error);
    }
}

/**
 * เพิ่มแถว pen ลงตารางโดยการอัปเดต checkbox
 *
 * @param {Array} pens - array ของ pen objects
 */
export function populatePenTable(pens) {
    try {
        clearPenTable();
        const tbody = safeGetById(SELECTORS.tables.penTableBody.replace('#', ''));
        if (!tbody) return;

        if (!pens || pens.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">ไม่มีปากการต</td></tr>';
            return;
        }

        pens.forEach(pen => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input type="checkbox" class="form-check-input pen-checkbox" value="${pen.id}" data-pen-name="${pen.pen_code}">
                </td>
                <td>${pen.pen_code}</td>
                <td>${pen.capacity || '-'}</td>
                <td>${pen.current_livestock_count || 0}</td>
            `;
            tbody.appendChild(tr);
        });

        debugLog('Populated pen table', { count: pens.length });
    } catch (error) {
        errorLog('Failed to populate pen table', error);
    }
}
