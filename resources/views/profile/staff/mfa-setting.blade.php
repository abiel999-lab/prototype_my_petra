<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Settings</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="https://my.petra.ac.id/adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet"
        href="https://my.petra.ac.id/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://my.petra.ac.id/adminlte/dist/css/adminlte.min.css">
    <link rel="shortcut icon" href="https://login.petra.ac.id/images/favicon.png" type="image/x-icon">
    <style>
        @media (min-width: 768px) {
            .col-md-9 {
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
        }

        .card-body {
            padding: 20px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        label:not(.form-check-label):not(.custom-file-label) {
            font-weight: 700;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="https://my.petra.ac.id/img/logo.png" alt="Gate">
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('staff.dashboard') }}" class="nav-link">Gate</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="fas fa-user"></i> {{ strtoupper(auth()->user()->name) }}
                        <i class="fas fa-caret-down"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="{{ route('profile.staff.setting') }}" class="dropdown-item">Setting</a>
                        <a href="{{ route('logout') }}" class="dropdown-item"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('staff.dashboard') }}" class="brand-link">
                <img src="https://my.petra.ac.id/img/logo.png" alt="Gate" class="brand-image img-circle elevation-3"
                    style="opacity: .8">
                <span class="brand-text font-weight-light">Gate</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="https://my.petra.ac.id/img/user.png" class="img-circle elevation-2">
                    </div>
                    <div class="info">
                        <a href="{{ route('profile.staff.setting') }}"
                            class="d-block">{{ strtoupper(auth()->user()->name) }}</a>
                    </div>
                </div>

                <!-- SidebarSearch Form -->
                <div class="form-inline">
                    <div class="input-group" data-widget="sidebar-search">
                        <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                            aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-sidebar">
                                <i class="fas fa-search fa-fw"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">

                        <li class="nav-item">
                            <a href="{{ route('profile.staff.profile') }}" class="nav-link">
                                <i class="nav-icon fas fa-user"></i>
                                <p>
                                    Profile
                                </p>
                            </a>
                        </li>



                        <li class="nav-item">
                            <a href="{{ route('profile.staff.session.show') }}" class="nav-link ">
                                <i class="nav-icon fas fa-stopwatch"></i>
                                <p>
                                    Session
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('profile.staff.mfa') }}" class="nav-link active">
                                <i class="nav-icon fas fa-shield-alt"></i>
                                <p>
                                    MFA
                                </p>
                            </a>
                        </li>

                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Multi-Factor Authentication</h1>
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">Setting</li>
                                <li class="breadcrumb-item active">MFA</li>
                            </ol>
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Small boxes (Stat box) -->
                    <div class="row">
                        <div class="col-md-9">
                            <div class="card">
                                <div class="card-header p-2">
                                    <ul class="nav nav-pills">
                                        <li class="nav-item active"><a class="nav-link" href="#tab_activation"
                                                data-toggle="tab">Activation</a></li>
                                        <li class="nav-item"><a class="nav-link" href="#tab_manage"
                                                data-toggle="tab">Manage Device</a></li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">

                                        <div class="tab-pane" id="tab_activation">
                                            {{-- MFA Toggle --}}
                                            <label for="mfa-toggle" style="margin-right: 20px;">Enable MFA</label>
                                            <label class="switch">
                                                <input type="checkbox" id="mfa-toggle"
                                                    {{ auth()->user()->mfa_enabled ? 'checked' : '' }}>
                                                <span class="slider"></span>
                                            </label>

                                            <form id="mfa-method-form">
                                                <label for="mfa_method">Choose Authentication Method:</label>
                                                <select name="mfa_method" id="mfa_method"
                                                    style="margin-bottom: 20px;" class="form-control" required>
                                                    <option value="email"
                                                        {{ auth()->user()->mfa_method === 'email' ? 'selected' : '' }}>
                                                        Email (OTP code sent through Email)</option>
                                                    <option value="google_auth"
                                                        {{ auth()->user()->mfa_method === 'google_auth' ? 'selected' : '' }}>
                                                        Google Authenticator</option>
                                                </select>
                                                <button type="submit" class="btn btn-primary save-btn">Save</button>
                                                <p style="margin-top: 1rem;">If you are using Google Auth app, click save to look at your QRCode</p>
                                            </form>

                                            {{-- QR Code Display --}}
                                            <div id="qr-code-container"
                                                class="mt-4 p-4 border border-gray-300 rounded bg-gray-50"
                                                style="display: none;">
                                                <p><b>Guide:</b></p>
                                                <p>1. Install Google Authentication app on Playstore</p>
                                                <img src="{{ asset('images/google_auth.jpg') }}"
                                                    alt="Google Authenticator app"
                                                    style="width: 250px; height: auto;">
                                                <p style="margin-top: 1rem;">2. Login using Google account</p>
                                                <p>3. Scan this QR code with your Google Authenticator app:</p>
                                                <img id="qr-code-image" src="" alt="QR Code">
                                                <p class="mt-2 text-sm text-gray-600">
                                                    After scanning the QR code, use the Google Authenticator app to
                                                    generate codes for login.
                                                </p>
                                            </div>

                                        </div>

                                        <div class="tab-pane" id="tab_manage">

                                        </div>




                                        <footer class="main-footer">
                                            <strong>Copyright &copy; 2023 <a href="https://petra.ac.id">Petra Christian
                                                    University</a>.</strong>
                                            All rights reserved.
                                            <div class="float-right d-none d-sm-inline-block">
                                                Pusat Pengembangan Sistem Informasi <span>version: v1.0.18</span>
                                            </div>
                                        </footer>
                                    </div>
                                    <!-- ./wrapper -->

                                    <!-- jQuery -->
                                    <script src="https://my.petra.ac.id/adminlte/plugins/jquery/jquery.min.js"></script>
                                    <!-- jQuery UI 1.11.4 -->
                                    <script src="https://my.petra.ac.id/adminlte/plugins/jquery-ui/jquery-ui.min.js"></script>
                                    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
                                    <script>
                                        $.widget.bridge('uibutton', $.ui.button)
                                    </script>
                                    <!-- Bootstrap 4 -->
                                    <script src="https://my.petra.ac.id/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
                                    <!-- overlayScrollbars -->
                                    <script src="https://my.petra.ac.id/adminlte/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
                                    <!-- AdminLTE App -->
                                    <script src="https://my.petra.ac.id/adminlte/dist/js/adminlte.js"></script>
                                    <script src="https://my.petra.ac.id/adminlte/plugins/sweetalert2/sweetalert2.min.js"></script>
                                    <script type="text/javascript">
                                        const Toast = Swal.mixin({
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            showCloseButton: true,
                                            timer: 5000
                                        });





                                        function showLoading() {
                                            Swal.fire({
                                                title: 'Loading ...',
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                allowEnterKey: false,
                                                didOpen: () => {
                                                    Swal.showLoading()
                                                },
                                            });
                                        }
                                    </script>
                                    <script type="text/javascript">
                                        $(function() {
                                            $('[data-toggle="tooltip"]').tooltip()
                                        })
                                    </script>
                                    <script type="text/javascript">
                                        $(function() {
                                            var tab = ``;
                                            if (tab) {
                                                $(`a[href="#tab_${tab}"]`).addClass('active');
                                                $(`#tab_${tab}`).addClass('active');
                                            } else {
                                                $(`a[href="#tab_activation"]`).addClass('active');
                                                $(`#tab_activation`).addClass('active');
                                            }
                                        });
                                    </script>
                                    <script>
                                        // Handle MFA method selection and display QR code
                                        document.getElementById('mfa-method-form').addEventListener('submit', function(e) {
                                            e.preventDefault(); // Prevent default form submission

                                            const formData = new FormData(this);

                                            fetch("{{ route('set-mfa-method') }}", {
                                                    method: 'POST',
                                                    headers: {
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    },
                                                    body: formData,
                                                })
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.status === 'success') {
                                                        alert(data.message);

                                                        // Display QR code if Google Authenticator is selected
                                                        if (data.qrCodeUrl) {
                                                            const qrCodeContainer = document.getElementById('qr-code-container');
                                                            const qrCodeImage = document.getElementById('qr-code-image');

                                                            qrCodeImage.src =
                                                                `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(data.qrCodeUrl)}&size=200x200`;
                                                            qrCodeContainer.style.display = 'block';
                                                        } else {
                                                            // Hide QR code if Email is selected
                                                            document.getElementById('qr-code-container').style.display = 'none';
                                                        }
                                                    } else {
                                                        alert('Failed to update MFA method.');
                                                    }
                                                })
                                                .catch(error => console.error('Error:', error));
                                        });

                                        // Handle MFA toggle
                                        document.getElementById('mfa-toggle').addEventListener('change', function() {
                                            fetch("{{ route('toggle-mfa') }}", {
                                                    method: 'POST',
                                                    headers: {
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                        'Content-Type': 'application/json',
                                                    },
                                                    body: JSON.stringify({}),
                                                })
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.status === 'success') {
                                                        alert(`MFA is now ${data.mfa_enabled ? 'enabled' : 'disabled'}.`);
                                                    } else {
                                                        alert('Failed to toggle MFA.');
                                                    }
                                                })
                                                .catch(error => console.error('Error:', error));
                                        });
                                    </script>
</body>

</html>
