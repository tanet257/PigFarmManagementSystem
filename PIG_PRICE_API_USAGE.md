# วิธีใช้งาน Pig Price API

## 📌 Endpoints

### 1. ดึงราคาหมูล่าสุด
```
GET /api/pig-price/latest
```

**Response:**
```json
{
    "success": true,
    "data": {
        "date": "2025-10-06",
        "price_per_pig": 1400.00,
        "price_per_kg": 56.00,
        "source": "CPF",
        "updated_at": "2025-10-11 10:30:00"
    }
}
```

### 2. ดึงประวัติราคา (ทุกสัปดาห์)
```
GET /api/pig-price/history
```

**Response:**
```json
{
    "success": true,
    "count": 41,
    "data": [
        {
            "date": "2025-10-06",
            "price_per_pig": 1400.00,
            "price_per_kg": 56.00
        },
        {
            "date": "2025-09-29",
            "price_per_pig": 1400.00,
            "price_per_kg": 56.00
        }
    ]
}
```

### 3. รีเฟรชราคา (ลบ cache และดึงใหม่)
```
POST /api/pig-price/refresh
```

**Response:**
```json
{
    "success": true,
    "message": "อัพเดทราคาสำเร็จ",
    "data": {
        "date": "2025-10-06",
        "price_per_kg": 56.00
    }
}
```

---

## 🔧 ใช้งานใน Blade Template

### แสดงราคาล่าสุดในหน้า Create Modal

```blade
<script>
// ดึงราคาหมูล่าสุดจาก API
async function fetchLatestPrice() {
    try {
        const response = await fetch('/api/pig-price/latest');
        const result = await response.json();

        if (result.success) {
            const price = result.data.price_per_kg;

            // แสดงในฟอร์ม
            document.querySelector('input[name="price_per_kg"]').value = price;

            // แสดง Badge แนะนำ
            alert(`ราคากลาง CPF: ${price} บาท/กก. (ณ วันที่ ${result.data.date})`);
        }
    } catch (error) {
        console.error('Error fetching pig price:', error);
    }
}

// เรียกใช้เมื่อเปิด modal
document.getElementById('createModal').addEventListener('shown.bs.modal', function() {
    fetchLatestPrice();
});
</script>
```

---

## 🎯 การใช้ใน Controller

```php
use App\Services\PigPriceService;

public function create(Request $request)
{
    // ดึงราคาอ้างอิง
    $suggestedPrice = PigPriceService::getLatestPrice();

    // ถ้าผู้ใช้ไม่ได้ระบุราคา ให้ใช้ราคาอ้างอิง
    $pricePerKg = $request->price_per_kg ?? $suggestedPrice['price_per_kg'];

    // บันทึกข้อมูล...
}
```

---

## ⚠️ หมายเหตุ

1. **Cache**: ข้อมูลจะ cache ไว้ 1 ชั่วโมง เพื่อลดการเรียกเว็บ CPF บ่อยเกินไป
2. **Error Handling**: ถ้าดึงข้อมูลไม่สำเร็จ จะ return `null` และบันทึกใน Log
3. **Rate Limit**: ไม่ควรเรียก API บ่อยเกินไป ควรใช้ Cache
4. **Web Scraping**: เว็บ CPF อาจเปลี่ยน HTML Structure ได้ ต้องตรวจสอบและปรับ Regex

---

## 🧪 ทดสอบ

```bash
# ทดสอบดึงราคาล่าสุด
curl http://localhost:8000/api/pig-price/latest

# ทดสอบดึงประวัติ
curl http://localhost:8000/api/pig-price/history

# ทดสอบรีเฟรช (ใช้ POST)
curl -X POST http://localhost:8000/api/pig-price/refresh
```
