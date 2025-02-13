
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Settings</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="https://my.petra.ac.id/adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="https://my.petra.ac.id/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
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
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('admin.dashboard') }}" class="nav-link">Gate</a>
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
                <a href="{{ route('profile.admin.setting') }}" class="dropdown-item">Setting</a>
                <a href="{{ route('logout') }}" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
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
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <img src="https://my.petra.ac.id/img/logo.png" alt="Gate" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Gate</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="https://my.petra.ac.id/img/user.png" class="img-circle elevation-2" alt="{{ Auth::user()->name }}">
            </div>
            <div class="info">
                <a href="{{ route('profile.admin.setting') }}" class="d-block">{{ strtoupper(auth()->user()->name) }}</a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <li class="nav-item">
                    <a href="{{ route('profile.admin.profile') }}" class="nav-link ">
                        <i class="nav-icon fas fa-user"></i>
                        <p>
                            Profile
                        </p>
                    </a>
                </li>



                <li class="nav-item">
                    <a href="{{ route('profile.admin.session') }}" class="nav-link ">
                        <i class="nav-icon fas fa-stopwatch"></i>
                        <p>
                            Session
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('profile.admin.mfa') }}" class="nav-link ">
                                        <i class="nav-icon fas fa-shield-alt"></i>
                        <p>
                            MFA
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('profile.admin.manageuser') }}" class="nav-link">
                                        <i class="nav-icon far fa-address-card"></i>
                        <p>
                            Manage Users
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
        <h1 class="m-0">Setting</h1>
    </div>
    <!-- /.col -->
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item active">Setting</li>
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
    <div class="col-12"></div>
    <!-- ./col -->

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
                Swal.showLoading()
            },
        });
    }
</script>
    <script type="text/javascript">
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
    </body>
</html>
