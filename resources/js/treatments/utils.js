/**
 * ========== UTILITY FUNCTIONS FOR TREATMENTS MODULE ==========
 * ไฟล์นี้เก็บฟังก์ชันช่วยเหลือต่างๆ ที่ใช้บ่อยๆในระบบการรักษา
 * เช่น format วันที่, แปลงสถานะ, บ่อนวอ แปลงหน่วย เป็นต้น
 *
 * ประโยชน์:
 * - หลีกเลี่ยงการเขียน code ซ้ำๆ (DRY principle)
 * - เปลี่ยนแปลงได้ง่ายถ้ารูปแบบข้อมูลเปลี่ยน
 */

import { TEXTS } from './constants.js';

/**
 * Format วันที่จากรูปแบบ YYYY-MM-DD เป็น DD/MM/YYYY
 *
 * ตัวอย่าง:
 * - Input: "2025-11-02"
 * - Output: "02/11/2025"
 *
 * @param {string} dateStr - วันที่ในรูปแบบ YYYY-MM-DD
 * @returns {string} วันที่ในรูปแบบ DD/MM/YYYY
 */
export function formatDisplayDate(dateStr) {
    if (!dateStr) return '';
    try {
        const [year, month, day] = dateStr.split('-');
        return `${day}/${month}/${year}`;
    } catch (error) {
        console.error('❌ Error formatting date:', error);
        return dateStr;
    }
}

/**
 * แปลงสถานะการรักษาเป็นข้อความที่อ่านเข้าใจ
 *
 * ตัวอย่าง:
 * - Input: "pending" → Output: "รอดำเนินการ"
 * - Input: "completed" → Output: "เสร็จสิ้น"
 * - Input: "cancelled" → Output: "ยกเลิก"
 *
 * @param {string} status - สถานะ (pending, completed, cancelled เป็นต้น)
 * @returns {string} ข้อความสถานะภาษาไทย
 */
export function getStatusText(status) {
    const statusMap = {
        'pending': 'รอดำเนินการ',
        'completed': 'เสร็จสิ้น',
        'cancelled': 'ยกเลิก',
        'on_going': 'กำลังดำเนินการ',
    };
    return statusMap[status] || status;
}

/**
 * ส่งคืนสีสำหรับแสดงสถานะต่างๆ
 * ใช้สำหรับให้ UI มีสีตรงกับสถานะ เพื่อให้ผู้ใช้เข้าใจง่ายขึ้น
 *
 * ตัวอย่าง:
 * - pending → badge bg-warning (สีเหลือง - ยังไม่ทำ)
 * - completed → badge bg-success (สีเขียว - เสร็จแล้ว)
 * - cancelled → badge bg-danger (สีแดง - ยกเลิก)
 *
 * @param {string} status - สถานะ
 * @returns {string} ชื่อ Bootstrap class สำหรับสี
 */
export function getStatusColor(status) {
    const colorMap = {
        'pending': 'bg-warning',
        'completed': 'bg-success',
        'cancelled': 'bg-danger',
        'on_going': 'bg-info',
    };
    return colorMap[status] || 'bg-secondary';
}

/**
 * แปลงความถี่การใช้ยาเป็นข้อความที่อ่านเข้าใจ
 *
 * ตัวอย่าง:
 * - Input: "daily" → Output: "วันละครั้ง"
 * - Input: "weekly" → Output: "สัปดาห์ละครั้ง"
 * - Input: "once" → Output: "ครั้งเดียว"
 *
 * @param {string} frequency - ความถี่ (once, daily, twice_daily, every_other_day, weekly เป็นต้น)
 * @returns {string} ข้อความความถี่ภาษาไทย
 */
export function getFrequencyLabel(frequency) {
    const frequencyMap = {
        'once': 'ครั้งเดียว',
        'daily': 'วันละครั้ง',
        'twice_daily': 'วันละ 2 ครั้ง',
        'three_times_daily': 'วันละ 3 ครั้ง',
        'every_other_day': 'วันเว้นวัน',
        'weekly': 'สัปดาห์ละครั้ง',
        'biweekly': '2 สัปดาห์ละครั้ง',
        'monthly': 'เดือนละครั้ง',
        'as_needed': 'ตามความจำเป็น',
    };
    return frequencyMap[frequency] || frequency || '-';
}

/**
 * แปลงสถานะการรักษาเป็นข้อความที่อ่านเข้าใจ
 *
 * ตัวอย่าง:
 * - Input: "pending" → Output: "รอดำเนินการ"
 * - Input: "completed" → Output: "เสร็จสิ้น"
 * - Input: "stopped" → Output: "หยุดการรักษา"
 *
 * @param {string} status - สถานะ (pending, ongoing, completed, stopped เป็นต้น)
 * @returns {string} ข้อความสถานะภาษาไทย
 */
export function getStatusLabel(status) {
    const statusMap = {
        'pending': 'รอดำเนินการ',
        'ongoing': 'กำลังดำเนินการ',
        'completed': 'เสร็จสิ้น',
        'stopped': 'หยุดการรักษา',
    };
    return statusMap[status] || status || '-';
}

/**
 * ปรับปรุงค่าตัวเลขให้ตรงกับหน่วยสต็อก (Ceiling - ปัดขึ้น)
 *
 * เหตุผล:
 * - ถ้าใช้ยา 0.2 ขวด ไม่สามารถใช้เศษ ต้องใช้ขวดเต็มหนึ่ง
 * - ดังนั้นต้องปัดขึ้นเสมอเพื่อให้หน่วยสต็อกลดลงถูกต้อง
 *
 * ตัวอย่าง:
 * - Input: 0.2 → Output: 1
 * - Input: 1.5 → Output: 2
 * - Input: 2.0 → Output: 2
 *
 * @param {number} value - ค่าตัวเลข
 * @returns {number} ค่าที่ปัดขึ้น
 */
export function ceilValue(value) {
    return Math.ceil(value);
}

/**
 * ตรวจสอบว่า element มีอยู่ใน DOM หรือไม่
 *
 * ประโยชน์:
 * - หลีกเลี่ยงข้อผิดพลาด "Cannot read property 'xxx' of null"
 * - ทำให้ code ปลอดภัยกว่า
 *
 * @param {string} id - ID ของ element
 * @returns {Element|null} element หรือ null ถ้าไม่พบ
 */
export function safeGetById(id) {
    try {
        return document.getElementById(id) || null;
    } catch (error) {
        console.error(`❌ Error getting element with ID: ${id}`, error);
        return null;
    }
}

/**
 * ตรวจสอบว่า object มี property ที่กำหนดหรือไม่
 *
 * ตัวอย่าง:
 * - hasProperty({name: "John"}, "name") → true
 * - hasProperty({name: "John"}, "age") → false
 *
 * @param {Object} obj - Object ที่ต้องตรวจสอบ
 * @param {string} prop - ชื่อ property
 * @returns {boolean} true ถ้ามี property นี้, false ถ้าไม่มี
 */
export function hasProperty(obj, prop) {
    return obj && prop in obj;
}

/**
 * แสดง snackbar notification สั้นๆที่ด้านล่างหน้าจอ
 *
 * ตัวอย่าง:
 * showSnackbar("บันทึกข้อมูลสำเร็จ!", "success");
 * showSnackbar("เกิดข้อผิดพลาด", "error");
 *
 * @param {string} message - ข้อความที่ต้องการแสดง
 * @param {string} type - ประเภท ('success', 'error', 'warning', 'info')
 * @param {number} duration - ระยะเวลา (milliseconds) default: 3000
 */
export function showSnackbar(message, type = 'info', duration = 3000) {
    try {
        // ใช้ function showSnackbar ที่ประกาศอยู่ใน blade
        if (typeof window.showSnackbar === 'function') {
            window.showSnackbar(message, type, duration);
        } else {
            console.warn(`⚠️ showSnackbar function not found, message: ${message}`);
        }
    } catch (error) {
        console.error('❌ Error showing snackbar:', error);
    }
}

/**
 * บันทึก log สำหรับ debug (ผลการทำงานภายใน)
 *
 * ตัวอย่าง:
 * debugLog('Farm selected', { farmId: 5, farmName: 'ฟาร์มสวนครั้ง' });
 *
 * @param {string} message - ข้อความ
 * @param {*} data - ข้อมูลที่ต้องการให้เห็น (optional)
 */
export function debugLog(message, data = null) {
    if (data) {
        console.log(`🔍 [DEBUG] ${message}:`, data);
    } else {
        console.log(`🔍 [DEBUG] ${message}`);
    }
}

/**
 * แสดง error log สำหรับ debug
 *
 * @param {string} message - ข้อความ error
 * @param {Error} error - Error object (optional)
 */
export function errorLog(message, error = null) {
    if (error) {
        console.error(`❌ [ERROR] ${message}:`, error);
    } else {
        console.error(`❌ [ERROR] ${message}`);
    }
}

/**
 * Delay execution - ใช้เวลารอก่อนทำงานต่อ
 *
 * ตัวอย่าง:
 * await delay(2000); // รอ 2 วินาที
 *
 * @param {number} ms - จำนวน milliseconds ที่ต้องการรอ
 * @returns {Promise} Promise ที่ resolve หลังจากเวลาผ่าน
 */
export function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * ตรวจสอบว่าค่านี้ว่างหรือไม่
 *
 * ตัวอย่าง:
 * isEmpty("") → true
 * isEmpty(null) → true
 * isEmpty("hello") → false
 * isEmpty(0) → true (เพราะ 0 ถือว่าว่างในบริบทการตรวจสอบ)
 *
 * @param {*} value - ค่าที่ต้องการตรวจสอบ
 * @returns {boolean} true ถ้าว่าง, false ถ้ามีค่า
 */
export function isEmpty(value) {
    return !value ||
           (typeof value === 'string' && value.trim() === '') ||
           (Array.isArray(value) && value.length === 0) ||
           (typeof value === 'object' && Object.keys(value).length === 0);
}

/**
 * ========== MEDICINE CONVERSION FUNCTIONS ==========
 * ฟังก์ชันสำหรับแปลงหน่วยยา (ml → bottles, kg → bags เป็นต้น)
 * ใช้ข้อมูล base_unit, quantity_per_unit, conversion_rate จาก storehouse
 */

/**
 * คำนวณจำนวนหน่วยสินค้าที่ต้องหักจากสต็อก
 *
 * ตัวอย่าง 1: ยา "อะกริเพน" ใช้ 100 ml
 * - base_unit: "ml", quantity_per_unit: 100, conversion_rate: 1.0
 * - คำนวน: 100 / (100 * 1.0) = 100 / 100 = 1 ขวด
 *
 * ตัวอย่าง 2: ยา "ยาฆ่าเชื้อ" ใช้ 20,000 ml = 20 l
 * - base_unit: "l", quantity_per_unit: 20, conversion_rate: 1000
 * - คำนวน: 20 (ลิตร) / (20 * 1) = 1 ถัง
 * หรือ ถ้าเป็น 5,000 ml = 5 l
 * - คำนวน: 5 / 20 = 0.25 ถัง → ปัดขึ้นเป็น 1 ถัง
 *
 * @param {number} usedQuantity - ปริมาณที่ใช้ (เป็น base_unit)
 * @param {Object} medicine - medicine object ที่มี base_unit, quantity_per_unit, conversion_rate
 * @returns {number} จำนวนหน่วยสินค้าที่ต้องหัก (ปัดขึ้น)
 */
export function calculateStockReduction(usedQuantity, medicine) {
    if (!medicine || !usedQuantity || usedQuantity <= 0) {
        return 0;
    }

    try {
        // ได้รับ base_unit, quantity_per_unit, conversion_rate
        const baseUnit = medicine.base_unit; // เช่น "ml", "kg", "l"
        const quantityPerUnit = medicine.quantity_per_unit || 1; // เช่น 100 (100 ml per bottle)
        const conversionRate = medicine.conversion_rate || 1.0; // เช่น 1.0, 1000

        // คำนวณ: จำนวนหน่วยเก็บ = ปริมาณที่ใช้ / (ปริมาณต่อขวด * อัตราแปลง)
        // เช่น: 100 ml / (100 ml/bottle * 1.0) = 1 bottle
        // เช่น: 20 l / (20 l/drum * 1.0) = 1 drum

        const totalQuantityPerUnit = quantityPerUnit * conversionRate;
        const reduction = usedQuantity / totalQuantityPerUnit;

        // ปัดขึ้นเสมอ (Ceiling) เพราะไม่สามารถใช้เศษ
        const roundedReduction = Math.ceil(reduction);

        debugLog('Stock reduction calculated', {
            usedQuantity,
            baseUnit,
            quantityPerUnit,
            conversionRate,
            reduction: reduction.toFixed(2),
            roundedReduction
        });

        return roundedReduction;
    } catch (error) {
        errorLog('Error calculating stock reduction', error);
        return 0;
    }
}

/**
 * แปลง ml เป็นข้อความที่เข้าใจ พร้อมหน่วยตรงตามหน่วยเก็บ
 *
 * ตัวอย่าง:
 * - convertMLToDisplayText(100, {base_unit: 'ml', quantity_per_unit: 100, unit: 'ขวด'})
 *   → "100 ml (1 ขวด)"
 * - convertMLToDisplayText(250, {base_unit: 'ml', quantity_per_unit: 100, unit: 'ขวด'})
 *   → "250 ml (2.5 ขวด / ปัดขึ้นเป็น 3 ขวด)"
 *
 * @param {number} mlQuantity - ปริมาณเป็น ml
 * @param {Object} medicine - medicine object ที่มี base_unit, quantity_per_unit, unit
 * @returns {string} ข้อความแสดงปริมาณพร้อมหน่วย
 */
export function convertMLToDisplayText(mlQuantity, medicine) {
    if (!medicine || !mlQuantity || mlQuantity <= 0) {
        return '-';
    }

    try {
        const quantityPerUnit = medicine.quantity_per_unit || 1;
        const unit = medicine.unit || 'หน่วย';

        const unitCount = mlQuantity / quantityPerUnit;
        const roundedUnitCount = Math.ceil(unitCount);

        // ถ้าเป็นจำนวนเต็ม ไม่ต้องแสดงเศษ
        if (unitCount === roundedUnitCount) {
            return `${mlQuantity} ml (${roundedUnitCount} ${unit})`;
        } else {
            return `${mlQuantity} ml (${unitCount.toFixed(2)} → ${roundedUnitCount} ${unit})`;
        }
    } catch (error) {
        errorLog('Error converting ML to display text', error);
        return mlQuantity + ' ml';
    }
}
