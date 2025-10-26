# Profit Chart Debugging Guide

## วิธีตรวจสอบ % ที่ charts

### 1️⃣ เปิด Browser Console
- **Windows/Linux**: `F12` หรือ `Ctrl + Shift + I`
- **Mac**: `Cmd + Option + I`
- ไปที่ tab "Console"

### 2️⃣ ดู Log Messages
ก่อนที่ page load ให้ดูว่ามี message ประมาณนี้ไหม:
```
ChartDataLabels available: function
ChartDataLabels registered
```

### 3️⃣ ถ้าไม่เห็น %
**สาเหตุอาจเป็น:**

#### A. Plugin ไม่ load ถูกต้อง
- ตรวจสอบใน console ว่ามี error ไหม
- ดูว่า network tab เห็น chartjs-plugin-datalabels CDN หรือไม่

#### B. Chart.js version conflict
- Pie chart อาจไม่ support datalabels บน doughnut type

### 4️⃣ วิธี TEST ที่ simple
ลองแก้เป็น Bar Chart style แทน Doughnut:

**แก้ไฟล์ profits/index.blade.php บรรทัด ~385:**
```javascript
type: 'bar',  // เปลี่ยนจาก 'doughnut'
```

### 5️⃣ Check ว่า Datalabels option ถูกเพิ่มหรือไม่
ดู network tab → click resource → search "datalabels"

---

## Alternative Solution: ใช้ Canvas Text Drawing

ถ้า plugin ไม่ทำงาน ให้ลองเพิ่ม % text โดยตรงบน chart ด้วย Canvas API:

```javascript
// เพิ่มใน options plugins section
afterDatasetsDraw: function(chart) {
    const ctx = chart.ctx;
    chart.data.datasets.forEach(function(dataset, datasetIndex) {
        const meta = chart.getDatasetMeta(datasetIndex);
        meta.data.forEach(function(datapoint, index) {
            const value = dataset.data[index];
            const total = dataset.data.reduce((a, b) => a + b, 0);
            const percentage = total > 0 ? ((value / total) * 100) : 0;
            
            // Draw percentage text on chart
            ctx.fillStyle = '#000';
            ctx.font = 'bold 12px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(percentage.toFixed(1) + '%', datapoint.x, datapoint.y);
        });
    });
}
```

---

## ✅ ถ้า % แสดงขึ้นมา
ยอดเยี่ยม! ให้ลบ console.log lines เพื่อเก็บ code ให้สะอาด
