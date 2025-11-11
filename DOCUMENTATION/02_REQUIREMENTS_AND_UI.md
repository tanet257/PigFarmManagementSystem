# 02 Requirements and UX/UI

## 3.2 วิเคราะห์ความต้องการของผู้ใช้
หลังจากการสอบถามความต้องการของผู้ใช้งาน ได้นำมาวิเคราะห์และจัดหมวดหมู่ความต้องการ ดังนี้

### 3.2.1 การวิเคราะห์ผู้ใช้งาน (User Analysis)
จากการศึกษาพบว่าระบบจะมีผู้ใช้งานหลัก 2 กลุ่ม คือ

1. เจ้าของฟาร์ม (Farm Owner)
   - เป้าหมาย (Goals): ตรวจสอบภาพรวมฟาร์ม ดูต้นทุน ผลกำไร และแนวโน้มเพื่อตัดสินใจเชิงธุรกิจ
   - กิจกรรมหลัก (Primary tasks): ดูแดชบอร์ดรายงาน, กำหนดงบประมาณ, อนุมัติการจ่ายเงิน, ตรวจสอบ KPI ของแต่ละรุ่น
   - ความถี่ (Frequency): รายวัน/สัปดาห์ สำหรับสรุปรายงาน, รายเดือนสำหรับการวางแผน
   - สิทธิ์ (Permissions): อ่านข้อมูลเชิงสรุปและละเอียดได้ทั้งหมด ตัดสินใจอนุมัติรายงานและการจ่ายเงิน
   - Pain points: ต้องการข้อมูลสรุปที่ชัดเจน, ไม่ต้องการความซับซ้อนในการเข้าดู

2. พนักงานฟาร์ม (Farm Staff)
   - เป้าหมาย (Goals): บันทึกข้อมูลประจำวันให้เร็วและแม่นยำ เพื่อให้ข้อมูลครบถ้วนสำหรับการคำนวณ KPI
   - กิจกรรมหลัก (Primary tasks): บันทึกการให้อาหาร, บันทึกการตาย/เจ็บป่วย, บันทึกการขาย, แจ้งเหตุขัดข้อง/สต็อกต่ำ
   - ความถี่ (Frequency): หลายครั้งต่อวัน (เช้า/เย็น/เมื่อเกิดเหตุการณ์)
   - สิทธิ์ (Permissions): บันทึก/แก้ไขข้อมูลในช่วงเวลาที่อนุญาต, ดูประวัติข้อมูลที่เกี่ยวข้องกับงานของตน
   - Pain points: หน้าจอยาวหรือซับซ้อนจะช้าต่อการทำงาน, ต้องการ validation และ default values เพื่อลดการพิมพ์

3. ผู้จัดการ/หัวหน้าฟาร์ม (Manager) — (Optional / Secondary persona)
   - เป้าหมาย: ตรวจสอบและอนุมัติงานที่สำคัญ เช่น การจ่ายเงิน, การสั่งซื้อสต็อก
   - กิจกรรม: อนุมัติ Cost, ดูรายงาน KPI ระหว่างวัน, วางแผนการจัดสรรทรัพยากร
   - สิทธิ์: ดู/อนุมัติ/ปฏิเสธบางรายการ มีการแจ้งเตือนรายการที่รออนุมัติ

#### Persona Summaries (ตัวอย่าง user stories)
- ในฐานะเจ้าของฟาร์ม ฉันต้องการดูรายงานกำไรต่อรุ่นในหน้าแดชบอร์ด เพื่อประเมินผลการเลี้ยง
- ในฐานะพนักงาน ฉันต้องการบันทึกข้อมูลการให้อาหารภายใน 2 ขั้นตอน เพื่อไม่ให้เสียเวลาทำงาน
- ในฐานะผู้จัดการ ฉันต้องการได้รับการแจ้งเตือนเมื่อต้นทุนที่บันทึกมีสถานะรออนุมัติ

---

### 3.2.2 การวิเคราะห์ความต้องการเชิงหน้าที่ (Functional Requirements)
ต่อไปนี้เป็นรายการฟังก์ชันหลักที่ระบบต้องรองรับ พร้อมรายละเอียดการทำงานย่อย (sub-features) และ acceptance criteria

1. ระบบจัดการฟาร์มและรุ่นหมู (Farm & Batch Management)
   - ฟังก์ชัน: สร้าง/อ่าน/อัปเดต/ลบ (CRUD) ฟาร์ม, รุ่นหมู (batch)
   - รายละเอียด: บทบาทต้องสามารถผูก batch กับ farm, กำหนด start_date, expected_end, initial quantity, target weight
   - Business rules: batch_code ต้องเป็น unique; ปิด (close) batch เมื่อออกขายครบหรือหมดอายุ
   - Acceptance criteria: เมื่อสร้าง batch ใหม่ จะต้องปรากฏในรายการ batch ของ farm และมีสถานะ 'active'

2. ระบบบันทึกข้อมูลประจำวัน (Daily Record)
   - ฟังก์ชัน: บันทึกการให้อาหาร, น้ำหนักเฉลี่ย, จำนวนตาย, จำนวนป่วย, หมายเหตุการรักษา
   - รายละเอียด: ฟอร์มบันทึกควรมีค่า default เมื่อเป็น batch เดียวกัน, validation (เช่น weight > 0)
   - Integration: บันทึกจะส่งผลต่อ batch_metrics (ADG, FCR) แบบเรียลไทม์หรือตาม schedule
   - Acceptance criteria: บันทึกเรียบร้อยแล้วต้องแสดงในตาราง daily records และส่งผลต่อ KPI ภายใน 1 นาที (หรือในการรัน job ถ้าเป็น background)

3. ระบบจัดการต้นทุน (Cost Management)
   - ฟังก์ชัน: สร้างบันทึกต้นทุน (feed, medicine, labor, utility), แนบใบเสร็จ, เปลี่ยนสถานะ (pending->approved->rejected)
   - Workflow: พนักงานบันทึกเป็น pending -> ผู้จัดการ/เจ้าของอนุมัติหรือปฏิเสธ -> เมื่ออนุมัติจะสร้าง cost_payment (ถ้ามี)
   - Automation: กำหนดกฎ auto-approve สำหรับค่าต้นทุนต่ำกว่า threshold
   - Acceptance criteria: ระบบเก็บ trail ของการอนุมัติ (who/when) และอัพเดตสถานะได้

4. ระบบรายได้และการขาย (Sales & Revenue)
   - ฟังก์ชัน: สร้างรายการขาย (pig_sales), คำนวณ revenue, เก็บข้อมูลลูกค้า และการชำระเงิน
   - Integration: เมื่อลงขายแล้วจะลดจำนวนใน batch/pen และสร้าง revenue record
   - Acceptance criteria: สร้างเลขที่ขาย unique และตรวจสอบว่าจำนวนขายไม่เกินคงเหลือ

5. ระบบคำนวณผลกำไรอัตโนมัติ (Profit Calculation)
   - ฟังก์ชัน: คำนวณ total_revenue, total_cost, gross_profit, profit_margin, แยกต้นทุนตามประเภท
   - Trigger: เมื่อ revenue หรือ cost ถูกสร้าง/อัปเดต ระบบจะ recalculation profit ของ batch
   - Acceptance criteria: ผลลัพธ์ต้องสอดคล้องกับสูตรที่ระบุ และมี log เวอร์ชันของการคำนวณ

6. ระบบจัดการสินค้าคงคลัง (Inventory Management)
   - ฟังก์ชัน: สต็อกสินค้า (storehouse), บันทึก movement (in/out), แจ้งเตือนเมื่อ stock ต่ำ (threshold)
   - Automation: แจ้งเตือนผ่าน Notification/Email/SMS (ถ้า config ได้)
   - Acceptance criteria: ระบบลด/เพิ่ม stock เมื่อมี movement และ trigger alert ถ้าน้อยกว่า min_quantity

7. ระบบรายงานและแดชบอร์ด (Dashboard & Reports)
   - ฟังก์ชัน: แสดง KPI (ADG, FCR, mortality rate), summaries (avg cost per pig), trend charts, export (PDF/CSV)
   - Filters: per farm, per batch, per period
   - Acceptance criteria: แดชบอร์ดโหลดภายใน 2 วินาทีสำหรับชุดข้อมูลปกติ (<= 10k rows aggregated)

8. ระบบสิทธิ์และผู้ใช้งาน (Roles & Permissions)
   - ฟังก์ชัน: RBAC, assign roles, audit trail, password reset, 2FA (optional)
   - Acceptance criteria: ผู้ใช้ที่ถูกกำหนด role จะเห็น/ทำงานในส่วนที่ได้รับอนุญาตเท่านั้น

9. การแจ้งเตือนและการอนุมัติ (Notifications & Approvals)
   - ฟังก์ชัน: แจ้งเตือนรายการรออนุมัติ, สต็อกต่ำ, หมูตายสูงกว่าค่าเกณฑ์
   - Channels: in-app notifications, email (SMTP config)
   - Acceptance criteria: Notification ถูกบันทึกและแสดงใน UI พร้อมลิงก์ไปยังรายการ

10. การส่งออกข้อมูลและการรายงาน (Export & Integration)
    - ฟังก์ชัน: Export CSV/PDF, API for integration with accounting systems
    - Acceptance criteria: Export สามารถกรองข้อมูลได้และดาวน์โหลดได้ในรูปแบบที่กำหนด

---

### 3.2.3 การวิเคราะห์ความต้องการเชิงคุณภาพ (Non-Functional Requirements)

1. ประสิทธิภาพ (Performance)
   - Target: หน้า Dashboard ต้องตอบสนองภายใน 2 วินาทีสำหรับชุดข้อมูลสรุป (aggregated)
   - Batch ops: Background jobs สำหรับ heavy calculation (queue worker)
   - Scalability: รองรับเพิ่มผู้ใช้งานแบบแนวนอน (stateless app + shared DB)

2. ความเข้ากันได้ (Compatibility & Responsiveness)
   - รองรับ Browser สมัยใหม่ (Chrome, Edge, Safari) และอุปกรณ์มือถือ (iOS/Android)
   - Responsive design with breakpoints: mobile (<= 480px), tablet (481-1024px), desktop (>=1025px)

3. ความปลอดภัย (Security)
   - Authentication: Laravel auth + optional 2FA
   - Authorization: RBAC with fine-grained permissions
   - Transport: HTTPS required
   - Data protection: Encrypt sensitive data at rest (where required) and in transit
   - Auditing: Log critical actions (create/update/delete) with user/time
   - Rate limiting: API rate limits to prevent abuse

4. ความง่ายในการใช้งาน (Usability)
   - UI language: Thai primary, English optional (localization)
   - UX: Minimize steps for frequent tasks (daily entry, sale)
   - Form validation and helpful inline messages
   - Onboarding: short tips and a help section

5. ความน่าเชื่อถือและความต่อเนื่อง (Reliability & Availability)
   - Backup: Daily DB backup policy, with restore guide
   - Fault tolerance: Retry policies for background tasks
   - Monitoring: Application logs, alerts for failures

6. การเข้าถึง (Accessibility)
   - Follow WCAG AA where possible: color contrast, keyboard navigation, aria labels for controls

7. ความสามารถในการบำรุงรักษา (Maintainability)
   - Clean separation: controllers, services, observers
   - Tests: Unit tests for services, feature tests for critical flows

8. ข้อกำหนดด้านการสำรองข้อมูลและกู้คืน (Backup & Recovery)
   - Point-in-time backups optional; at minimum daily dumps
   - Test restore procedures quarterly

9. การวัดผล (Metrics)
   - SLOs: dashboard load time, job queue latency, API error rate (<1%)
   - Business metrics: mortality rate, avg feed cost/pig, profit margin

---

## 3.3 ออกแบบ UX/UI

### 3.3.1 การออกแบบประสบการณ์ผู้ใช้ (User Experience Design)
การออกแบบประสบการณ์ผู้ใช้งานเริ่มจากการศึกษาขั้นตอนการทำงานจริงภายในฟาร์ม โดยเน้นให้ผู้ใช้สามารถบันทึกข้อมูลได้ง่ายและรวดเร็ว

#### หลักการออกแบบ UX
- Reduce cognitive load: ลดจำนวนฟิลด์ที่ต้องกรอกในแบบฟอร์มที่ใช้งานบ่อย
- Optimize for speed: ใช้ default values, auto-complete, quick actions
- Provide feedback: แจ้งสถานะ (success/error) ชัดเจน
- Progressive disclosure: ซ่อนฟิลด์ขั้นสูงไว้ใน collapsible panels

#### User flows (ตัวอย่างสำคัญ)
1. Daily Entry Flow (พนักงาน)
   - เปิดหน้า Daily Entry -> เลือก Batch -> กรอกวันที่ (default: วันนี้) -> กรอก avg weight, feed consumed, dead_count -> บันทึก
   - Success path: ข้อความ "บันทึกสำเร็จ" และตัวเลข KPI อัพเดต
   - Edge cases: หากจำนวนตายมากเกินกว่าคงเหลือ ให้แสดง warning และป้องกันการบันทึก
   - Time target: < 30 วินาที ต่อ entry

2. Cost Approval Flow (พนักงาน → ผู้จัดการ → เจ้าของ)
   - พนักงานบันทึก cost เป็น pending -> ผู้จัดการได้รับ notification -> ผู้จัดการเปิด review แล้ว approve/reject -> ถ้า approve และเกิน threshold อาจต้องให้เจ้าของ final approve
   - Audit trail: บันทึก who/when/notes

3. Sale Flow (พนักงาน)
   - สร้าง sale -> เลือก customer -> ระบุจำนวนและน้ำหนัก -> ระบบคำนวณ revenue และสร้าง pending payment -> เจ้าของ/ผู้จัดการอนุมัติการจ่ายเงิน
   - Validation: ห้ามขายเกินจำนวนคงเหลือ

4. Dashboard Flow (เจ้าของ/ผู้จัดการ)
   - เปิด dashboard -> ดู KPI snapshot (วันนี้/สัปดาห์/เดือน) -> คลิก chart เพื่อ drill-down ไปยัง batch หรือ daily records

#### Notifications & Alerts
- ประเภทของแจ้งเตือน: stock_low, pending_approval, high_mortality, scheduled_task_complete
- Behaviour: in-app bell icon with unread count + optionally email
- Actionability: notification มีลิงก์ตรงไปยังรายการเพื่อดำเนินการ

#### Acceptance criteria (UX)
- พนักงานสามารถสร้าง Daily Entry ใน <= 30 วินาที
- เจ้าของสามารถดูรายงานสรุป (monthly profit) ใน <= 5 วินาที (aggregated)
- ระบบต้องให้ข้อผิดพลาดที่ชัดเจนและแนวทางแก้ไขสำหรับ validation errors

---

### 3.3.2 การออกแบบส่วนติดต่อผู้ใช้ (User Interface Design)

#### Visual system (โทนสีและ typography)
- Primary colors: Orange (#FF8C42) as action color, White (#FFFFFF) as background
- Accent colors: Dark Gray (#2D2D2D) for text, Light Gray (#F5F5F5) for surfaces
- Typography: Use system font stack or Google font (e.g., "Noto Sans Thai" / "Inter") for Thai readability

#### Layout and components
- Global layout: Header (top) + Sidebar (left) + Content area
- Sidebar: collapsible, icons + labels, grouped sections (Dashboard, Batches, Inventory, Costs, Sales, Reports, Settings)
- Header: notification icon, user menu, quick-add button
- Dashboard Widgets: KPI cards, trend charts (line), breakdown (pie/bar), recent activities
- Forms: clear labels, placeholders, inline validation, grouped fields. Use large touch targets for mobile.
- Tables: sortable columns, server-side pagination, filters, bulk actions
- Modals: confirm actions (delete, approve)
- Buttons: primary (orange), secondary (outline), danger (red)
- Icons: use an icon set that supports Thai semantics (FontAwesome / Heroicons)

#### Responsive behavior
- Mobile: Single-column layout, condensed tables into list/views, quick-add floating button
- Tablet: Two-column where possible
- Desktop: Sidebar visible by default; table grid with pagination

#### Accessibility (A11y)
- Color contrast: ensure text/icons meet WCAG AA
- Keyboard: All interactive elements reachable by keyboard
- Screen reader: ARIA labels for dynamic regions and forms
- Focus states: visible outlines for keyboard navigation

#### Forms and Validation patterns
- Client-side validation with helpful messages, server-side validation as source of truth
- Clear error messages near fields, summary of errors at top
- Use input masks for phone, date pickers for dates, numeric stepper for quantities

#### Charts and Reports
- Use clear color palettes; allow high-contrast mode
- Export options: CSV, PDF for reports
- Drill-down: clicking a chart element navigates to filtered view

#### Interaction details
- Inline editing for minor tweaks (e.g., edit quantity) with optimistic UI where safe
- Undo: for destructive actions show toast with "Undo" for short window (5-10s)

#### Example page specifications
1. Dashboard (Owner)
   - Top KPIs: total pigs, avg cost/pig, gross profit, mortality rate
   - Trend chart: profit over last 12 months
   - Quick actions: create sale, create cost, create daily record

2. Daily Entry (Staff)
   - Quick-select batch -> dynamic fields appear -> Save
   - Show last 3 entries for that batch

3. Cost Entry (Staff)
   - Fields: date, type (enum), amount, receipt attach, note
   - Auto-calc: total price = quantity * price_per_unit
   - Preview approval workflow

4. Stock/Inventory Page
   - List items with current qty, min_qty, status
   - Quick adjust for emergency corrections (log reason)

---

## 3.4 QA, Acceptance criteria and Test cases

### Acceptance Criteria (overall)
- All core flows (daily entry, cost entry, sale, approval) must have automated tests
- Dashboard must display consistent KPI values matching calculations in Profit module
- Role-based access control enforced on UI and API

### Example test cases
1. Create Daily Entry: create entry with valid data appears in DB KPI recalculated
2. Cost approval: create cost as staff manager approves status changes to approved and cost_payment generated
3. Sale validation: attempt to create sale greater than available quantity rejected with friendly error
4. Stock alert: reduce inventory below min_quantity notification created and shown

---

## 3.5 Deliverables (UX)
- Low-fidelity wireframes for: Dashboard, Daily Entry, Cost Entry, Sale Flow, Approval Flow
- High-fidelity mockups for Dashboard and Daily Entry
- Component library (buttons, forms, tables) in storybook or UI kit
- Usability test reports (5 users, key tasks)

---

## Appendix: Quick wireframe sketches (text)

Dashboard (mobile summary)
- Card Total pigs
- Card Avg cost/pig
- Chart Profit last 6 months (small)
- List Recent activities

Daily Entry (form)
- Batch selector (searchable)
- Date (default today)
- Avg weight (kg)
- Feed consumed (kg)
- Dead count
- Notes
- Save button

---

เอกสารนี้เป็นแผนการออกแบบความต้องการและ UX/UI ที่ขยายรายละเอียดจากหัวข้อ 3.2 และ 3.3 ครบถ้วนและพร้อมใช้งาน
