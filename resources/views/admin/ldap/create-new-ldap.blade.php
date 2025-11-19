<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Create New LDAP User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://login.petra.ac.id/css/bootstrap.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/style.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/mmenu.css" rel="stylesheet">
    <link rel="shortcut icon" href="https://login.petra.ac.id/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-wrapper .form-control {
            background: #fdfdfd;
            border: 1px solid #E6E6E6;
            border-radius: 0;
            height: 60px;
            margin-bottom: 10px;
            margin-top: 0px !important;
        }

        .pagination {
            display: flex;
            justify-content: center;
            padding: 10px;
            margin: 10px;
        }

        .page-link {
            color: #007bff !important;
            border: 1px solid #dee2e6;
            padding: 8px 12px;
        }

        .page-item.active .page-link {
            background-color: #007bff !important;
            color: white !important;
            border-color: #007bff !important;
        }

        ol,
        ul {
            padding-left: 0px !important;
        }

        input.form-control-sm {
            font-size: 14px;
            padding: 4px 8px;
        }
    </style>
</head>

<body>
    <div class="page-wrapper">
        <header class="main-header main-header-auth">
            <div class="nav-outer">
                <div class="logo-box" style="margin-right: auto;">
                    <div class="logo">
                        <a href="{{ route('admin.dashboard') }}">
                            <img src="https://login.petra.ac.id/images/logo-ukp.png">
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="row">
            <div class="col-sm-4 px-0 d-none d-sm-block">
                <div class="login-img-forgot"></div>
            </div>
            <div class="col-sm-8 login-section-wrapper">
                <div class="row d-flex justify-content-center">
                    <div class="col-sm-8">
                        <div class="login-wrapper mt-4">
                            <h1 class="login-title">Create New LDAP User</h1>

                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- 🔹 INFO: koneksi LDAP mana yang gagal (datang dari controller) --}}
                            @if (isset($ldapDown))
                                @php
                                    $downList = collect($ldapDown)
                                        ->filter(fn($v) => $v)
                                        ->keys()
                                        ->implode(', ');
                                @endphp

                                @if ($downList !== '')
                                    <div class="alert alert-warning">
                                        Beberapa server LDAP tidak dapat dihubungi:
                                        <strong>{{ $downList }}</strong>.<br>
                                        Data yang tampil hanya dari koneksi LDAP yang berhasil.
                                    </div>
                                @endif
                            @endif

                            <form method="POST" action="{{ route('ldap.store') }}">

                                @csrf

                                <div class="mb-3">
                                    <input type="hidden" name="target" value="auto">
                                </div>

                                <div class="mb-3">
                                    <label>UID</label>
                                    <input type="text" class="form-control" name="uid"
                                        placeholder="e.g. a11200048" required>
                                </div>

                                <div class="mb-3">
                                    <label>Common Name (Full Name)</label>
                                    <input type="text" class="form-control" name="cn"
                                        placeholder="e.g. John Doe" required>
                                </div>

                                <div class="mb-3">
                                    <label>UID Number</label>
                                    <input type="number" class="form-control" name="uidnumber" placeholder="e.g. 14210"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label>GID Number</label>
                                    <input type="number" class="form-control" name="gidnumber" value="8000" required>
                                </div>

                                <div class="mb-3">
                                    <label>Home Directory</label>
                                    <input type="text" class="form-control" name="homedirectory"
                                        placeholder="/home3/youruid" required>
                                </div>

                                <div class="mb-3" id="passwordGroup">
                                    <label>Password</label>
                                    <input type="password" class="form-control" name="password">
                                </div>

                                <button type="submit" class="btn btn-lg login-btn">Create User</button>
                            </form>

                            <div class="mt-3">
                                <!-- Link ke halaman LDAP -->
                                <div class="mt-4">
                                    <a href="{{ route('profile.admin.manageuser') }}" class="btn btn-outline-primary">
                                        Back to Manage Users
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="container mt-5" style="max-width: 1500px;">
            <h2 class="login-title mt-4">All LDAP Users</h2>

            <form method="GET" action="{{ route('ldap.index') }}" class="mb-3">
                <label class="form-label fw-bold">Search LDAP by UID</label>
                <input type="text" name="uid" id="searchInput" class="form-control" placeholder="Type UID..."
                    value="{{ request('uid') }}">
            </form>

            <table class="table table-bordered" id="ldapTable">

                <thead class="table-dark">
                    <tr>
                        <th>UID</th>
                        <th>CN</th>
                        <th>DN</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($allUsers as $index => $user)
                        <tr id="row-{{ $index }}">
                            <td>
                                <span class="display-uid">{{ $user['uid'] }}</span>
                                <input type="text" name="uid" value="{{ $user['uid'] }}"
                                    class="form-control form-control-sm edit-uid d-none">
                            </td>
                            <td>
                                <span class="display-cn">{{ $user['cn'] }}</span>
                                <input type="text" name="cn" value="{{ $user['cn'] }}"
                                    class="form-control form-control-sm edit-cn d-none">
                            </td>
                            <td>{{ $user['dn'] }}</td>
                            <td>
                                <div class="action-default">
                                    <button type="button" class="btn btn-warning btn-sm"
                                        onclick="enableEdit({{ $index }})">Edit</button>
                                </div>

                                <div class="action-edit d-none">
                                    <form method="POST" action="{{ route('ldap.update') }}" class="d-inline-block">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="dn" value="{{ $user['dn'] }}">
                                        <input type="hidden" name="connection" value="{{ $user['connection'] }}">
                                        <input type="hidden" name="uid" class="uid-input-{{ $index }}">
                                        <input type="hidden" name="cn" class="cn-input-{{ $index }}">
                                        <button type="submit" class="btn btn-success btn-sm"
                                            onclick="return applyEditValues({{ $index }})">Save</button>
                                    </form>

                                    <button type="button" class="btn btn-secondary btn-sm"
                                        onclick="cancelEdit({{ $index }})">Cancel</button>
                                </div>

                                {{-- Delete Form di luar action-edit --}}
                                <form method="POST" action="{{ route('ldap.delete') }}"
                                    class="d-inline-block mt-1">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="dn" value="{{ $user['dn'] }}">
                                    <input type="hidden" name="connection" value="{{ $user['connection'] }}">
                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin ingin menghapus user ini?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No user found.</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    {{ $allUsers->links('pagination::bootstrap-5') }}
                </div>
            </div>


        </div>



        <div class="footer">
            <span>
                <strong>Copyright &copy; 2023
                    <a href="https://petra.ac.id">Petra Christian University</a>.
                </strong>
                All rights reserved.
            </span>
        </div>
    </div>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('searchInput');
        const rows = document.querySelectorAll('#ldapTable tr');
        const defaultLimit = 10;

        function filterRows() {
            const keyword = input.value.toLowerCase();
            let visibleCount = 0;

            rows.forEach(row => {
                const uid = row.cells[0].textContent.toLowerCase();
                const match = uid.includes(keyword);
                row.style.display = (match || keyword === '') && visibleCount < defaultLimit ? '' :
                    'none';
                if (match || keyword === '') visibleCount++;
            });
        }

        input.addEventListener('input', filterRows);
        filterRows(); // on load
    });
</script>
<script>
    function enableEdit(index) {
        const row = document.getElementById('row-' + index);
        row.querySelector('.display-uid').classList.add('d-none');
        row.querySelector('.display-cn').classList.add('d-none');
        row.querySelector('.edit-uid').classList.remove('d-none');
        row.querySelector('.edit-cn').classList.remove('d-none');
        row.querySelector('.action-default').classList.add('d-none');
        row.querySelector('.action-edit').classList.remove('d-none');
    }

    function cancelEdit(index) {
        const row = document.getElementById('row-' + index);
        row.querySelector('.display-uid').classList.remove('d-none');
        row.querySelector('.display-cn').classList.remove('d-none');
        row.querySelector('.edit-uid').classList.add('d-none');
        row.querySelector('.edit-cn').classList.add('d-none');
        row.querySelector('.action-default').classList.remove('d-none');
        row.querySelector('.action-edit').classList.add('d-none');
    }
</script>
<script>
    function applyEditValues(index) {
        const row = document.getElementById('row-' + index);
        const uidValue = row.querySelector('.edit-uid').value;
        const cnValue = row.querySelector('.edit-cn').value;
        row.querySelector('.uid-input-' + index).value = uidValue;
        row.querySelector('.cn-input-' + index).value = cnValue;
        return true;
    }
</script>



</html>
