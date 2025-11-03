/**
 * ========== DATA MANAGEMENT FOR TREATMENTS MODULE ==========
 * ไฟล์นี้จัดการการเก็บ เรียก และจัดเก็บข้อมูล
 *
 * ข้อมูลมี 2 ประเภท:
 * 1. Stored Data - ข้อมูลที่โหลดครั้งเดียวตั้งแต่เปิดหน้า (บันทึกอยู่ใน data attribute)
 * 2. Dynamic Data - ข้อมูลที่เปลี่ยนแปลงตลอดเวลา (อัปเดตผ่าน JavaScript)
 */

import { SELECTORS } from './constants.js';
import { debugLog, errorLog, isEmpty } from './utils.js';

/**
 * ========== STORED DATA RETRIEVAL ==========
 * ดึงข้อมูลที่เก็บไว้ในหน้า HTML ในรูปแบบ JSON
 *
 * วิธีการเก็บ:
 * <div id="treatmentsDataStore" data-treatments="{{ json_encode($treatments) }}"></div>
 */

/**
 * ดึงข้อมูลการรักษาทั้งหมดที่เก็บไว้ในหน้า
 *
 * @returns {Array} อาร์เรย์ของ treatment objects
 */
export function getAllTreatmentsFromStore() {
    try {
        const dataStore = document.getElementById(SELECTORS.dataStores.treatments.replace('#', ''));
        if (!dataStore) {
            console.warn('⚠️ Treatments data store not found');
            return [];
        }

        const jsonData = dataStore.getAttribute('data-treatments');
        if (!jsonData) {
            console.warn('⚠️ No treatments data in store');
            return [];
        }

        const treatments = JSON.parse(jsonData);
        debugLog('Loaded treatments from store', { count: treatments.length });
        return treatments;
    } catch (error) {
        errorLog('Failed to retrieve treatments from store', error);
        return [];
    }
}

/**
 * ค้นหา treatment เดียว จากข้อมูลที่เก็บไว้ด้วย treatment ID
 *
 * ตัวอย่าง:
 * const treatment = getTreatmentFromStore(5);
 *
 * @param {number|string} treatmentId - ID ของ treatment ที่ต้องการ
 * @returns {Object|null} treatment object หรือ null ถ้าไม่พบ
 */
export function getTreatmentFromStore(treatmentId) {
    try {
        const allTreatments = getAllTreatmentsFromStore();
        const treatment = allTreatments.find(t => t.id == treatmentId);

        if (!treatment) {
            console.warn(`⚠️ Treatment ${treatmentId} not found in store`);
            return null;
        }

        debugLog(`Found treatment ${treatmentId}`, treatment);
        return treatment;
    } catch (error) {
        errorLog(`Failed to get treatment ${treatmentId}`, error);
        return null;
    }
}

/**
 * ดึงข้อมูลรุ่น (batch) ทั้งหมดที่เก็บไว้
 *
 * @returns {Array} อาร์เรย์ของ batch objects
 */
export function getAllBatchesFromStore() {
    try {
        const dataStore = document.getElementById(SELECTORS.dataStores.batches.replace('#', ''));
        if (!dataStore) {
            console.warn('⚠️ Batches data store not found');
            return [];
        }

        const jsonData = dataStore.getAttribute('data-batches');
        const batches = jsonData ? JSON.parse(jsonData) : [];
        debugLog('Loaded batches from store', { count: batches.length });
        return batches;
    } catch (error) {
        errorLog('Failed to retrieve batches from store', error);
        return [];
    }
}

/**
 * ดึงรุ่นของฟาร์มหนึ่งๆจากข้อมูลที่เก็บไว้
 *
 * ตัวอย่าง:
 * const batches = getBatchesForFarmFromStore(3);
 *
 * @param {number|string} farmId - ID ของฟาร์ม
 * @returns {Array} อาร์เรย์ของ batch ที่เป็นของฟาร์มนี้
 */
export function getBatchesForFarmFromStore(farmId) {
    try {
        const allBatches = getAllBatchesFromStore();
        const farmBatches = allBatches.filter(b => b.farm_id == farmId);
        debugLog(`Found batches for farm ${farmId}`, { count: farmBatches.length });
        return farmBatches;
    } catch (error) {
        errorLog(`Failed to get batches for farm ${farmId}`, error);
        return [];
    }
}

/**
 * ดึงข้อมูลยา/วัคซีนทั้งหมดที่เก็บไว้
 *
 * @returns {Array} อาร์เรย์ของ medicine objects
 */
export function getAllMedicinesFromStore() {
    try {
        const dataStore = document.getElementById(SELECTORS.dataStores.medicines.replace('#', ''));
        if (!dataStore) {
            console.warn('⚠️ Medicines data store not found');
            return [];
        }

        const jsonData = dataStore.getAttribute('data-medicines');
        const medicines = jsonData ? JSON.parse(jsonData) : [];
        debugLog('Loaded medicines from store', { count: medicines.length });
        return medicines;
    } catch (error) {
        errorLog('Failed to retrieve medicines from store', error);
        return [];
    }
}

/**
 * ดึงยา/วัคซีนเดียวจากข้อมูลที่เก็บไว้ด้วย medicine ID
 *
 * ตัวอย่าง:
 * const medicine = getMedicineFromStore(5);
 *
 * @param {number|string} medicineId - ID ของ medicine
 * @returns {Object|null} medicine object หรือ null ถ้าไม่พบ
 */
export function getMedicineFromStore(medicineId) {
    try {
        const allMedicines = getAllMedicinesFromStore();
        const medicine = allMedicines.find(m => m.id == medicineId);

        if (!medicine) {
            debugLog(`Medicine ${medicineId} not found in store`);
            return null;
        }

        debugLog(`Found medicine ${medicineId}`, medicine);
        return medicine;
    } catch (error) {
        errorLog(`Failed to get medicine ${medicineId}`, error);
        return null;
    }
}

/**
 * ดึงยา/วัคซีนของสต็อก (storehouse) หนึ่งๆจากข้อมูลที่เก็บไว้
 *
 * @param {number|string} storehouseId - ID ของ storehouse
 * @returns {Array} อาร์เรย์ของ medicine ที่อยู่ใน storehouse นี้
 */
export function getMedicinesForStorehouseFromStore(storehouseId) {
    try {
        const allMedicines = getAllMedicinesFromStore();
        const storehouseMedicines = allMedicines.filter(m => m.storehouse_id == storehouseId);
        debugLog(`Found medicines for storehouse ${storehouseId}`, { count: storehouseMedicines.length });
        return storehouseMedicines;
    } catch (error) {
        errorLog(`Failed to get medicines for storehouse ${storehouseId}`, error);
        return [];
    }
}

/**
 * ========== FORM DATA MANAGEMENT ==========
 * จัดการข้อมูลในฟอร์ม (จาก input fields)
 */

/**
 * ดึงค่าทั้งหมดที่ผู้ใช้กรอกในฟอร์มการรักษา
 *
 * ส่งคืน object มีโครงสร้างเหมือนกับ treatment model ใน database
 *
 * @returns {Object} object ที่มีข้อมูลฟอร์มทั้งหมด
 */
export function getFormData() {
    try {
        const form = document.getElementById(SELECTORS.modal.formInModal.replace('#', ''));
        if (!form) {
            console.warn('⚠️ Form not found');
            return null;
        }

        // ดึงข้อมูลจากฟอร์ม
        const formData = new FormData(form);
        const data = {
            treatment_id: document.getElementById(SELECTORS.form.treatmentId.replace('#', '')).value || '',
            farm_id: document.getElementById(SELECTORS.dropdowns.farmInput.replace('#', '')).value || '',
            batch_id: document.getElementById(SELECTORS.dropdowns.batchInput.replace('#', '')).value || '',
            disease_name: document.getElementById(SELECTORS.form.diseaseInput.replace('#', '')).value || '',
            dosage: document.getElementById(SELECTORS.form.dosageInput.replace('#', '')).value || '',
            planned_start_date: document.getElementById(SELECTORS.form.startDateInput.replace('#', '')).value || '',
            planned_duration: document.getElementById(SELECTORS.form.durationInput.replace('#', '')).value || '',
            total_doses: document.getElementById(SELECTORS.form.totalDosesInput.replace('#', '')).value || '',
            treatment_level: document.querySelector(SELECTORS.dropdowns.treatmentLevelRadios + ':checked')?.value || '',
            status: document.querySelector(SELECTORS.dropdowns.statusRadios + ':checked')?.value || '',
            treatment_note: document.getElementById(SELECTORS.form.noteInput.replace('#', '')).value || '',
            attachment: document.getElementById(SELECTORS.form.attachmentInput.replace('#', '')).value || '',
            medicine_name: document.querySelector(SELECTORS.dropdowns.medicineName).value || '',
            medicine_code: document.querySelector(SELECTORS.dropdowns.medicineCode).value || '',
        };

        debugLog('Retrieved form data', data);
        return data;
    } catch (error) {
        errorLog('Failed to get form data', error);
        return null;
    }
}

/**
 * ========== FORM DATA SETTING ==========
 * ตั้งค่าข้อมูลในฟอร์ม (เขียนลงใน input fields)
 */

/**
 * เติมข้อมูล treatment เข้าไปในฟอร์ม (ใช้สำหรับ edit mode)
 *
 * @param {Object} treatment - treatment object ที่มีข้อมูลทั้งหมด
 */
export function populateFormWithTreatment(treatment) {
    try {
        if (!treatment) {
            console.warn('⚠️ No treatment data to populate');
            return;
        }

        // ตั้งค่า treatment ID (hidden field)
        const idInput = document.getElementById(SELECTORS.form.treatmentId.replace('#', ''));
        if (idInput) idInput.value = treatment.id || '';

        // ตั้งค่า disease
        const diseaseInput = document.getElementById(SELECTORS.form.diseaseInput.replace('#', ''));
        if (diseaseInput) diseaseInput.value = treatment.disease_name || '';

        // ตั้งค่า dosage
        const dosageInput = document.getElementById(SELECTORS.form.dosageInput.replace('#', ''));
        if (dosageInput) dosageInput.value = treatment.dosage || '';

        // ตั้งค่าวันที่เริ่ม
        const startDateInput = document.getElementById(SELECTORS.form.startDateInput.replace('#', ''));
        if (startDateInput) startDateInput.value = treatment.planned_start_date || '';

        // ตั้งค่าระยะเวลา
        const durationInput = document.getElementById(SELECTORS.form.durationInput.replace('#', ''));
        if (durationInput) durationInput.value = treatment.planned_duration || '';

        // ตั้งค่า total doses
        const totalDosesInput = document.getElementById(SELECTORS.form.totalDosesInput.replace('#', ''));
        if (totalDosesInput) totalDosesInput.value = treatment.total_doses || '';

        // ตั้งค่า treatment level radio
        const levelRadio = document.querySelector(`${SELECTORS.dropdowns.treatmentLevelRadios}[value="${treatment.treatment_level}"]`);
        if (levelRadio) levelRadio.checked = true;

        // ตั้งค่า status radio
        const statusRadio = document.querySelector(`${SELECTORS.dropdowns.statusRadios}[value="${treatment.status}"]`);
        if (statusRadio) statusRadio.checked = true;

        // ตั้งค่า note
        const noteInput = document.getElementById(SELECTORS.form.noteInput.replace('#', ''));
        if (noteInput) noteInput.value = treatment.treatment_note || '';

        // ตั้งค่า medicine
        const medicineName = document.querySelector(SELECTORS.dropdowns.medicineName);
        const medicineCode = document.querySelector(SELECTORS.dropdowns.medicineCode);
        if (medicineName) medicineName.value = treatment.medicine_name || '';
        if (medicineCode) medicineCode.value = treatment.medicine_code || '';

        debugLog('Populated form with treatment data', { treatmentId: treatment.id });
    } catch (error) {
        errorLog('Failed to populate form', error);
    }
}

/**
 * ========== VALIDATION ==========
 * ตรวจสอบความถูกต้องของข้อมูล
 */

/**
 * ตรวจสอบว่าฟอร์มมีข้อมูลครบถ้วนหรือไม่
 *
 * @returns {Object} { isValid: boolean, errors: Array }
 */
export function validateFormData() {
    const data = getFormData();
    const errors = [];

    if (isEmpty(data.farm_id)) errors.push('โปรดเลือกฟาร์ม');
    if (isEmpty(data.batch_id)) errors.push('โปรดเลือกรุ่น');
    if (isEmpty(data.disease_name)) errors.push('โปรดระบุชื่อโรค/อาการ');
    if (isEmpty(data.dosage)) errors.push('โปรดระบุขนาดยา');
    if (isEmpty(data.planned_start_date)) errors.push('โปรดเลือกวันที่เริ่มต้น');
    if (isEmpty(data.planned_duration)) errors.push('โปรดระบุระยะเวลา');
    if (isEmpty(data.treatment_level)) errors.push('โปรดเลือกระดับการรักษา');
    if (isEmpty(data.status)) errors.push('โปรดเลือกสถานะ');

    return {
        isValid: errors.length === 0,
        errors: errors
    };
}
