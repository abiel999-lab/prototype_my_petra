<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gate</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="https://my.petra.ac.id/adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="https://my.petra.ac.id/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <!-- DataTables -->
<link rel="stylesheet" href="https://my.petra.ac.id/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://my.petra.ac.id/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://my.petra.ac.id/adminlte/dist/css/adminlte.min.css">
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
    <a href="{{ route('staff.dashboard') }}" class="brand-link">
        <img src="https://my.petra.ac.id/img/logo.png" alt="Gate" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Gate</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="https://my.petra.ac.id/img/user.png" class="img-circle elevation-2" alt="ABIEL NATHANAEL GEORGIUS PASARIBU">
            </div>
            <div class="info">
                <a href="{{ route('profile.staff.setting') }}" class="d-block">{{ strtoupper(auth()->user()->name) }}</a>
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
                    <a href="{{ route('profile.staff.profile') }}" class="nav-link ">
                        <i class="nav-icon fas fa-user"></i>
                        <p>
                            Profile
                        </p>
                    </a>
                </li>



                <li class="nav-item">
                    <a href="{{ route('profile.staff.session') }}" class="nav-link  active ">
                        <i class="nav-icon fas fa-stopwatch"></i>
                        <p>
                            Session
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
        <h1 class="m-0">Session</h1>
    </div>
    <!-- /.col -->
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item active">Session</li>
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
                    <div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">List Session</h3>
                <div class="card-tools">
                    <button class="btn btn-danger" data-toggle="tooltip" title="Revoke All" onclick="confirmDeleteSessionAll()">Revoke All <i class="fa fa-trash"></i></a>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body">

                <table class="datatable table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>IP</th>
                            <th>Device</th>
                            <th>Login At</th>
                            <th>Expired At</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                                                <tr>
                            <td>1</td>
                            <td>203.189.120.68</td>
                            <td>Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36</td>
                            <td>27 Jan 2025 21:29</td>
                            <td>27 Jan 2025 23:29</td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-sm" data-toggle="tooltip" title="Revoke" onclick="confirmDeleteSession(`233243`, `203.189.120.68`, `Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36`)"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                                            </tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
</div>

<form action="https://my.petra.ac.id/setting/session/revoke/:id" method="POST" id="delete-session">
    <input type="hidden" name="_method" value="DELETE">    <input type="hidden" name="_token" value="iNaLLKim1OL6te1P1U2dE23M9zPRdHMKjM2UHWIN"></form>

<form action="https://my.petra.ac.id/setting/session/revoke/all" method="POST" id="delete-session-all">
    <input type="hidden" name="_method" value="DELETE">    <input type="hidden" name="_token" value="iNaLLKim1OL6te1P1U2dE23M9zPRdHMKjM2UHWIN"></form>
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
    <!-- DataTables -->
<script src="https://my.petra.ac.id/adminlte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="https://my.petra.ac.id/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://my.petra.ac.id/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="https://my.petra.ac.id/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script type="text/javascript">
    $(function() {
        $(".datatable").DataTable({
            "responsive": true,
            "autoWidth": false,
            "order": [],
        });
    });

    function confirmDeleteSession(id, ip, user_agent) {
        Swal.fire({
            title: 'Konfirmasi Revoke',
            text: `Revoke Session ${ip} - ${user_agent}?`,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Delete'
        }).then((result) => {
            if (result.value) {
                let url = $('#delete-session').attr('action');
                url = url.replace(':id', id);
                $('#delete-session').attr('action', url);
                $('#delete-session').submit();
            }
        });
    }

    function confirmDeleteSessionAll() {
        Swal.fire({
            title: 'Konfirmasi Revoke All',
            text: `Revoke semua Session?`,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Delete'
        }).then((result) => {
            if (result.value) {
                $('#delete-session-all').submit();
            }
        });
    }
</script>
</body>
</html>
