/**
 * ========== CONSTANTS FOR TREATMENTS MODULE ==========
 * ไฟล์นี้เก็บค่าคงที่ที่ใช้ในโมดูลการรักษา
 * เช่น selectors, class names, API endpoints เป็นต้น
 *
 * ประโยชน์:
 * - ถ้าต้องเปลี่ยน selector ก็เปลี่ยนที่นี่จุดเดียว
 * - ทำให้ code อื่นๆ เรียบร้อย ไม่ต้องเขียน selector ยาวๆซ้ำๆ
 */

// ========== DOM SELECTOR CONSTANTS ==========
// ใช้สำหรับเลือก element จากหน้า HTML

export const SELECTORS = {
    // Modal elements - องค์ประกอบของ modal
    modal: {
        modal: '#treatmentFormModal',
        formInModal: '#treatmentFormInModal',
    },

    // Form inputs - ช่องกรอกข้อมูลในฟอร์ม
    form: {
        treatmentId: '#treatmentId',
        diseaseInput: '#treatmentDiseaseName',
        dosageInput: '#dosage',
        startDateInput: '#planned_start_date',
        durationInput: '#planned_duration',
        totalDosesInput: '#total_doses',
        noteInput: '#treatment_note',
        attachmentInput: '#attachment',
    },

    // Dropdown elements - ปุ่มดรอปดาวน์เลือกข้อมูล
    dropdowns: {
        // Farm dropdown
        farmBtn: '#treatmentFarmDropdownBtn',
        farmLabel: '#treatmentFarmDropdownLabel',
        farmInput: '#treatmentFarmId',

        // Batch dropdown
        batchBtn: '#treatmentBatchDropdownBtn',
        batchLabel: '#treatmentBatchDropdownLabel',
        batchMenu: '#treatmentBatchDropdownMenu',
        batchInput: '#treatmentBatchId',

        // Medicine dropdown
        medicineBtn: '.treatment-medicine-dropdown-btn',
        medicineMenu: '.treatment-medicine-dropdown-menu',
        medicineName: '.treatment-medicine-name',
        medicineCode: '.treatment-medicine-code',

        // Frequency dropdown
        frequencyBtn: '.treatment-frequency-btn',

        // Treatment level radio buttons
        treatmentLevelRadios: 'input[name="treatment_level"]',
        statusRadios: 'input[name="status"]',
    },

    // Table elements - ตารางข้อมูล
    tables: {
        penTableBody: '#treatmentPenTableBody',
    },

    // Data stores - ตำแหน่งเก็บข้อมูล JSON
    dataStores: {
        treatments: '#treatmentsDataStore',
        batches: '#batchesDataStore',
        medicines: '#medicinesDataStore',
    },

    // Loading indicator - ตัวบ่งชี้กำลังโหลด
    loading: '#treatmentFormLoadingIndicator',
};

// ========== API ENDPOINTS ==========
// ใช้เมื่อต้องเรียก API จากเบื้องหลัง

export const API = {
    // Farm related
    farmBatches: (farmId) => `/api/farms/${farmId}/batches`,
    medicines: '/api/medicines',

    // Treatment related
    getTreatment: (treatmentId) => `/treatments/${treatmentId}`,
    getTreatmentData: (treatmentId) => `/api/treatments/${treatmentId}`,
    conversionCalculator: '/api/treatments/conversion-calculator',
};

// ========== TEXT CONSTANTS ==========
// ข้อความที่ใช้แสดงให้ผู้ใช้เห็น

export const TEXTS = {
    // Placeholders - ข้อความเสมือน
    selectFarm: '-- เลือกฟาร์ม --',
    selectBatch: '-- เลือกรุ่น --',
    selectMedicine: 'เลือกยา/วัคซีน',
    selectFarmFirst: '-- เลือกฟาร์มก่อน --',

    // Status messages - ข้อความสถานะ
    loading: 'กำลังโหลด...',
    noData: 'ไม่มีข้อมูล',
    error: 'เกิดข้อผิดพลาด',

    // Success/Error messages
    savedSuccess: 'บันทึกข้อมูลสำเร็จ',
    loadError: 'โหลดข้อมูลไม่สำเร็จ',
};

// ========== CSS CLASSES ==========
// ชื่อ class ที่ใช้สำหรับ styling

export const CLASSES = {
    active: 'active',
    disabled: 'disabled',
    loading: 'loading',
    hidden: 'd-none',
};

// ========== OTHER CONSTANTS ==========
export const DEFAULTS = {
    // ค่าเริ่มต้นเวลาเปิด modal
    treatmentLevel: 'pen',
    status: 'pending',
};
