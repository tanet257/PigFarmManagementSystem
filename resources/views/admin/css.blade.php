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
        --warning: #ffc107;
        --info: #17a2b8;
    }

    /* ========== Global Styles ========== */
    body {
        background-color: var(--body-bg);
        color: var(--text-dark);
        font-family: 'Muli', sans-serif;
    }

    /* Cards */
    .card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: var(--text-light);
        border-bottom: none;
        font-weight: 600;
    }

    /* Buttons */
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        border: none;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--secondary-orange), var(--primary-orange));
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 91, 34, 0.3);
    }

    .btn-secondary {
        background-color: var(--gray);
        border: none;
    }

    .btn-secondary:hover {
        background-color: #8a8a8a;
    }

    /* Sidebar */
    .sidebar {
        background-color: var(--sidebar-bg);
        color: var(--text-light);
    }

    .sidebar .sidebar-link {
        color: var(--text-light);
        transition: all 0.3s ease;
    }

    .sidebar .sidebar-link:hover {
        background-color: var(--primary-orange);
        padding-left: 20px;
    }

    .sidebar .sidebar-link.active {
        background: linear-gradient(90deg, var(--primary-orange), var(--secondary-orange));
        border-left: 4px solid var(--light-peach);
    }

    /* Header */
    .page-header {
        background-color: var(--header-bg);
        border-bottom: 2px solid var(--light-peach);
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
        border: 1px solid var(--border-color);
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-orange);
        box-shadow: 0 0 0 0.2rem rgba(255, 91, 34, 0.25);
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

    /* Dropdown */
    .dropdown-menu {
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-md);
    }

    .dropdown-item:hover {
        background-color: var(--light-peach);
        color: var(--text-dark);
    }

    /* ========== Custom Cards ========== */
    .card-custom {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: white;
        border: none;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(255, 91, 34, 0.2);
        transition: all 0.3s ease;
    }

    .card-custom:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(255, 91, 34, 0.3);
    }

    .card-custom-secondary {
        background: linear-gradient(135deg, #1E3E62, #2d5a8a);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(30, 62, 98, 0.2);
        transition: all 0.3s ease;
    }

    .card-custom-secondary:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(30, 62, 98, 0.3);
    }

    .card-custom-tertiary {
        background: linear-gradient(135deg, var(--light-peach), #ffffff);
        color: var(--text-dark);
        border: 2px solid var(--secondary-orange);
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(254, 205, 166, 0.2);
        transition: all 0.3s ease;
    }

    .card-custom-tertiary:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(254, 205, 166, 0.3);
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
        flex-wrap: wrap;
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
    }

    .toolbar .btn {
        border-radius: 6px;
        padding: 8px 16px;
        font-weight: 500;
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

    /* Table Primary (Orange Theme) */
    .table-primary {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    .table-primary thead {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: white;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table-primary thead th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid var(--secondary-orange);
    }

    .table-primary tbody tr {
        transition: all 0.3s ease;
        cursor: pointer;
        border-bottom: 1px solid var(--light-peach);
    }

    .table-primary tbody tr:hover {
        background-color: var(--light-peach);
        transform: scale(1.01);
    }

    .table-primary tbody td {
        padding: 12px 15px;
        color: var(--text-dark);
    }

    .table-primary tbody tr:nth-child(even) {
        background-color: #fcf8f4;
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

    .table-secondary tbody tr {
        transition: all 0.3s ease;
        cursor: pointer;
        border-bottom: 1px solid #e0e0e0;
    }

    .table-secondary tbody tr:hover {
        background-color: #e3f2fd;
        transform: scale(1.01);
    }

    .table-secondary tbody td {
        padding: 12px 15px;
        color: var(--text-dark);
    }

    .table-secondary tbody tr:nth-child(even) {
        background-color: #f5f5f5;
    }

    /* Clickable Row Style */
    .table tbody tr.clickable-row {
        cursor: pointer;
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
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: #fff;
        text-align: center;
        border-radius: 8px;
        padding: 16px;
        position: fixed;
        z-index: 9999;
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
</script>
