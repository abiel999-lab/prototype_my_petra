<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf_token" content="{{ csrf_token() }}" />
    <title>BAP</title>

    <link rel="modulepreload" href="https://bap.petra.ac.id/build/assets/app.e935a2fc.js" />
    <script type="module" src="https://bap.petra.ac.id/build/assets/app.e935a2fc.js" data-navigate-track="reload"></script>
    <script src="/livewire/livewire.js?id=b67331b2" data-csrf="{{ csrf_token() }}" data-uri="/livewire/update"
        data-navigate-once="true"></script>
    <link rel="icon" type="image/x-icon" href="https://bap.petra.ac.id/img/PCU.png">

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://bap.petra.ac.id/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet"
        href="https://bap.petra.ac.id/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="https://bap.petra.ac.id/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="https://bap.petra.ac.id/plugins/jqvmap/jqvmap.min.css">
    <link rel="stylesheet" href="https://bap.petra.ac.id/css/adminlte.min.css">
    <link rel="stylesheet" href="https://bap.petra.ac.id/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="https://bap.petra.ac.id/plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="https://bap.petra.ac.id/plugins/summernote/summernote-bs4.min.css">
    <link rel="stylesheet" href="https://bap.petra.ac.id/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="stylesheet" href="https://bap.petra.ac.id/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet"
        href="https://bap.petra.ac.id/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://bap.petra.ac.id/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://bap.petra.ac.id/plugins/datatables-rowgroup/css/rowGroup.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        .navbar {
            height: 80px !important;
            font-size: 20px;
            font-weight: bold;
        }

        @media only screen and (min-width: 600px) {
            .navbar {
                height: 80px !important;
                font-size: 20px;
                font-weight: bold;
            }
        }

        @media only screen and (min-width: 768px) {
            .navbar {
                height: 80px !important;
                font-size: 20px;
                font-weight: bold;
            }
        }

        .sidebar-petra a,
        .breadcrumb a,
        .content-header h1,
        .card-header h3,
        .navbar-nav a,
        .navbar-nav a b,
        h3,
        p {
            color: #1E3258;
            font-weight: bold;
        }

        .dropdown-item.active,
        .dropdown-item:active {
            color: #1E3258;
            text-decoration: none;
            background-color: #ffffff;
        }

        .flashing-yellow {
            background-color: white;
            animation: example 1.5s infinite ease-in-out;
        }

        @keyframes example {
            from {
                background-color: white;
            }

            to {
                background-color: yellow;
            }
        }

        .float {
            position: fixed;
            bottom: 60px;
            right: 10px;
            text-align: center;
            box-shadow: 2px 2px 3px #999;
        }

        .my-float {
            margin-top: 22px;
        }

        .btn-blinking {
            border-radius: 10px;
            border: none;
            color: #FFFFFF;
            cursor: pointer;
            display: inline-block;
            font-size: 22px;
            padding: 10px 19px;
            text-align: center;
            text-decoration: none;
            animation: glowing 1s infinite;
        }

        @keyframes glowing {
            0% {
                background-color: #0026c0;
                box-shadow: 0 0 3px #0026c0;
            }

            50% {
                background-color: #007bff;
                box-shadow: 0 0 40px #007bff;
            }

            100% {
                background-color: #0026c0;
                box-shadow: 0 0 3px #0026c0;
            }
        }
    </style>
</head>

<body class="hold-transition layout-fixed">
    <div class="wrapper">
        <aside class="main-sidebar elevation-2 sidebar-light-primary">
            <a href="http://localhost:8000/" class="brand-link text-center" style="border-bottom:3px #f8ad3d solid;">
                <img src="https://login.petra.ac.id/images/logo-ukp.png" alt="My Petra" style="width: 211px;">
            </a>
        </aside>


        {{-- NAVBAR --}}
        <nav class="main-header navbar navbar-expand navbar-white navbar-light"
            style="border-bottom:3px #f8ad3d solid;">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button" style="color:#1E3258;"><i
                            class="fas fa-bars"></i></a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                {{-- 🔁 Role Switcher --}}
                @php
                    $user = auth()->user();
                    $activeRole = session('active_role', $user->usertype);
                    $roles = $user->roles->pluck('name')->toArray();
                @endphp

                @if (!empty($roles))
                    <li class="nav-item dropdown mr-2">
                        <a class="nav-link btn btn-outline-secondary" data-toggle="dropdown" href="#">
                            <i class="fas fa-random"></i> {{ strtoupper($activeRole) }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <form action="{{ route('role.switch.update') }}" method="POST">
                                @csrf
                                <button type="submit" name="role" value="{{ $user->usertype }}"
                                    class="dropdown-item {{ $activeRole === $user->usertype ? 'active' : '' }}">
                                    <i class="fas fa-user-check"></i> Return to {{ ucfirst($user->usertype) }}
                                </button>
                                <div class="dropdown-divider"></div>
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


                {{-- Profile --}}
                <li class="nav-item dropdown">
                    <a class="nav-link btn btn-outline-secondary" data-toggle="dropdown" href="#">
                        {{ strtoupper($user->name) }} <i class="fas fa-user-circle"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right text-center">
                        <div class="card-body box-profile">
                            <img class="profile-user-img img-fluid img-circle mb-2"
                                src="https://bap.petra.ac.id/foto/mhs/..." alt="User profile picture">
                            <h3 class="profile-username">{{ strtoupper($user->name) }}</h3>
                            <p class="text-muted">{{ $user->email }}</p>

                            <a href="http://localhost:8000/from-bap/setting" class="btn btn-outline-primary mb-2 mt-2">
                                Manage your Account
                            </a>

                            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                @csrf
                                <button type="button" class="btn btn-danger"
                                    onclick="confirmLogout()">Logout</button>
                            </form>
                        </div>
                    </div>

                </li>
            </ul>
            </ul>
        </nav>


        {{-- SIDEBAR --}}
        <aside class="main-sidebar elevation-2 sidebar-light-primary">
            <a href="http://localhost:8000/from-bap" class="brand-link text-center"
                style="border-bottom:3px #f8ad3d solid;">
                <img src="https://login.petra.ac.id/images/logo-ukp.png" alt="My Petra" style="width: 211px;">
            </a>
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column">
                        <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-home"></i>
                                <p>Beranda</p>
                            </a></li>
                        <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-user"></i>
                                <p>Ketua Kelas</p>
                            </a></li>
                        <li class="nav-item"><a href="#" class="nav-link"><i class="fas fa-id-badge"></i>
                                <p>Pengawas Ujian</p>
                            </a></li>
                    </ul>
                </nav>
            </div>
        </aside>


        {{-- MAIN CONTENT --}}
        <div class="content-wrapper">
            <div class="content-header">
                <div class="row mb-2">
                    <div class="col-12">

                        @php
                            $roleLabel = strtoupper(session('active_role', auth()->user()->usertype));
                        @endphp

                        <h1 class="m-0 pl-3">
                            Selamat Datang, <span>{{ $roleLabel }}</span>
                        </h1>
                    </div>
                </div>
            </div>


            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="row">
                                <div class="col-lg-3 col-md-6 col-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-secondary elevation-1"><i
                                                class="fas fa-calendar"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Semester</span>
                                            <span class="info-box-number text-md">2024/2025 Genap</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Tambah box lainnya sesuai kebutuhan -->
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- FOOTER --}}
        <footer class="main-footer">
            <strong>Copyright &copy; 2022.</strong> All rights reserved.
            <div class="float-right d-none d-sm-inline-block"><b>Version</b></div>
        </footer>

        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>

    <form id="form-post" method="POST" action="" hidden>
        @csrf
    </form>

    {{-- Semua JS eksternal tetap dipertahankan --}}
    <script src="https://bap.petra.ac.id/plugins/jquery/jquery.min.js"></script>
    <script src="https://bap.petra.ac.id/plugins/jquery-ui/jquery-ui.min.js"></script>
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <script src="https://bap.petra.ac.id/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://bap.petra.ac.id/js/adminlte.js"></script>
    <script src="https://bap.petra.ac.id/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script>
        $(function() {
            $('.alert').alert();
        });

        function showLoading(text = null) {
            Swal.fire({
                title: text ?? "Sedang dalam proses...",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                text: "You will be logged out from both apps.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, log out"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("logout-form").submit();
                    // Tunggu 500ms lalu arahkan ke force-logout (biar BAP selesai logout dulu)
                    setTimeout(() => {
                        window.location.href = "http://localhost:8000/force-logout";
                    }, 500);
                }
            });

        }
    </script>
</body>

</html>
