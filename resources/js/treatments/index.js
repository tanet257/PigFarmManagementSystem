/**
 * ========== MAIN INIT FILE FOR TREATMENTS MODULE ==========
 * ไฟล์นี้เป็นจุดเริ่มต้นหลักของระบบ
 *
 * เรียกใช้โค้ดจากไฟล์อื่นๆและเซต event listeners
 *
 * วิธีใช้:
 * 1. Import ไฟล์นี้ในหน้า blade
 * 2. Treatments module จะ auto-initialize เมื่อหน้าโหลดเสร็จ
 */

import { SELECTORS, TEXTS } from './constants.js';
import {
    getAllBatchesFromStore,
    getBatchesForFarmFromStore,
    getAllMedicinesFromStore,
    getMedicinesForStorehouseFromStore,
    getMedicineFromStore,
    getTreatmentFromStore,
    getFormData,
    populateFormWithTreatment,
    validateFormData
} from './dataManager.js';
import {
    showLoading,
    hideLoading,
    updateFarmDropdown,
    updateBatchDropdown,
    updateMedicineDropdown,
    disableFormSections,
    enableFormSections,
    populateBatchDropdown,
    populateMedicineDropdown,
    resetForm,
    clearPenTable,
    populatePenTable
} from './uiManager.js';
import {
    formatDisplayDate,
    getStatusText,
    getStatusColor,
    getFrequencyLabel,
    showSnackbar,
    debugLog,
    errorLog,
    calculateStockReduction,
    convertMLToDisplayText
} from './utils.js';

/**
 * ========== INITIALIZATION ==========
 * เรียกเมื่อหน้า HTML โหลดเสร็จสิ้น
 */

// ตรวจสอบว่า Bootstrap Modal อยู่ในหน้าหรือไม่
if (typeof bootstrap === 'undefined') {
    console.error('❌ Bootstrap not found. Please include Bootstrap JS before this script.');
}

/**
 * Event: Farm Dropdown Change
 * เมื่อผู้ใช้เลือกฟาร์ม
 *
 * งาน:
 * 1. ดึงรุ่นของฟาร์มนี้จาก store
 * 2. เต็มรุ่นเข้า dropdown
 * 3. อัปเดต farm input hidden field
 */
export function setupFarmDropdownListener() {
    try {
        // ค้นหาปุ่ม farm dropdown
        const farmBtn = document.getElementById(SELECTORS.dropdowns.farmBtn.replace('#', ''));
        if (!farmBtn) {
            console.warn('⚠️ Farm dropdown button not found');
            return;
        }

        // ตรวจสอบเมื่อคลิกที่ item ใน dropdown menu
        farmBtn.addEventListener('click', function() {
            debugLog('Farm dropdown opened');
        });

        // ฟังเหตุการณ์ของ dropdown menu items (ใช้ event delegation)
        document.addEventListener('click', function(e) {
            // ตรวจสอบว่า click มาจาก farm dropdown menu items หรือไม่
            if (e.target.closest('#farmDropdownMenu .dropdown-item')) {
                const farmItem = e.target.closest('a');
                const farmId = farmItem.getAttribute('data-farm-id');
                const farmName = farmItem.textContent.trim();

                debugLog('Farm selected', { farmId, farmName });

                // 1. อัปเดต dropdown button
                updateFarmDropdown(farmName);

                // 2. เก็บ farm ID ไว้ใน input
                const farmInput = document.getElementById(SELECTORS.dropdowns.farmInput.replace('#', ''));
                if (farmInput) farmInput.value = farmId;

                // 3. ดึงรุ่นของฟาร์มนี้
                const batches = getBatchesForFarmFromStore(farmId);

                // 4. เต็มรุ่นเข้า dropdown
                populateBatchDropdown(batches);

                // 5. เปิดใจปุ่ม batch dropdown
                const batchBtn = document.getElementById(SELECTORS.dropdowns.batchBtn.replace('#', ''));
                if (batchBtn && batches.length > 0) {
                    batchBtn.disabled = false;
                }
            }
        });
    } catch (error) {
        errorLog('Failed to setup farm dropdown listener', error);
    }
}

/**
 * Event: Batch Dropdown Change
 * เมื่อผู้ใช้เลือกรุ่น
 */
export function setupBatchDropdownListener() {
    try {
        document.addEventListener('click', function(e) {
            if (e.target.closest('#treatmentBatchDropdownMenu .dropdown-item')) {
                const batchItem = e.target.closest('a');
                const batchId = batchItem.getAttribute('data-batch-id');
                const batchName = batchItem.textContent.trim();

                debugLog('Batch selected', { batchId, batchName });

                // อัปเดต batch dropdown button
                updateBatchDropdown(batchName);

                // เก็บ batch ID
                const batchInput = document.getElementById(SELECTORS.dropdowns.batchInput.replace('#', ''));
                if (batchInput) batchInput.value = batchId;

                // โหลด pen table ถ้าทั้ง farm และ batch เลือกแล้ว
                const farmId = document.getElementById(SELECTORS.dropdowns.farmInput.replace('#', '')).value;
                const level = document.querySelector(SELECTORS.dropdowns.treatmentLevelRadios + ':checked')?.value || 'pen';

                if (farmId && batchId) {
                    // loadPenTable(farmId, batchId, level); // ต้องสร้างฟังก์ชัน loadPenTable
                }
            }
        });
    } catch (error) {
        errorLog('Failed to setup batch dropdown listener', error);
    }
}

/**
 * Event: Medicine Dropdown Change
 * เมื่อผู้ใช้เลือกยา/วัคซีน
 */
export function setupMedicineDropdownListener() {
    try {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.treatment-medicine-dropdown-menu .dropdown-item')) {
                const medicineItem = e.target.closest('a');
                const medicineId = medicineItem.getAttribute('data-medicine-id');
                const medicineCode = medicineItem.getAttribute('data-medicine-code');
                const medicineName = medicineItem.getAttribute('data-medicine-name');

                debugLog('Medicine selected', { medicineId, medicineCode, medicineName });

                // อัปเดต dropdown button
                updateMedicineDropdown(medicineName);

                // เก็บข้อมูลยา
                const nameInput = document.querySelector(SELECTORS.dropdowns.medicineName);
                const codeInput = document.querySelector(SELECTORS.dropdowns.medicineCode);

                if (nameInput) nameInput.value = medicineName;
                if (codeInput) codeInput.value = medicineCode;

                // ✅ คำนวณการลดสต็อกเมื่อผู้ใช้เลือกยาและกรอกปริมาณ
                calculateMedicineConversion();
            }
        });
    } catch (error) {
        errorLog('Failed to setup medicine dropdown listener', error);
    }
}

/**
 * คำนวณการแปลงหน่วยยา (ml → bottles/units) และแสดง UI
 *
 * เมื่อเรียกฟังก์ชันนี้:
 * 1. ดึงยาที่เลือก
 * 2. ดึงปริมาณ (ml) ที่กรอก
 * 3. คำนวณจำนวนหน่วยสินค้า
 * 4. แสดงผลการคำนวณใหม่
 */
export function calculateMedicineConversion() {
    try {
        // 1. ดึงข้อมูล
        const medicineCode = document.querySelector(SELECTORS.dropdowns.medicineCode)?.value || '';
        const dosageInput = document.getElementById('dosage');
        const dosage = dosageInput ? parseFloat(dosageInput.value) || 0 : 0;
        const displayElement = document.getElementById('dosageCalculationDisplay');

        if (!displayElement) return;

        // ถ้าไม่ได้เลือกยา ให้แสดงข้อความ
        if (!medicineCode) {
            displayElement.innerHTML = '❌ โปรดเลือกยาก่อน';
            displayElement.className = 'd-block mt-2 text-muted';
            return;
        }

        // ถ้าไม่ได้กรอกปริมาณ ให้แสดงข้อความ
        if (dosage <= 0) {
            displayElement.innerHTML = '⚠️ โปรดระบุปริมาณยา (ml)';
            displayElement.className = 'd-block mt-2 text-muted';
            return;
        }

        // 2. ดึงข้อมูลยาจาก store
        const allMedicines = getAllMedicinesFromStore();
        const medicine = allMedicines.find(m => m.code === medicineCode || m.medicine_code === medicineCode);

        if (!medicine) {
            displayElement.innerHTML = '❌ ไม่พบข้อมูลยา';
            displayElement.className = 'd-block mt-2 text-danger';
            debugLog('Medicine not found', { medicineCode, allMedicines });
            return;
        }

        // 3. คำนวณจำนวนหน่วยสินค้า
        const stockReduction = calculateStockReduction(dosage, medicine);
        const displayText = convertMLToDisplayText(dosage, medicine);

        // 4. แสดงผลการคำนวณ
        const baseUnit = medicine.base_unit || 'ml';
        const unit = medicine.unit || 'หน่วย';
        const quantityPerUnit = medicine.quantity_per_unit || 1;

        displayElement.innerHTML = `
            ✅ ${displayText}
            <br><small class="text-success"><strong>จะหักจากสต็อก: ${stockReduction} ${unit}</strong></small>
        `;
        displayElement.className = 'd-block mt-2';

        debugLog('Medicine conversion calculated', {
            medicine: medicine.name || medicine.item_name,
            dosage,
            stockReduction,
            unit
        });

    } catch (error) {
        errorLog('Failed to calculate medicine conversion', error);
    }
}

/**
 * Event: Treatment Level Change (Barn vs Pen)
 * เมื่อผู้ใช้เลือก radio button treatment level
 *
 * งาน:
 * - โหลด pen/barn table ตามที่เลือก
 */
export function setupTreatmentLevelListener() {
    try {
        const radios = document.querySelectorAll(SELECTORS.dropdowns.treatmentLevelRadios);
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                const level = this.value;
                debugLog('Treatment level changed', { level });

                const farmId = document.getElementById(SELECTORS.dropdowns.farmInput.replace('#', '')).value;
                const batchId = document.getElementById(SELECTORS.dropdowns.batchInput.replace('#', '')).value;

                if (farmId && batchId) {
                    // loadPenTable(farmId, batchId, level);
                }
            });
        });
    } catch (error) {
        errorLog('Failed to setup treatment level listener', error);
    }
}

/**
 * Event: Edit Treatment Button
 * เมื่อผู้ใช้กดปุ่ม Edit ในตาราง
 *
 * งาน:
 * 1. โหลดข้อมูล treatment จาก store
 * 2. เติมข้อมูลเข้าฟอร์ม
 * 3. เปิด modal
 */
export function setupEditTreatmentListener() {
    try {
        document.addEventListener('click', function(e) {
            const editBtn = e.target.closest('.btn-edit-treatment');
            if (!editBtn) return;

            const treatmentId = editBtn.getAttribute('data-treatment-id');
            debugLog('Edit button clicked', { treatmentId });

            // 1. แสดง loading
            showLoading();

            // 2. ดึงข้อมูล treatment จาก store
            const treatment = getTreatmentFromStore(treatmentId);

            if (!treatment) {
                errorLog('Treatment not found', { treatmentId });
                showSnackbar('ไม่พบข้อมูลการรักษา', 'error');
                hideLoading();
                return;
            }

            // 3. รีเซ็ตฟอร์ม
            resetForm();

            // 4. เติมข้อมูล
            populateFormWithTreatment(treatment);

            // 5. ซ่อน loading
            hideLoading();

            // 6. เปิด modal (ใช้ showTreatmentFormModal ถ้ามีอยู่)
            // หรือ new bootstrap.Modal(document.getElementById('treatmentFormModal')).show();
        });
    } catch (error) {
        errorLog('Failed to setup edit treatment listener', error);
    }
}

/**
 * Event: Create Treatment Button
 * เมื่อผู้ใช้กดปุ่มสร้างการรักษาใหม่
 */
export function setupCreateTreatmentListener() {
    try {
        const createBtn = document.getElementById('createTreatmentBtn');
        if (!createBtn) {
            console.warn('⚠️ Create button not found');
            return;
        }

        createBtn.addEventListener('click', function() {
            debugLog('Create button clicked');

            // รีเซ็ตฟอร์ม
            resetForm();

            // เปิด modal
            const modal = new bootstrap.Modal(document.getElementById(SELECTORS.modal.modal.replace('#', '')));
            modal.show();
        });
    } catch (error) {
        errorLog('Failed to setup create button listener', error);
    }
}

/**
 * Event: Form Submission
 * เมื่อผู้ใช้กด Save ในฟอร์ม
 */
export function setupFormSubmission() {
    try {
        const form = document.getElementById(SELECTORS.modal.formInModal.replace('#', ''));
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            debugLog('Form submitted');

            // 1. ตรวจสอบ validation
            const validation = validateFormData();
            if (!validation.isValid) {
                validation.errors.forEach(error => {
                    showSnackbar(error, 'warning');
                });
                return;
            }

            // 2. ดึงข้อมูล
            const data = getFormData();
            debugLog('Form data ready to submit', data);

            // 3. ส่งไป server (ใช้ form.submit() หรือ fetch)
            // form.submit();
        });

        // ✅ เพิ่ม event listener สำหรับ dosage input
        // เมื่อผู้ใช้เปลี่ยนปริมาณ ให้คำนวณใหม่
        const dosageInput = document.getElementById('dosage');
        if (dosageInput) {
            dosageInput.addEventListener('change', calculateMedicineConversion);
            dosageInput.addEventListener('input', calculateMedicineConversion);
        }
    } catch (error) {
        errorLog('Failed to setup form submission', error);
    }
}

/**
 * ========== AUTO-INITIALIZE ==========
 * เรียกเมื่อ DOM โหลดเสร็จ
 */

export function initTreatmentsModule() {
    try {
        debugLog('Initializing treatments module...');

        // เซต event listeners ทั้งหมด
        setupFarmDropdownListener();
        setupBatchDropdownListener();
        setupMedicineDropdownListener();
        setupTreatmentLevelListener();
        setupEditTreatmentListener();
        setupCreateTreatmentListener();
        setupFormSubmission();

        debugLog('✅ Treatments module initialized successfully');
    } catch (error) {
        errorLog('Failed to initialize treatments module', error);
    }
}

// Auto-initialize เมื่อ DOM พร้อม
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTreatmentsModule);
} else {
    // ถ้า DOM พร้อมแล้ว เรียกใช้ทันที
    initTreatmentsModule();
}
