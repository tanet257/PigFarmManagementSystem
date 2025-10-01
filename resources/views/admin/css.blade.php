<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Dark Bootstrap Admin </title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <!-- Bootstrap CSS จาก Bootswatch Cyborg -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/darkly/bootstrap.min.css">

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


    <!-- theme stylesheet-->
    <link rel="stylesheet" href="admin/css/style.violet.css" id="theme-stylesheet">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="admin/css/custom.css">
    <!-- Favicon-->
    <link rel="shortcut icon" href="admin/img/favicon.ico">
    <!-- Tweaks for older IEs--><!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->


    <style>
        /* snackbar */
        .snackbar {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 16px;
            position: fixed;
            z-index: 9999;
            right: 20px;
            bottom: 30px;
            font-size: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
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
