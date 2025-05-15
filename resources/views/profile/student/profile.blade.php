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
                    <a href="{{ route('student.dashboard') }}" class="nav-link">Gate</a>
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
                        <a href="{{ route('profile.student.setting') }}" class="dropdown-item">Setting</a>
                        <a href="{{ route('logout') }}" class="dropdown-item"
                            onclick="event.preventDefault(); confirmLogout(); ">Logout</a>
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
            <a href="{{ route('student.dashboard') }}" class="brand-link">
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
                        <a href="{{ route('profile.student.setting') }}"
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
                            <a href="{{ route('profile.student.profile') }}" class="nav-link  active ">
                                <i class="nav-icon fas fa-user"></i>
                                <p>
                                    Profile
                                </p>
                            </a>
                        </li>



                        <li class="nav-item">
                            <a href="{{ route('profile.student.session.show') }}" class="nav-link ">
                                <i class="nav-icon fas fa-stopwatch"></i>
                                <p>
                                    Session
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('profile.student.mfa') }}" class="nav-link ">
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
                            <h1 class="m-0">Profile</h1>
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">Setting</li>
                                <li class="breadcrumb-item active">Profile</li>
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
                        <div class="col-md-3">

                            <div class="card card-primary card-outline">
                                <div class="card-body box-profile">
                                    <div class="text-center">
                                        <img class="profile-user-img img-fluid img-circle"
                                            src="https://my.petra.ac.id/img/user.png" alt="User profile picture">
                                    </div>
                                    <h3 class="profile-username text-center">{{ strtoupper(auth()->user()->name) }}
                                    </h3>
                                    <p class="text-muted text-center">{{ strtoupper(auth()->user()->usertype) }}</p>
                                    <ul class="list-group list-group-unbordered mb-3">
                                        <li class="list-group-item">
                                            <b>Email</b> <a
                                                class="float-right text-muted">{{ auth()->user()->email }}</a>
                                        </li>
                                    </ul>
                                </div>

                            </div>

                        </div>

                        <div class="col-md-9">
                            <div class="card">
                                <div class="card-header p-2">
                                    <ul class="nav nav-pills">
                                        <li class="nav-item"><a class="nav-link" href="#tab_account"
                                                data-toggle="tab">Account</a></li>
                                        <li class="nav-item"><a class="nav-link" href="#tab_password"
                                                data-toggle="tab">Password</a></li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">

                                        <div class="tab-pane" id="tab_account">
                                            <form class="form-horizontal" action="#" method="POST">
                                                <inp ut type="hidden" name="_token" value="">
                                                    <div class="form-group row">
                                                        <label for="nama"
                                                            class="col-sm-2 col-form-label">Nama</label>
                                                        <div class="col-sm-10">
                                                            <input type="text" class="form-control "
                                                                id="nama" name="nama" placeholder="Nama"
                                                                value="{{ strtoupper(auth()->user()->name) }}"
                                                                required disabled>
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label for="email"
                                                            class="col-sm-2 col-form-label">Email</label>
                                                        <div class="col-sm-10">
                                                            <input type="email" class="form-control "
                                                                id="email" name="email" placeholder="Email"
                                                                value="{{ auth()->user()->email }}" required disabled>
                                                        </div>
                                                    </div>

                                            </form>
                                            <form class="form-horizontal"
                                                action="{{ route('profile.update.phone') }}" method="POST">
                                                @csrf
                                                @method('PUT')

                                                <!-- Input Nomor HP -->
                                                <div class="form-group row">
                                                    <label for="phone_number" class="col-sm-2 col-form-label">Nomor
                                                        HP</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control" id="phone_number"
                                                            name="phone_number" placeholder="Nomor HP"
                                                            value="{{ auth()->user()->phone_number ?? '' }}" required>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="tab-pane" id="tab_password">
                                            <div class="form-group row">
                                                <div class="col-sm-12">

                                                    <a href="{{ route('logout') }}" class="btn btn-primary"
                                                        style="margin-top: 15px;"
                                                        onclick="event.preventDefault(); confirmLogout();">Log
                                                        out untuk ganti Password</a>
                                                    <form id="logout-form" action="{{ route('logout') }}"
                                                        method="POST" style="display: none;">
                                                        @csrf
                                                    </form>


                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>
                </div>
                <!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <footer class="main-footer">
            <strong>Copyright &copy; 2023 <a href="https://petra.ac.id">Petra Christian University</a>.</strong>
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
                    Swal.showLoadin g()
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
                $(`a[href="#tab_account"]`).addClass('active');
                $(`#tab_account`).addClass('active');
            }
        });
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
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('current-url-input');
            if (input) {
                input.value = window.location.href;
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonText: 'OK'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops!',
                text: '{{ session('error') }}',
                confirmButtonText: 'OK'
            });
        @endif
    </script>



</body>

</html>
