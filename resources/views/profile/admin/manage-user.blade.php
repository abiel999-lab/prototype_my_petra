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
    <!-- DataTables -->
    <link rel="stylesheet"
        href="https://my.petra.ac.id/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet"
        href="https://my.petra.ac.id/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://my.petra.ac.id/adminlte/dist/css/adminlte.min.css">
    <link rel="shortcut icon" href="https://login.petra.ac.id/images/favicon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .btn-group-sm>.btn,
        .btn-sm {
            padding: .25rem .5rem;
            font-size: .875rem;
            line-height: 1.5;
            border-radius: .2rem;
            margin: 3px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            padding: 10px;
        }

        .page-link {
            color: #007bff !important;
            /* Bootstrap primary blue */
            border: 1px solid #dee2e6;
            padding: 8px 12px;
        }

        .page-item.active .page-link {
            background-color: #007bff !important;
            color: white !important;
            border-color: #007bff !important;
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
            <a href="{{ route('admin.dashboard') }}" class="brand-link">
                <img src="https://my.petra.ac.id/img/logo.png" alt="Gate" class="brand-image img-circle elevation-3"
                    style="opacity: .8">
                <span class="brand-text font-weight-light">Gate</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="https://my.petra.ac.id/img/user.png" class="img-circle elevation-2"
                            alt="ABIEL NATHANAEL GEORGIUS PASARIBU">
                    </div>
                    <div class="info">
                        <a href="{{ route('profile.admin.setting') }}"
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
                            <a href="{{ route('profile.admin.profile') }}" class="nav-link ">
                                <i class="nav-icon fas fa-user"></i>
                                <p>
                                    Profile
                                </p>
                            </a>
                        </li>



                        <li class="nav-item">
                            <a href="{{ route('profile.admin.session.show') }}" class="nav-link">
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
                                    Security
                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('profile.admin.manageuser') }}" class="nav-link active">
                                <i class="nav-icon far fa-address-card"></i>
                                <p>
                                    Manage Users
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
                            <h1 class="m-0">Manage Users</h1>
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">Setting</li>

                                <li class="breadcrumb-item active">Manage Users</li>
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
                                <!-- /.card-header -->



                                <div class="card-body">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif

                                    <!-- Search Form -->
                                    <div class="mb-4">
                                        <form id="search-form" class="d-flex" onsubmit="return false;">
                                            <input type="text" id="search-input" name="search"
                                                class="form-control me-2"
                                                placeholder="Search users... (e.g. mfa_enabled:on mfa_method:google_authenticator usertype:admin)">
                                            <button type="button" class="btn btn-primary"
                                                onclick="searchUsers()">Search</button>
                                        </form>
                                    </div>



                                    <!-- Add User Form -->
                                    <div class="row">
                                        <div class="col-md-3">
                                            <h4 class="mb-3">Add User</h4>
                                            <form method="POST"
                                                action="{{ route('profile.admin.manageuser.store') }}">
                                                @csrf
                                                <div class="form-group mb-3">
                                                    <label for="name">Name</label>
                                                    <input type="text" id="name" name="name"
                                                        class="form-control" required>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label for="email">Email</label>
                                                    <input type="email" id="email" name="email"
                                                        class="form-control" required>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label for="password">Password</label>
                                                    <input type="password" id="password" name="password"
                                                        class="form-control" required>
                                                </div>
                                                <div class="form-group mb-3">
                                                    <label for="usertype">User Type</label>
                                                    <select id="usertype" name="usertype" class="form-control"
                                                        required>
                                                        <option value="general">General</option>
                                                        <option value="student">Student</option>
                                                        <option value="staff">Staff</option>
                                                        <option value="admin">Admin</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary rounded">Add
                                                    User</button>
                                            </form>
                                        </div>

                                        <!-- User List -->
                                        <div class="col-md-9">
                                            <h4 class="mb-3">User List</h4>
                                            <table class="table table-bordered">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>User Type</th>
                                                        <th>MFA Enabled</th>
                                                        <th>MFA Method</th>
                                                        <th>Operating Systems</th> <!-- OS Column -->
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="user-list">
                                                    @foreach ($users as $user)
                                                        <tr>
                                                            <td>{{ $user->id }}</td>
                                                            <td>
                                                                <input type="text" id="name-{{ $user->id }}"
                                                                    value="{{ $user->name }}"
                                                                    class="form-control form-control-sm" disabled>
                                                            </td>
                                                            <td>
                                                                <input type="email" id="email-{{ $user->id }}"
                                                                    value="{{ $user->email }}"
                                                                    class="form-control form-control-sm" disabled>
                                                            </td>
                                                            <td>
                                                                <select id="usertype-{{ $user->id }}"
                                                                    class="form-control form-control-sm" disabled>
                                                                    <option value="admin"
                                                                        {{ $user->usertype == 'admin' ? 'selected' : '' }}>
                                                                        Admin</option>
                                                                    <option value="student"
                                                                        {{ $user->usertype == 'student' ? 'selected' : '' }}>
                                                                        Student</option>
                                                                    <option value="staff"
                                                                        {{ $user->usertype == 'staff' ? 'selected' : '' }}>
                                                                        Staff</option>
                                                                    <option value="general"
                                                                        {{ $user->usertype == 'general' ? 'selected' : '' }}>
                                                                        General</option>
                                                                </select>
                                                            </td>
                                                            <td class="text-center">
                                                                <input type="checkbox"
                                                                    id="mfa_enabled-{{ $user->id }}"
                                                                    {{ $user->mfa_enabled ? 'checked' : '' }} disabled>
                                                            </td>
                                                            <td>
                                                                <select id="mfa_method-{{ $user->id }}"
                                                                    class="form-control form-control-sm" disabled>
                                                                    <option value="email"
                                                                        {{ $user->mfa_method == 'email' ? 'selected' : '' }}>
                                                                        Email</option>
                                                                    <option value="google_authenticator"
                                                                        {{ $user->mfa_method == 'google_authenticator' ? 'selected' : '' }}>
                                                                        Google Authenticator
                                                                    </option>
                                                                    <option value="whatsapp"
                                                                        {{ $user->mfa_method == 'whatsapp' ? 'selected' : '' }}>
                                                                        WhatsApp</option>
                                                                    <option value="sms"
                                                                        {{ $user->mfa_method == 'sms' ? 'selected' : '' }}>
                                                                        SMS</option>
                                                                </select>
                                                            </td>
                                                            <td> <!-- 🔹 Operating System Column with Trust/Untrust Buttons in One Line -->
                                                                @if (count($user->devices) > 0)
                                                                    @foreach ($user->devices->unique('os') as $device)
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <span
                                                                                class="badge badge-primary">{{ $device->os }}</span>

                                                                            @if ($device->trusted)
                                                                                <span
                                                                                    class="badge bg-success d-flex align-items-center">

                                                                                    Trusted
                                                                                </span>
                                                                            @endif

                                                                            <form
                                                                                action="{{ route('profile.admin.mfa.trust', $device->id) }}"
                                                                                method="POST" class="d-inline">
                                                                                @csrf
                                                                                <button type="submit"
                                                                                    class="btn btn-success btn-sm"
                                                                                    onclick="return confirm('Trusting this device will untrust all others for this user. Continue?');"
                                                                                    {{ $device->trusted ? 'disabled' : '' }}>
                                                                                    Trust
                                                                                </button>
                                                                            </form>

                                                                            <form
                                                                                action="{{ route('profile.admin.mfa.untrust', $device->id) }}"
                                                                                method="POST" class="d-inline">
                                                                                @csrf
                                                                                <button type="submit"
                                                                                    class="btn btn-warning btn-sm"
                                                                                    onclick="return confirm('Are you sure you want to untrust this device?');"
                                                                                    {{ !$device->trusted ? 'disabled' : '' }}>
                                                                                    Untrust
                                                                                </button>
                                                                            </form>
                                                                            <form
                                                                                action="{{ route('profile.admin.mfa.delete', $device->id) }}"
                                                                                method="POST" class="d-inline"
                                                                                onsubmit="return confirm('Are you sure you want to delete this device?');">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit"
                                                                                    class="btn btn-danger btn-sm">Delete</button>
                                                                            </form>

                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    No Devices
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <!-- 🔹 User Edit Form (Ensures every user has an Edit button) -->
                                                                <form id="edit-form-{{ $user->id }}"
                                                                    method="POST"
                                                                    action="{{ route('profile.admin.manageuser.update', $user->id) }}">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <button type="button"
                                                                        class="btn btn-warning btn-sm rounded me-2"
                                                                        onclick="toggleEdit({{ $user->id }})"
                                                                        id="edit-btn-{{ $user->id }}">Edit</button>
                                                                    <button type="submit"
                                                                        class="btn btn-success btn-sm rounded me-2 d-none"
                                                                        id="save-btn-{{ $user->id }}">Save</button>
                                                                    <input type="hidden" name="name"
                                                                        id="hidden-name-{{ $user->id }}">
                                                                    <input type="hidden" name="email"
                                                                        id="hidden-email-{{ $user->id }}">
                                                                    <input type="hidden" name="usertype"
                                                                        id="hidden-usertype-{{ $user->id }}">
                                                                    <input type="hidden" name="mfa_enabled"
                                                                        id="hidden-mfa_enabled-{{ $user->id }}">
                                                                    <input type="hidden" name="mfa_method"
                                                                        id="hidden-mfa_method-{{ $user->id }}">
                                                                </form>

                                                                <!-- 🔹 Ban/Unban User -->
                                                                <form method="POST"
                                                                    id="ban-form-{{ $user->id }}"
                                                                    class="d-inline">
                                                                    @csrf
                                                                    @if ($user->banned_status)
                                                                        <button type="button"
                                                                            class="btn btn-success btn-sm rounded"
                                                                            onclick="updateBanStatus({{ $user->id }}, false)">Unban</button>
                                                                    @else
                                                                        <button type="button"
                                                                            class="btn btn-danger btn-sm rounded"
                                                                            onclick="updateBanStatus({{ $user->id }}, true)">Ban</button>
                                                                    @endif
                                                                </form>

                                                                <!-- 🔹 Delete User -->
                                                                <form method="POST"
                                                                    action="{{ route('profile.admin.manageuser.delete', $user->id) }}"
                                                                    class="d-inline"
                                                                    onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="btn btn-danger btn-sm rounded">Delete</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>


                                            <div class="d-flex justify-content-center mt-3" id="pagination-links">
                                                {{ $users->links('pagination::bootstrap-5') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>
                    </div>

                    <form action="https://my.petra.ac.id/setting/session/revoke/:id" method="POST"
                        id="delete-session">
                        <input type="hidden" name="_method" value="DELETE"> <input type="hidden" name="_token"
                            value="iNaLLKim1OL6te1P1U2dE23M9zPRdHMKjM2UHWIN">
                    </form>

                    <form action="https://my.petra.ac.id/setting/session/revoke/all" method="POST"
                        id="delete-session-all">
                        <input type="hidden" name="_method" value="DELETE"> <input type="hidden" name="_token"
                            value="iNaLLKim1OL6te1P1U2dE23M9zPRdHMKjM2UHWIN">
                    </form>
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

    <!-- jQuery + Dependencies -->
    <script src="https://my.petra.ac.id/adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="https://my.petra.ac.id/adminlte/plugins/jquery-ui/jquery-ui.min.js"></script>
    <script>
        $.widget.bridge('uibutton', $.ui.button);
    </script>
    <script src="https://my.petra.ac.id/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://my.petra.ac.id/adminlte/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <script src="https://my.petra.ac.id/adminlte/dist/js/adminlte.js"></script>
    <script src="https://my.petra.ac.id/adminlte/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Toast & Loader -->
    <script>
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
                didOpen: () => Swal.showLoading()
            });
        }
    </script>

    <!-- Tooltip Init -->
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>

    <!-- DataTables -->
    <script src="https://my.petra.ac.id/adminlte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="https://my.petra.ac.id/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://my.petra.ac.id/adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="https://my.petra.ac.id/adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script>
        $(function() {
            $(".datatable").DataTable({
                responsive: true,
                autoWidth: false,
                order: [],
            });
        });
    </script>

    <!-- SweetAlert Delete -->
    <script>
        document.querySelectorAll('.delete-user-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Delete User?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
    </script>

    <!-- Edit Save Confirmation -->
    <script>
        document.querySelectorAll('form[id^="edit-form-"]').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const userId = this.id.split('-')[2];

                document.getElementById(`hidden-name-${userId}`).value = document.getElementById(
                    `name-${userId}`).value;
                document.getElementById(`hidden-email-${userId}`).value = document.getElementById(
                    `email-${userId}`).value;
                document.getElementById(`hidden-usertype-${userId}`).value = document.getElementById(
                    `usertype-${userId}`).value;
                document.getElementById(`hidden-mfa_enabled-${userId}`).value = document.getElementById(
                    `mfa_enabled-${userId}`).checked ? 1 : 0;
                document.getElementById(`hidden-mfa_method-${userId}`).value = document.getElementById(
                    `mfa_method-${userId}`).value;

                Swal.fire({
                    title: 'Save Changes?',
                    text: "Are you sure you want to update this user?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, save it!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>

    <!-- Toggle Edit Button Logic -->
    <script>
        function toggleEdit(userId) {
            const fields = ['name', 'email', 'usertype', 'mfa_enabled', 'mfa_method'];
            fields.forEach(field => {
                const el = document.getElementById(`${field}-${userId}`);
                el.disabled = !el.disabled;
            });

            const editBtn = document.getElementById(`edit-btn-${userId}`);
            const saveBtn = document.getElementById(`save-btn-${userId}`);
            if (editBtn.textContent === 'Edit') {
                editBtn.textContent = 'Cancel';
                saveBtn.classList.remove('d-none');
            } else {
                editBtn.textContent = 'Edit';
                saveBtn.classList.add('d-none');
            }
        }
    </script>

    <!-- Ban/Unban with Toast -->
    <script>
        function updateBanStatus(userId, isBan) {
            let actionUrl = isBan ?
                "{{ url('/admin/setting/manageuser/ban') }}/" + userId :
                "{{ url('/admin/setting/manageuser/unban') }}/" + userId;

            fetch(actionUrl, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json",
                    }
                }).then(response => response.json())
                .then(data => {
                    Swal.fire({
                        icon: data.success ? "success" : "error",
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000
                    });

                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }).catch(error => console.log("Error:", error));
        }
    </script>

    <!-- Logout SweetAlert -->
    <script>
        function confirmLogout() {
            const mfaMethod = "{{ auth()->user()->mfa_method }}";
            const mfaEnabled = "{{ auth()->user()->mfa_enabled }}";
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

    <!-- Enhanced Smart Search -->
    <script>
        document.getElementById("search-input").addEventListener("keypress", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                searchUsers();
            }
        });

        function searchUsers() {
            let inputQuery = document.getElementById("search-input").value;
            let params = {
                search: "",
                mfa_enabled: "",
                mfa_method: "",
                usertype: ""
            };

            const matches = inputQuery.match(/(\w+):(\w+)/g);
            if (matches) {
                matches.forEach(match => {
                    let [key, value] = match.split(":");
                    if (params.hasOwnProperty(key)) {
                        params[key] = value.toLowerCase();
                    }
                });
            }

            params.search = inputQuery.replace(/(\w+):(\w+)/g, "").trim();

            let queryString = Object.keys(params)
                .filter(key => params[key] !== "")
                .map(key => `${key}=${encodeURIComponent(params[key])}`)
                .join("&");

            fetch("{{ route('profile.admin.manageuser') }}?" + queryString)
                .then(response => response.text())
                .then(html => {
                    document.getElementById("user-list").innerHTML = new DOMParser()
                        .parseFromString(html, "text/html")
                        .getElementById("user-list").innerHTML;
                })
                .catch(error => console.error("Error:", error));
        }
    </script>






</body>

</html>
