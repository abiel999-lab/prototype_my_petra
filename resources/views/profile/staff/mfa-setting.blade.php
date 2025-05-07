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

        /* Improve button spacing & alignment */
        .btn-group form {
            display: inline-block;
            margin-right: 5px;
        }

        /* Button improvements */
        .btn {
            padding: 6px 12px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        /* Smooth hover effect */
        .btn:hover {
            opacity: 0.85;
        }

        /* Rounded buttons for a modern look */
        .btn-danger,
        .btn-success,
        .btn-warning {
            border-radius: 6px;
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
                {{-- 🔁 Switch Role Dropdown --}}
                @php
                    $user = auth()->user();
                    $activeRole = session('active_role', $user->usertype);
                    $roles = $user->roles->pluck('name')->toArray(); // dari relasi many-to-many
                @endphp

                @if (!empty($roles))
                    <li class="nav-item dropdown" style="margin-right: 10px">
                        <a class="nav-link btn btn-outline-secondary" data-toggle="dropdown" href="#">
                            <i class="fas fa-random"></i> {{ strtoupper($activeRole) }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <form action="{{ route('role.switch.update') }}" method="POST">
                                @csrf

                                {{-- Default role (usertype) --}}
                                <button type="submit" name="role" value="{{ $user->usertype }}"
                                    class="dropdown-item {{ $activeRole === $user->usertype ? 'active' : '' }}">
                                    <i class="fas fa-user-check"></i> Return to {{ ucfirst($user->usertype) }}
                                </button>

                                <div class="dropdown-divider"></div>

                                {{-- Role tambahan dari tabel role_user --}}
                                @foreach ($roles as $role)
                                    @if ($role !== $user->usertype)
                                        <button type="submit" name="role" value="{{ $role }}"
                                            class="dropdown-item {{ $activeRole === $role ? 'active' : '' }}">
                                            @if ($role === 'student')
                                                <i class="fas fa-user-graduate"></i>
                                            @elseif ($role === 'staff')
                                                <i class="fas fa-user-tie"></i>
                                            @elseif ($role === 'admin')
                                                <i class="fas fa-user-shield"></i>
                                            @else
                                                <i class="fas fa-users"></i>
                                            @endif
                                            {{ ucfirst($role) }} View
                                        </button>
                                    @endif
                                @endforeach
                            </form>
                        </div>
                    </li>
                @endif
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="fas fa-user"></i> {{ strtoupper(auth()->user()->name) }}
                        <i class="fas fa-caret-down"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="{{ route('profile.staff.setting') }}" class="dropdown-item">Setting</a>
                        <a href="{{ route('logout') }}" class="dropdown-item"
                            onclick="event.preventDefault(); confirmLogout();">Logout</a>
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
                                    Security
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('customer-support') }}" class="nav-link ">
                                <i class="nav-icon fa fa-question-circle"></i>
                                <p>
                                    Support
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
                                                    {{ auth()->user()->mfa && auth()->user()->mfa->mfa_enabled ? 'checked' : '' }}>
                                                <span class="slider"></span>
                                            </label>

                                            <form id="mfa-method-form">
                                                <label for="mfa_method">Choose Authentication Method:</label>
                                                <select name="mfa_method" id="mfa_method"
                                                    style="margin-bottom: 20px;" class="form-control" required>
                                                    <option value="email"
                                                        {{ auth()->user()->mfa && auth()->user()->mfa->mfa_method === 'email' ? 'selected' : '' }}>
                                                        Email
                                                    </option>
                                                    <option value="google_auth"
                                                        {{ auth()->user()->mfa && auth()->user()->mfa->mfa_method === 'google_auth' ? 'selected' : '' }}>
                                                        Mobile Authenticator
                                                    </option>
                                                    <option value="whatsapp"
                                                        {{ auth()->user()->mfa && auth()->user()->mfa->mfa_method === 'whatsapp' ? 'selected' : '' }}>
                                                        WhatsApp
                                                    </option>
                                                    <option value="sms"
                                                        {{ auth()->user()->mfa && auth()->user()->mfa->mfa_method === 'sms' ? 'selected' : '' }}>
                                                        SMS (not recommended)
                                                    </option>
                                                </select>


                                                <button type="submit" class="btn btn-primary save-btn">Save</button>
                                                <p style="margin-top: 1rem;">If you are using Google Auth app, click
                                                    save to look at your QRCode</p>
                                            </form>

                                            {{-- QR Code Display --}}
                                            <div id="qr-code-container"
                                                class="mt-4 p-4 border border-gray-300 rounded bg-gray-50"
                                                style="display: none;">
                                                <p class="important">IMPORTANT!!!!</p>
                                                <style>
                                                    .important {
                                                        font-size: 24px;
                                                        font-weight: bold;
                                                        color: red;
                                                        text-transform: uppercase;
                                                        background-color: yellow;
                                                        padding: 10px;
                                                        border: 2px solid red;
                                                        display: inline-block;
                                                        animation: blink 1s infinite alternate;
                                                    }

                                                    @keyframes blink {
                                                        0% {
                                                            opacity: 1;
                                                        }

                                                        100% {
                                                            opacity: 0.5;
                                                        }
                                                    }
                                                </style>

                                                <p><b>Guide:</b></p>
                                                <p>1. Install Google/Microsoft Authenticator from Playstore/Appstore</p>
                                                <img src="https://studioimpactid.com/wp-content/uploads/2025/04/google_auth.jpg"
                                                    alt="Google Authenticator app"
                                                    style="width: 250px; height: auto;">
                                                <p>2. Login using Google account</p>
                                                <p>3. Scan the QR code below with your Authenticator app:</p>

                                                <img id="qr-code-image" src="" alt="QR Code"
                                                    style="display: none;" />

                                                <p class="mt-2 text-sm text-gray-600">
                                                    After scanning the QR code, enter the 6-digit OTP code here:
                                                </p>

                                                <input type="text" name="otp" id="otp"
                                                    placeholder="Enter OTP" class="form-control mt-2"
                                                    style="display: none;" />
                                                <button type="button" id="verify-google-auth"
                                                    class="btn btn-success mt-2"
                                                    style="display: none;">Verify</button>
                                            </div>
                                            <br>
                                            <label for="passwordless_enabled" style="margin-right: 20px;">Enable
                                                Passwordless Login</label>
                                            <label class="switch">
                                                <input type="checkbox" id="passwordless_enabled"
                                                    {{ auth()->user()->mfa && auth()->user()->mfa->passwordless_enabled ? 'checked' : '' }}>
                                                <span class="slider"></span>
                                            </label>
                                            <p style="margin-top: 15px; font-size: 16px;">
                                                <strong>Apa itu Multi-Factor Authentication (MFA)?</strong><br>
                                                MFA menambahkan lapisan keamanan ekstra saat login. Setelah memasukkan password, Anda akan diminta memasukkan <strong>kode OTP</strong> yang dikirim melalui metode yang Anda pilih (Email, WhatsApp, SMS, atau Google Authenticator).<br>
                                                Tujuannya adalah untuk <strong>melindungi akun Anda jika password diketahui orang lain</strong>.

                                                <br><br>

                                                <strong>Apa itu Passwordless Login?</strong><br>
                                                Passwordless Login memungkinkan Anda <strong>login tanpa memasukkan password</strong>. Sistem akan langsung mengirimkan <strong>OTP melalui metode MFA</strong> untuk memverifikasi identitas Anda.<br>
                                                Fitur ini memberikan <strong>kemudahan tanpa mengorbankan keamanan</strong>, selama metode MFA Anda aktif dan dapat diakses.
                                            </p>

                                        </div>

                                        <div class="tab-pane" id="tab_manage">
                                            <div class="table-responsive">
                                                <table id="deviceTable"
                                                    class="table table-bordered table-hover text-center">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>IP Address</th>
                                                            <th>Device</th>
                                                            <th>OS</th>
                                                            <th class="d-none d-md-table-cell">Last Used</th>
                                                            <!-- Hidden on small screens -->
                                                            <th class="d-none d-md-table-cell">Status</th>
                                                            <!-- Hidden on small screens -->
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($devices as $index => $device)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ $device->ip_address }}</td>
                                                                <td>{{ $device->device ?? 'Unknown' }}</td>
                                                                <td>{{ $device->os ?? 'Unknown' }}</td>
                                                                <td class="d-none d-md-table-cell">
                                                                    {{ $device->updated_at->format('d M Y H:i') }}</td>
                                                                <td class="d-none d-md-table-cell">
                                                                    @if ($device->trusted)
                                                                        ✅ Trusted
                                                                    @else
                                                                        ❌ Not Trusted
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex justify-content-center"
                                                                        style="gap: 5px">
                                                                        <!-- Adds spacing between buttons -->
                                                                        <!-- Delete Device -->
                                                                        <form id="deleteForm-{{ $device->id }}"
                                                                            action="{{ route('profile.staff.mfa.delete', $device->id) }}"
                                                                            method="POST">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="button"
                                                                                onclick="confirmDelete('{{ $device->id }}')"
                                                                                class="btn btn-danger btn-sm">Delete</button>
                                                                        </form>

                                                                        <!-- Trust / Untrust Device -->
                                                                        @if ($device->trusted)
                                                                            <form
                                                                                action="{{ route('profile.staff.mfa.untrust', $device->id) }}"
                                                                                method="POST">
                                                                                @csrf
                                                                                <input type="hidden" name="device_id"
                                                                                    value="{{ $device->id }}">
                                                                                <button type="submit"
                                                                                    class="btn btn-warning btn-sm">Untrust</button>
                                                                            </form>
                                                                        @else
                                                                            <form
                                                                                action="{{ route('profile.staff.mfa.trust', $device->id) }}"
                                                                                method="POST">
                                                                                @csrf
                                                                                <input type="hidden" name="device_id"
                                                                                    value="{{ $device->id }}">
                                                                                <button type="submit"
                                                                                    class="btn btn-success btn-sm">Trust</button>
                                                                            </form>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <p style="margin-top: 15px; font-size: 16px;">
                                                <strong>Apa itu Manage Device?</strong><br>
                                                Fitur ini menampilkan daftar perangkat yang pernah digunakan untuk login ke akun Anda. Anda bisa <strong>menandai perangkat sebagai "Trusted"</strong> agar tidak diminta OTP berulang kali saat login.<br>
                                                Jika Anda melihat perangkat asing, <strong>hapus atau untrust segera</strong> untuk mencegah akses tidak sah. Fitur ini membantu Anda <strong>mengontrol keamanan dari sisi perangkat</strong>.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
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
        document.addEventListener("DOMContentLoaded", function() {
            const mfaForm = document.getElementById("mfa-method-form");
            const mfaSelect = document.getElementById("mfa_method");
            const qrCodeContainer = document.getElementById("qr-code-container");
            const qrCodeImage = document.getElementById("qr-code-image");
            const otpInput = document.getElementById("otp");
            const verifyButton = document.getElementById("verify-google-auth");
            const mfaToggle = document.getElementById("mfa-toggle");
            const smsWarning = document.getElementById("sms-warning");

            // 🔄 Submit method MFA
            mfaForm.addEventListener("submit", function(e) {
                e.preventDefault();
                const formData = new FormData(mfaForm);
                const selectedMethod = mfaSelect.value;

                fetch("{{ route('set-mfa-method') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        },
                        body: formData,
                    })
                    .then((response) => {
                        return response.json().then((data) => {
                            if (!response.ok) throw new Error(data.message || "Request failed");
                            return data;
                        });
                    })
                    .then((data) => {
                        if (selectedMethod === "google_auth") {
                            if (data.status === "pending") {
                                Swal.fire("Scan QR Code", data.message, "info");

                                if (data.qrCodeUrl) {
                                    const qrApi =
                                        `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(data.qrCodeUrl)}&size=200x200`;
                                    qrCodeImage.src = qrApi;
                                    qrCodeImage.style.display = "block";
                                }

                                qrCodeContainer.style.display = "block";
                                otpInput.style.display = "block";
                                otpInput.required = true;
                                verifyButton.style.display =
                                    "inline-block"; // ✅ tampilkan tombol verify
                            } else if (data.status === "success") {
                                Swal.fire("Success", data.message, "success");
                                hideQrSection();
                            } else {
                                Swal.fire("Error", data.message, "error");
                            }
                        } else {
                            Swal.fire("Success", data.message, "success");
                            hideQrSection();
                        }
                    })
                    .catch((error) => {
                        console.error("MFA Error:", error);
                        Swal.fire("Error", error.message || "Failed to update MFA.", "error");
                    });
            });

            // 🔐 Tombol VERIFIKASI OTP Google Authenticator
            verifyButton.addEventListener("click", function() {
                const formData = new FormData();
                formData.append("mfa_method", "google_auth");
                formData.append("otp", otpInput.value);

                fetch("{{ route('set-mfa-method') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        },
                        body: formData,
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.status === "success") {
                            Swal.fire("Verified!", data.message, "success");
                            hideQrSection();
                        } else {
                            Swal.fire("Error", data.message, "error");
                        }
                    })
                    .catch((error) => {
                        console.error("Verification Error:", error);
                        Swal.fire("Error", "Failed to verify Google Authenticator.", "error");
                    });
            });

            // 📌 Toggle MFA enable/disable
            mfaToggle.addEventListener("change", function() {
                fetch("{{ route('toggle-mfa') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({}),
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        Swal.fire({
                            icon: data.status === "success" ? "success" : "error",
                            title: "MFA Status",
                            text: data.status === "success" ?
                                `MFA is now ${data.mfa_enabled ? "enabled" : "disabled"}.` :
                                "Failed to toggle MFA.",
                        });
                    })
                    .catch((error) => {
                        console.error("Toggle MFA Error:", error);
                        Swal.fire("Error", "Failed to toggle MFA.", "error");
                    });
            });

            // 🚨 Warning jika phone number kosong untuk SMS/WhatsApp
            mfaSelect.addEventListener("change", function() {
                const phone = "{{ auth()->user()->phone_number ?? '' }}";
                const method = mfaSelect.value;

                if ((method === "sms" || method === "whatsapp") && !phone.trim()) {
                    if (smsWarning) smsWarning.style.display = "block";
                } else {
                    if (smsWarning) smsWarning.style.display = "none";
                }

                if (method !== "google_auth") {
                    hideQrSection();
                }

                if (method === "sms") {
                    Swal.fire({
                        icon: "warning",
                        title: "Are you sure?",
                        text: "SMS is slow and less secure. Use Email/Google Auth/WhatsApp if possible.",
                        showCancelButton: true,
                        confirmButtonText: "Yes, use SMS",
                        cancelButtonText: "No, pick another",
                    }).then((result) => {
                        if (!result.isConfirmed) {
                            mfaSelect.value = "email";
                            if (smsWarning) smsWarning.style.display = "none";
                        }
                    });
                }
            });

            // 📌 Utility: Sembunyikan bagian QR dan input OTP
            function hideQrSection() {
                qrCodeContainer.style.display = "none";
                qrCodeImage.style.display = "none";
                otpInput.style.display = "none";
                verifyButton.style.display = "none";
                otpInput.required = false;
                otpInput.value = "";
            }

            // Initial state
            mfaSelect.dispatchEvent(new Event("change"));
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#deviceTable').DataTable({
                "searching": true,
                "ordering": true,
                "paging": true,
                "responsive": true,
                "columnDefs": [{
                    "orderable": false,
                    "targets": [7]
                }]
            });
        });

        function confirmDelete(deviceId) {
            Swal.fire({
                title: "Are you sure?",
                text: "This device will be deleted permanently!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm-' + deviceId).submit();
                }
            });
        }
    </script>
    <script>
        function confirmLogout() {
            const mfaMethod = "{{ auth()->user()->mfa->mfa_method ?? '' }}";
            const mfaEnabled = "{{ auth()->user()->mfa->mfa_enabled ?? 0 }}";
            const phoneNumber = "{{ auth()->user()->phone_number ?? '' }}";

            if (mfaEnabled === "1" && (mfaMethod === "whatsapp" || mfaMethod === "sms") && phoneNumber.trim() === "") {
                Swal.fire({
                    icon: "warning",
                    title: "Logout Blocked",
                    text: "You must add your phone number before logging out when using WhatsApp or SMS MFA.",
                });
                return;
            }

            Swal.fire({
                title: "Are you sure?",
                text: "You will be logged out.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, log out",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("logout-form").submit();
                }
            });
        }
    </script>
    <script>
        document.getElementById('passwordless_enabled').addEventListener('change', function() {
            fetch('{{ route('profile.staff.toggle-passwordless') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({})
                }).then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Passwordless Login',
                            text: data.passwordless_enabled ? 'Passwordless login enabled.' :
                                'Passwordless login disabled.',
                        });
                    } else {
                        Swal.fire('Error', 'Failed to toggle passwordless.', 'error');
                    }
                });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('current-url-input');
            if (input) {
                input.value = window.location.href;
            }
        });
    </script>


</body>

</html>
