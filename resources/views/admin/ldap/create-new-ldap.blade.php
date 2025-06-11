<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New LDAP User | My Petra</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
    <link rel="stylesheet" href="{{ asset('css/style29r.css') }}">
</head>
<body>
    <div id="login_wrap">
        <div id="login_header"></div>
        <div id="login_body">
            <h2>Create New LDAP User</h2>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('ldap.store') }}">
                @csrf
                <p>Select LDAP Target</p>
                <select name="target" class="field" required>
                    <option value="student">LDAP Student</option>
                    <option value="default">LDAP Staff</option>
                </select>
                <br><br>

                <p>UID</p>
                <input type="text" name="uid" class="field" placeholder="e.g. c14210164" required>
                <br><br>

                <p>Common Name (Full Name)</p>
                <input type="text" name="cn" class="field" placeholder="e.g. John Doe" required>
                <br><br>

                <p>UID Number</p>
                <input type="number" name="uidnumber" class="field" placeholder="e.g. 14210" required>
                <br><br>

                <p>GID Number</p>
                <input type="number" name="gidnumber" class="field" value="8000" required>
                <br><br>

                <p>Home Directory</p>
                <input type="text" name="homedirectory" class="field" placeholder="/home3/youruid" required>
                <br><br>

                <p>Password</p>
                <input type="password" name="password" class="field" required>
                <br><br>

                <button type="submit" id="but_change">Create User</button>
            </form>
        </div>

        <div id="changes"><a href="{{ url('/') }}">Back to Home</a></div>
    </div>

    <div id="login_copy">&copy; {{ date('Y') }} Petra Christian University. All Rights Reserved.</div>

    <div id="footer">
    </div>
</body>
</html>
