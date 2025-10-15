<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Pig Farm Management System</title>
<meta name="description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="all,follow">

<!-- Bootstrap 5 CSS (Base Theme) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<!-- Bootstrap Icon CSS-->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

<!-- Font Awesome CSS-->
<link rel="stylesheet" href="admin/vendor/font-awesome/css/font-awesome.min.css">

<!-- Custom Font Icons CSS-->
<link rel="stylesheet" href="admin/css/font.css">

<!-- Google fonts - Muli-->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Muli:300,400,700">

<!-- Choices.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<!-- flatpickr.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- flatpickr month CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">

<!-- Theme stylesheet (Original Admin Theme) -->
<link rel="stylesheet" href="admin/css/style.default.css" id="theme-stylesheet">

<!-- Custom stylesheet - Override with Warm Orange Theme -->
<link rel="stylesheet" href="admin/css/custom.css">

<!-- Favicon-->
<link rel="shortcut icon" href="admin/img/favicon.ico">
<!-- Tweaks for older IEs--><!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->


<style>
    /* ========== CSS Variables - Warm Orange Theme ========== */
    :root {
        /* Main Colors */
        --primary-orange: #FF5B22;
        /* สีส้มหลัก */
        --secondary-orange: #FF9130;
        /* สีส้มรอง */
        --light-peach: #FECDA6;
        /* ครีมส้มอ่อน */
        --gray: #A9A9A9;
        /* เทา */
        --dark-blue: #273F4F;
        /* น้ำเงินเข้ม */
        --cream-white: #F9F5F0;
        /* ครีมขาว */

        /* Background Colors */
        --body-bg: #F9F5F0;
        --card-bg: #ffffff;
        --sidebar-bg: #273F4F;
        --header-bg: #ffffff;

        /* Text Colors */
        --text-dark: #273F4F;
        --text-light: #F9F5F0;
        --text-muted: #A9A9A9;

        /* Border & Shadow */
        --border-color: #FECDA6;
        --shadow-sm: 0 2px 4px rgba(255, 91, 34, 0.1);
        --shadow-md: 0 4px 8px rgba(255, 91, 34, 0.15);

        /* Status Colors */
        --success: #28a745;
        --danger: #dc3545;
        --warning: #f39c12;
        --info: #17a2b8;

        --bg-warning: #f39c12;
        --bg-secondary: #FF9130;
        --bg-tertiary: #ffffff;
    }

    /* ========== Global Styles ========== */

    .bg-secondary {
        background-color: var(--bg-secondary) !important;
        color: var(--text-light);
    }

    .bg-tertiary {
        background-color: var(--bg-tertiary) !important;
        color: var(--text-dark);
    }


    body {
        background-color: var(--body-bg);
        color: var(--text-dark);
        font-family: 'Muli', sans-serif;
    }

    /* Cards */
    .card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px !important;
        box-shadow: var(--shadow-sm);
        transition: box-shadow 0.3s ease;
        overflow: hidden !important;
    }

    .card:hover {
        box-shadow: var(--shadow-md);
    }

    .card-title {

        color: #ffffff;
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: var(--text-light);
        border-radius: 12px !important;
        border-bottom: none !important;
        font-weight: 600;
        padding: 20px 25px !important;
    }

    .card-body {
        background: linear-gradient(to bottom, #FCF9EA, #ffffff) !important;
        border-radius: 0 0 12px 12px !important;
        padding: 25px !important;
    }

    /* Status Summary Cards */
    .card-status-summary {
        background: transparent !important;
        padding: 30px 20px !important;
        border-radius: 12px !important;
    }

    .card-status-summary h3 {
        font-size: 2.5rem !important;
        font-weight: 700 !important;
        margin-bottom: 10px !important;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card-status-summary p {
        font-size: 1rem !important;
        font-weight: 500 !important;
        margin-bottom: 0 !important;
        opacity: 0.95;
    }

    .card-status-summary i {
        font-size: 1.2rem !important;
        margin-right: 5px;
    }

    /* Override Bootstrap's bg-warning with custom color */
    .bg-warning {
        background-color: var(--bg-warning) !important;
    }

    /* Buttons */
    .btn-primary {
        background-color: var(--primary-orange) !important;
        border: none;
        color: white;
    }

    .btn-primary:hover {
        background-color: #EF7722 !important;
        box-shadow: 0 4px 12px rgba(255, 91, 34, 0.3) !important;
    }

    .btn-secondary {
        background-color: var(--gray);
        border: none;
    }

    .btn-secondary:hover {
        background-color: #8a8a8a;
    }

    /* ========== Sidebar Styles ========== */

    /* Container หลักของ Sidebar */
    #sidebar {
        background-color: #17313E !important;
        /* สีน้ำเงินเข้มตามธีม */
        color: var(--text-light) !important;
        /* สีตัวอักษรขาว */
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1) !important;
        /* เงาด้านขวาเพื่อให้ดูมีมิติ */
    }

    /* เส้นแบ่งระหว่างแต่ละเมนูหลัก */
    #sidebar ul.list-unstyled>li {
        border-bottom: 1px solid rgba(255, 145, 48, 0.15) !important;
        /* เส้นสีส้มอ่อน 15% */
    }

    /* ไม่ให้เมนูสุดท้ายมีเส้นแบ่ง */
    #sidebar ul.list-unstyled>li:last-child {
        border-bottom: none !important;
    }

    /* ========== ลิงก์เมนูหลัก (ระดับ 1) ========== */
    #sidebar ul.list-unstyled>li>a {
        color: var(--text-light) !important;
        /* สีตัวอักษรขาว */
        padding: 15px 20px !important;
        /* ระยะห่างด้านในปุ่ม */
        display: block !important;
        /* ให้คลิกได้ทั้งพื้นที่ */
        text-decoration: none !important;
        /* ไม่ให้มีเส้นใต้ */
        transition: all 0.3s ease !important;
        /* Animation นุ่มนวล 0.3 วินาที */
        font-weight: 500 !important;
        /* ตัวอักษรหนาปานกลาง */
        border-left: 4px solid transparent !important;
        /* Border ซ้ายโปร่งใส (จะเปลี่ยนสีเมื่อ hover/active) */
        background-color: transparent !important;
    }

    /* เมื่อ Hover เมนูหลัก */
    #sidebar ul.list-unstyled>li>a:hover {
        background-color: rgba(255, 91, 34, 0.15) !important;
        /* พื้นหลังสีส้มจาง 15% */
        border-left-color: var(--primary-orange) !important;
        /* Border ซ้ายเป็นสีส้ม */
        padding-left: 25px !important;
        /* เลื่อนข้อความไปทางขวา 5px */
    }

    /* ========== Icon ในเมนูหลัก ========== */
    #sidebar ul.list-unstyled>li>a i {
        margin-right: 10px !important;
        /* ระยะห่างระหว่าง icon กับข้อความ */
        width: 20px !important;
        /* กำหนดความกว้างเท่ากันทุก icon */
        text-align: center !important;
        /* จัด icon ให้อยู่กลาง */
        color: var(--light-peach) !important;
        /* สี icon เป็นครีมส้มอ่อน */
        transition: all 0.3s ease !important;
        /* Animation นุ่มนวล */
    }

    /* Icon เมื่อ Hover เมนู */
    #sidebar ul.list-unstyled>li>a:hover i {
        color: var(--primary-orange) !important;
        /* เปลี่ยนเป็นสีส้มสด */
        transform: scale(1.1) !important;
        /* ขยาย icon 10% */
    }

    /* ========== เมนูที่กำลัง Active (หน้าปัจจุบัน) ========== */
    #sidebar ul.list-unstyled>li.active>a {
        background: linear-gradient(90deg, rgba(255, 91, 34, 0.2), rgba(255, 145, 48, 0.1)) !important;
        /* Gradient สีส้ม */
        border-left: 4px solid var(--primary-orange) !important;
        /* Border ซ้ายสีส้มเข้ม */
        color: #fff !important;
        /* ตัวอักษรสีขาว */
        font-weight: 600 !important;
        /* ตัวอักษรหนาขึ้น */
    }

    /* Icon ของเมนู Active */
    #sidebar ul.list-unstyled>li.active>a i {
        color: var(--primary-orange) !important;
        /* Icon สีส้มสด */
    }

    /* ========== Sub-menu (Dropdown ระดับ 2) ========== */
    #sidebar ul.collapse {
        background-color: rgba(0, 0, 0, 0.2) !important;
        /* พื้นหลังดำจาง 20% */
        padding-left: 0 !important;
        /* ไม่มี padding ซ้าย */
    }

    /* ลิงก์ใน Sub-menu */
    #sidebar ul.collapse li a {
        color: rgba(255, 255, 255, 0.8) !important;
        /* สีขาวจาง 80% */
        padding: 12px 20px 12px 50px !important;
        /* padding ซ้ายมากขึ้นเพื่อเยื้อง */
        display: block !important;
        text-decoration: none !important;
        transition: all 0.3s ease !important;
        font-size: 0.9rem !important;
        /* ตัวอักษรเล็กกว่าเมนูหลัก */
        border-left: 3px solid transparent !important;
        /* Border ซ้ายบางกว่าเมนูหลัก */
        background-color: transparent !important;
    }

    /* Hover Sub-menu */
    #sidebar ul.collapse li a:hover {
        background-color: rgba(255, 145, 48, 0.2) !important;
        /* พื้นหลังสีส้มรองจาง 20% */
        color: #fff !important;
        /* ตัวอักษรสีขาวชัด */
        padding-left: 55px !important;
        /* เลื่อนไปทางขวา 5px */
        border-left-color: var(--secondary-orange) !important;
        /* Border ซ้ายสีส้มรอง */
    }

    /* Sub-menu ที่ Active */
    #sidebar ul.collapse li a.active {
        background-color: rgba(255, 145, 48, 0.25) !important;
        /* พื้นหลังสีส้มจาง 25% */
        color: #fff !important;
        /* ตัวอักษรสีขาว */
        border-left: 3px solid var(--secondary-orange) !important;
        /* Border ซ้ายสีส้มรอง */
        font-weight: 600 !important;
        /* ตัวอักษรหนา */
    }

    /* ========== Nested Sub-menu (ระดับ 3 - เช่น Farm > Add Farm) ========== */
    #sidebar ul.collapse ul.collapse {
        background-color: rgba(0, 0, 0, 0.3) !important;
        /* พื้นหลังดำเข้มขึ้น 30% */
    }

    /* ลิงก์ใน Nested Sub-menu */
    #sidebar ul.collapse ul.collapse li a {
        padding-left: 70px !important;
        /* เยื้องมากขึ้น */
        font-size: 0.85rem !important;
        /* ตัวอักษรเล็กลง */
    }

    /* Hover Nested Sub-menu */
    #sidebar ul.collapse ul.collapse li a:hover {
        padding-left: 75px !important;
        /* เลื่อนไปทางขวา 5px */
        background-color: rgba(255, 145, 48, 0.15) !important;
        /* พื้นหลังสีส้มจางลง */
    }

    /* ========== Header / Navbar Styles ========== */
    #header,
    header.header,
    nav.navbar,
    #header .navbar,
    header .navbar {
        background-color: #17313E !important;
        /* สีเดียวกับ sidebar */
    }

    #header .navbar-brand,
    header .navbar-brand {
        color: white !important;
    }

    #header .nav-link,
    header .nav-link {
        color: rgba(255, 255, 255, 0.8) !important;
    }

    #header .nav-link:hover,
    header .nav-link:hover {
        color: var(--primary-orange) !important;
    }

    /* Header */
    .page-header {
        background-color: #f5f5f5 !important;
        border-bottom: 2px solid rgba(187, 186, 186, 0.3) !important;
        box-shadow: var(--shadow-sm);
    }

    .page-content {
        background-color: #f5f5f5 !important;
        border-bottom: 2px solid rgba(255, 145, 48, 0.3) !important;
        box-shadow: var(--shadow-sm);
    }

    /* Tables */
    .table {
        background-color: var(--card-bg);
    }

    .table thead {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: white;
    }

    .table tbody tr:hover {
        background-color: var(--light-peach);
        cursor: pointer;
    }

    /* Forms */
    .form-control,
    .form-select {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 6px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-orange);
        box-shadow: 0 0 0 0.2rem rgba(255, 91, 34, 0.25);
    }

    .per-page {
        width: 100px !important;
        /* ความกว้างสั้นแต่ยังอ่านง่าย */
        border-radius: 8px;
        padding: 4px 8px;
        font-size: 0.875rem;
    }

    /* Badges */
    .badge-primary {
        background-color: var(--primary-orange);
    }

    .badge-secondary {
        background-color: var(--secondary-orange);
    }

    /* Links */
    a {
        color: var(--primary-orange);
        transition: color 0.3s ease;
    }

    a:hover {
        color: var(--secondary-orange);
    }

    /* ========== Dropdown Menu Styles ========== */
    .dropdown-menu {
        min-width: 100% !important;
        max-width: 100% !important;
        width: 100% !important;
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #1E3E62;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 0.375rem;
    }

    .dropdown-toggle {
        background-color: var(--primary-orange) !important;
    }

    /* แก้ focus outline ให้เป็นสีส้มตามธีม */
    .dropdown-toggle:focus,
    .dropdown-toggle:active,
    .btn:focus,
    .btn:active,
    button:focus,
    button:active {
        outline: none !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 145, 48, 0.25) !important;
    }

    /* แก้ไขขนาด dropdown-menu ให้ติดกับ parent ที่มี dropdown class */
    .dropdown {
        position: relative;
    }

    .dropdown .dropdown-menu {
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        background-color: #273F4F !important;
        border: 1px solid rgba(255, 145, 48, 0.3) !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
        padding: 0.5rem 0 !important;
    }

    /* สำหรับ barn และ pen dropdown ที่ไม่ได้อยู่ใน .dropdown wrapper */
    .barn-dropdown,
    .pen-dropdown {
        position: absolute !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        margin: 0 !important;

    }

    .dropdown-item {
        background-color: transparent !important;
        color: #ffffff !important;
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
        border: none !important;
    }

    .dropdown-item:hover {
        background-color: rgba(255, 145, 48, 0.2) !important;
        color: var(--secondary-orange) !important;
        transform: translateX(5px);
    }

    /* ไม่แสดงสีเมื่อ active */
    .dropdown-item.active {
        background-color: transparent !important;
        color: #ffffff !important;
    }

    .dropdown-item.active:hover {
        background-color: rgba(255, 145, 48, 0.2) !important;
        color: var(--secondary-orange) !important;
    }


    /* ========== Custom Cards ========== */
    .card-custom {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange)) !important;
        color: white;
        border: none;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(255, 91, 34, 0.2);
    }

    .card-custom:hover {
        box-shadow: 0 8px 20px rgba(255, 91, 34, 0.3);
    }

    .card-custom-secondary {
        background: #1E3E62;
        border: 1px solid #1E3E62;
        border-radius: 1rem;
        padding: 1rem 1.5rem;
        color: white;
        box-shadow: 0 4px 12px rgba(30, 62, 98, 0.2);
    }

    .card-custom-secondary:hover {
        box-shadow: 0 8px 20px rgba(30, 62, 98, 0.3);
    }

    .card-custom-tertiary {
        background: linear-gradient(#FCF9EA, #ffffff);
        color: var(--text-dark);
        border: 1px solid #FECDA6;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(254, 205, 166, 0.2);
    }

    .card-custom-tertiary:hover {
        box-shadow: 0 8px 20px rgba(254, 205, 166, 0.3);
    }

    .cardTemplateRow {
        background-color: #fff;
        border-radius: 0.75rem;
        padding: 1rem;
        margin: 0.5rem auto;
        width: 95%;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    /* ========== Toolbar ========== */
    .toolbar {
        background-color: white;
        border-bottom: 2px solid var(--light-peach);
        padding: 15px 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: var(--shadow-sm);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: nowrap;
        gap: 10px;
    }

    .toolbar .toolbar-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--primary-orange);
        margin: 0;
    }

    .toolbar .toolbar-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: nowrap;
    }

    .toolbar .btn {
        border-radius: 6px;
        padding: 8px 16px;
        font-weight: 500;
        white-space: nowrap;
    }

    /* ========== Custom Select Styling for Filters (Global) ========== */
    .filter-select-orange {
        min-width: 120px;
        max-width: 140px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .filter-select-orange {
        background-color: #FF6500;
        border: none;
        color: white;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    }

    .filter-select-orange:hover {
        background-color: #ff7a3d;
        box-shadow: 0 4px 12px rgba(255, 91, 34, 0.3);
    }

    .filter-select-orange:focus {
        background-color: #FF6500;
        border-color: #FF6500;
        box-shadow: 0 0 0 0.25rem rgba(255, 91, 34, 0.25);
    }

    /* Style select dropdown options */
    .filter-select-orange option {
        background-color: white;
        color: #333;
        padding: 8px 12px;
    }

    /* ========== Table Styles ========== */
    /* Table Container with Scroll */
    .table-container {
        width: 100%;
        overflow-x: auto;
        overflow-y: auto;
        max-height: 600px;
        border-radius: 8px;
        box-shadow: var(--shadow-sm);
        background-color: white;
    }

    .table-container::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .table-container::-webkit-scrollbar-track {
        background: var(--light-peach);
        border-radius: 4px;
    }

    .table-container::-webkit-scrollbar-thumb {
        background: var(--secondary-orange);
        border-radius: 4px;
    }

    .table-container::-webkit-scrollbar-thumb:hover {
        background: var(--primary-orange);
    }

    /* ========== Table Primary (Orange Theme) ========== */
    /* Override Bootstrap's default table-primary color (ต้องใช้ specificity สูงเพื่อ override Bootstrap) */

    /* พื้นหลังตาราง - สีครีมอ่อน */
    table.table-primary,
    .table.table-primary,
    table.table-primary tbody,
    .table.table-primary tbody {
        background-color: #FCF9EA !important;
        /* สีครีมอ่อน - พื้นหลังตาราง */
    }

    table.table-primary,
    .table.table-primary {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate !important;
        /* ต้องเป็น separate เพื่อให้ border-radius ทำงาน */
        border-spacing: 0 !important;
        /* ลบช่องว่างระหว่างเซลล์ */
        border-radius: 12px !important;
        /* มุมโค้งมน */
        overflow: hidden !important;
        /* ซ่อนส่วนที่เกินเพื่อให้ border-radius ทำงาน */
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
        /* เงาเบาๆ ให้ตารางดูนุ่มนวล */
    }

    /* Override Bootstrap's blue background (#cfe2ff) */
    table.table-primary> :not(caption)>*>*,
    .table-primary> :not(caption)>*>* {
        background-color: transparent !important;
        /* ให้ใช้สีจาก tr แทน */
    }

    /* หัวตาราง - Gradient สีส้ม */
    table.table-primary thead,
    .table-primary thead,
    table.table-primary thead tr,
    .table-primary thead tr {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange)) !important;

        /* Gradient ส้ม */
        color: white !important;
        /* ตัวอักษรสีขาว */
    }

    table.table-primary thead {
        position: sticky;
        /* ติดหัวตารางเมื่อ scroll */
        top: 0;
        z-index: 10;
    }

    /* เซลล์หัวตาราง (th) */
    table.table-primary thead th,
    .table-primary thead th {
        padding: 15px;
        /* ระยะห่างด้านใน */
        text-align: left;
        /* จัดตัวอักษรชิดซ้าย */
        font-weight: 600;
        /* ตัวอักษรหนา */

        /* เส้นล่างสีส้มรอง */
        background-color: transparent !important;
        /* ให้ใช้สีจาก thead */
        color: white !important;
    }

    /* มุมโค้งมนสำหรับ th มุมซ้ายบน */
    table.table-primary thead th:first-child,
    .table-primary thead th:first-child {
        border-top-left-radius: 12px !important;
    }

    /* มุมโค้งมนสำหรับ th มุมขวาบน */
    table.table-primary thead th:last-child,
    .table-primary thead th:last-child {
        border-top-right-radius: 12px !important;
    }

    /* แถวในตาราง (tbody) */
    table.table-primary tbody tr,
    .table-primary tbody tr td {
        background-color: transparent !important;
        /* ใช้สีจาก tbody */
        cursor: pointer;
        /* เปลี่ยน cursor เป็นมือเมื่อ hover */
        /* เส้นแบ่งแถวสีส้มรองอ่อน 25% opacity */
        border-bottom: 1px solid rgba(255, 145, 48, 0.25) !important;
    }



    /* Hover แถวในตาราง */
    table.table-primary tbody tr:hover,
    .table-primary tbody tr:hover,
    table.table-primary tbody tr:hover>*,
    .table-primary tbody tr:hover>* {
        background-color: var(--light-peach) !important;
        /* พื้นหลังสีครีมส้มเมื่อ hover */
    }

    /* Override Bootstrap's hover state for table-primary */
    table.table-primary.table-hover tbody tr:hover,
    .table-primary.table-hover tbody tr:hover,
    table.table-primary.table-hover tbody tr:hover>*,
    .table-primary.table-hover tbody tr:hover>* {
        --bs-table-accent-bg: var(--light-peach) !important;
        background-color: var(--light-peach) !important;
        color: var(--text-dark) !important;
    }

    /* Force override any Bootstrap hover colors */
    table.table-primary tbody tr:hover td,
    .table-primary tbody tr:hover td,
    table.table-primary tbody tr:hover th,
    .table-primary tbody tr:hover th {
        background-color: var(--light-peach) !important;
        color: var(--text-dark) !important;
    }

    /* เซลล์ในตาราง (td) */
    table.table-primary tbody td,
    .table-primary tbody td {
        padding: 12px 15px;
        /* ระยะห่างด้านใน */
        color: var(--text-dark) !important;
        /* สีตัวอักษรเทาเข้ม */
        background-color: transparent !important;
        /* ใช้สีจาก tr */
    }

    /* แถวคู่ - สีสลับ */
    table.table-primary tbody tr:nth-child(even),
    .table-primary tbody tr:nth-child(even),
    table.table-primary tbody tr:nth-child(even)>*,
    .table-primary tbody tr:nth-child(even)>* {
        background-color: #fcf8f4 !important;
        /* สีครีมอ่อนกว่าเล็กน้อยสำหรับแถวคู่ */
    }

    /* มุมโค้งมนสำหรับแถวสุดท้าย */
    table.table-primary tbody tr:last-child td:first-child,
    .table-primary tbody tr:last-child td:first-child {
        border-bottom-left-radius: 12px !important;
    }

    table.table-primary tbody tr:last-child td:last-child,
    .table-primary tbody tr:last-child td:last-child {
        border-bottom-right-radius: 12px !important;
    }

    /* ลบ border-bottom ของแถวสุดท้าย */
    table.table-primary tbody tr:last-child,
    .table-primary tbody tr:last-child {
        border-bottom: none !important;
    }

    /* Table Secondary (Dark Blue Theme) */
    .table-secondary {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    .table-secondary thead {
        background: linear-gradient(135deg, #1E3E62, #2d5a8a);
        color: white;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table-secondary thead th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #2d5a8a;
    }

    .table-secondary .table-sm {
        border-radius: 12px;
    }

    .table-secondary tbody tr {
        cursor: pointer;
        border-bottom: 1px solid #e0e0e0;
    }

    .table-secondary tbody tr:hover {
        background-color: #e3f2fd;
    }

    .table-secondary tbody tr td {
        padding: 12px 15px;
        border-bottom: 1px solid #A9A9A9;
        color: var(--text-dark);
    }

    .table-secondary tbody tr:nth-child(even) {
        background-color: #f5f5f5;
    }

    /* Clickable Row Style */
    .table tbody tr.clickable-row {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .table tbody tr.clickable-row:hover {
        background-color: var(--light-peach) !important;
        /* สีครีมส้มเมื่อ hover */
    }

    .table tbody tr.clickable-row:hover td,
    .table tbody tr.clickable-row:hover th {
        background-color: var(--light-peach) !important;
        /* ให้ทุกเซลล์เป็นสีเดียวกัน */
    }

    .table tbody tr.clickable-row:active {
        background-color: var(--secondary-orange);
        color: white;
    }

    /* Table Responsive Text */
    .table td,
    .table th {
        white-space: nowrap;
    }

    /* Table Status Badges */
    .table .badge {
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    /* snackbar */
    .snackbar {
        visibility: hidden;
        min-width: 250px;
        margin-left: -125px;
        background-color: #dc3545;
        color: #fff;
        text-align: center;
        border-radius: 8px;
        padding: 16px;
        position: fixed;
        z-index: 99999 !important;
        right: 20px;
        bottom: 30px;
        font-size: 16px;
        box-shadow: 0 4px 12px rgba(255, 91, 34, 0.4);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .snackbar.show {
        visibility: visible;
        animation: fadein 0.5s, fadeout 0.5s 10s;
    }

    .snackbar button {
        background: none;
        border: none;
        color: #fff;
        font-weight: bold;
        margin-left: 10px;
        cursor: pointer;
    }

    @keyframes fadein {
        from {
            bottom: 0;
            opacity: 0;
        }

        to {
            bottom: 30px;
            opacity: 1;
        }
    }

    @keyframes fadeout {
        from {
            bottom: 30px;
            opacity: 1;
        }

        to {
            bottom: 0;
            opacity: 0;
        }
    }

    /* ========== Notification Dropdown Styles ========== */
    .notifications-list {
        min-width: 450px !important;
        max-width: 500px !important;
        max-height: 600px;
        overflow-y: auto;
        padding: 0;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        left: 50% !important;
        right: auto !important;
        transform: translateX(-50%) !important;
    }

    .notifications-list .dropdown-header {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: white;
        padding: 15px 20px;
        font-weight: 600;
        border-radius: 8px 8px 0 0;
        font-size: 1rem;
    }

    .notification-item {
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s;
    }

    .notification-item:hover {
        background-color: var(--light-peach);
    }

    .notification-item.unread {
        background-color: #fff3e0;
        border-left: 3px solid var(--primary-orange);
    }

    .notification-content strong {
        color: #FF6500;
        font-size: 1rem;
        font-weight: 600;
    }

    .notification-content .badge {
        font-size: 0.7rem;
        padding: 3px 8px;
        font-weight: 600;
    }

    .notification-content .text-secondary {
        font-size: 0.9rem;
        line-height: 1.4;
        color: #666;
    }

    .notification-content small {
        font-size: 0.8rem;
        margin-top: 4px;
        display: inline-block;
        color: #999;
    }

    .notifications-list .text-center {
        padding: 30px 20px;
        color: #999;
    }

    .notifications-list .text-center i {
        font-size: 2.5rem;
        margin-bottom: 10px;
        display: block;
        opacity: 0.5;
    }

    .notifications-toggle {
        position: relative;
    }

    .notifications-toggle .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        padding: 3px 6px;
        font-size: 0.7rem;
    }

    /* ========== Modal Styles ========== */
    /* Modal backdrop - พื้นหลังโปร่งแสง */
    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.5) !important;
        /* ดำโปร่งแสง 50% */
    }

    /* Modal content - กล่อง modal */
    .modal-content {
        border: none !important;
        border-radius: 12px !important;
        /* มุมโค้งมน */
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15) !important;
        /* เงานุ่มนวล */
        overflow: hidden !important;
        /* ให้ border-radius ทำงาน */
    }

    /* Modal header - หัว modal */
    .modal-header {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange)) !important;
        /* Gradient สีส้ม */
        color: white !important;
        border-bottom: none !important;
        padding: 20px 25px !important;
    }

    .modal-header .modal-title {
        font-weight: 600 !important;
        font-size: 1.25rem !important;
        color: white !important;
    }

    .modal-header .btn-close {
        background-color: rgba(255, 255, 255, 0.2) !important;
        border-radius: 50% !important;
        opacity: 1 !important;
        padding: 0.5rem !important;
        transition: all 0.3s ease !important;
    }

    .modal-header .btn-close:hover {
        background-color: rgba(255, 255, 255, 0.3) !important;
        transform: rotate(90deg) !important;
        /* หมุน 90 องศาเมื่อ hover */
    }

    /* Modal body - เนื้อหา modal */
    .modal-body {
        padding: 25px !important;
        background: linear-gradient(#FCF9EA, #ffffff);
        max-height: 70vh !important;
        /* จำกัดความสูงไม่เกิน 70% ของหน้าจอ */
        overflow-y: auto !important;
        /* scroll ได้ถ้าเนื้อหายาว */
    }

    /* Scrollbar ใน modal body */
    .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #f0f0f0;
        border-radius: 4px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: var(--light-peach);
        border-radius: 4px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: var(--secondary-orange);
    }

    /* Modal footer - ท้าย modal */
    .modal-footer {
        background-color: #F9F5F0 !important;
        border-top: 1px solid rgba(254, 205, 166, 0.3) !important;
        /* เส้นบนสีส้มอ่อน */
        padding: 15px 25px !important;
    }

    /* ปุ่มใน modal */
    .modal-footer .btn {
        border-radius: 6px !important;
        padding: 8px 20px !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }

    .modal-footer .btn-primary {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange)) !important;
        border: none !important;
    }

    .modal-footer .btn-primary:hover {
        box-shadow: 0 4px 12px rgba(255, 91, 34, 0.4) !important;
        transform: translateY(-2px) !important;
    }

    .modal-footer .btn-warning {
        background: linear-gradient(135deg, #ffc107, #ffb300) !important;
        border: none !important;
        color: #333 !important;
    }

    .modal-footer .btn-warning:hover {
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4) !important;
        transform: translateY(-2px) !important;
    }

    .modal-footer .btn-secondary {
        background-color: #6c757d !important;
        border: none !important;
    }

    .modal-footer .btn-secondary:hover {
        background-color: #5a6268 !important;
    }

    /* ตารางใน modal */
    .modal-body .table {
        margin-bottom: 0 !important;
    }

    .modal-body .table tr td:first-child {
        font-weight: 600 !important;
        color: var(--text-dark) !important;
        width: 40% !important;
    }

    /* Form ใน modal */
    .modal-body .form-label {
        font-weight: 600 !important;
        color: var(--text-dark) !important;
        margin-bottom: 8px !important;
    }

    .modal-body .form-control,
    .modal-body .form-select {
        border: 1px solid rgba(254, 205, 166, 0.5) !important;
        border-radius: 6px !important;
        padding: 10px 12px !important;
        transition: all 0.3s ease !important;
    }

    .modal-body .form-control:focus,
    .modal-body .form-select:focus {
        border-color: var(--primary-orange) !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 91, 34, 0.15) !important;
    }

    /* Animation เมื่อเปิด modal */
    .modal.fade .modal-dialog {
        transition: transform 0.3s ease-out !important;
    }

    .modal.show .modal-dialog {
        transform: none !important;
    }

    .list-group-item {
        border: none !important;
        border-bottom: 1px solid rgba(108, 108, 108, 0.5) !important;
        border-top: 1px solid rgba(108, 108, 108, 0.5) !important;
    }

    .btn-info {
    border-radius: 8px !important; /* มนขึ้นนิดหน่อย */
    color: #fff !important;
    transition: all 0.2s ease-in-out;
}
</style>

<script>
    window.onload = function() {
        const sb = document.getElementById("snackbar");
        const sbMsg = document.getElementById("snackbarMessage");

        @if (session('success'))
            sbMsg.innerText = "{{ session('success') }}";
            sb.style.backgroundColor = "#28a745"; // เขียว
            sb.style.display = "flex";
            sb.classList.add("show");
            setTimeout(() => {
                sb.classList.remove("show");
                sb.style.display = "none";
            }, 10500);
        @elseif (session('error'))
            sbMsg.innerText = "{{ session('error') }}";
            sb.style.backgroundColor = "#dc3545"; // แดง
            sb.style.display = "flex";
            sb.classList.add("show");
            setTimeout(() => {
                sb.classList.remove("show");
                sb.style.display = "none";
            }, 10500);
        @endif
    };

    function showSnackbar(message, bgColor = "#dc3545") {
        const sb = document.getElementById("snackbar");
        const sbMsg = document.getElementById("snackbarMessage");
        sbMsg.innerText = message;
        sb.style.backgroundColor = bgColor;
        sb.style.display = "flex";
        sb.classList.add("show");
        setTimeout(() => {
            sb.classList.remove("show");
            sb.style.display = "none";
        }, 5000);
    }

    function copySnackbar() {
        let text = document.getElementById("snackbarMessage").innerText;
        navigator.clipboard.writeText(text).then(() => {
            let btn = document.getElementById("copyBtn");
            btn.innerHTML = '<i class="bi bi-check2"></i> Copied';
            btn.disabled = true;
            setTimeout(() => {
                btn.innerHTML = '<i class="bi bi-copy"></i>';
                btn.disabled = false;
            }, 2000);
        });
    }

    function closeSnackbar() {
        let sb = document.getElementById("snackbar");
        sb.classList.remove("show");
        sb.style.display = "none";
    }

    // ========== Sidebar Active Menu Highlight ==========
    document.addEventListener('DOMContentLoaded', function() {
        const currentUrl = window.location.href;
        const sidebarLinks = document.querySelectorAll('#sidebar a');

        sidebarLinks.forEach(link => {
            // เช็คว่า link href ตรงกับ current URL หรือไม่
            if (link.href === currentUrl) {
                link.classList.add('active');

                // เปิด parent dropdown ถ้ามี
                let parentCollapse = link.closest('.collapse');
                if (parentCollapse) {
                    parentCollapse.classList.add('show');

                    // เปิด parent dropdown ที่ 2 ถ้ามี (nested)
                    let parentCollapse2 = parentCollapse.closest('li').closest('.collapse');
                    if (parentCollapse2) {
                        parentCollapse2.classList.add('show');
                    }
                }

                // เพิ่ม class active ให้ parent li
                let parentLi = link.closest('li');
                while (parentLi) {
                    if (parentLi.parentElement.id === 'sidebar' || parentLi.parentElement.classList
                        .contains('list-unstyled')) {
                        parentLi.classList.add('active');
                    }
                    parentLi = parentLi.parentElement.closest('li');
                }
            }
        });
    });
</script>
