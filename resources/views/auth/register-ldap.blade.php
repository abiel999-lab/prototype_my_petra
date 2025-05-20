<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>LDAP Register | Petra</title>
    <link href="https://login.petra.ac.id/css/bootstrap.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/style.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/mmenu.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://login.petra.ac.id/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="shortcut icon" href="https://login.petra.ac.id/images/favicon.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0">
    <style>
        .login-wrapper .form-control {
            background: #fdfdfd;
            border: 1px solid #E6E6E6;
            border-radius: 0;
            height: 60px;
            margin-top: 0px !important;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="loading-screen" style="display: none;">
        <div class="col-md-12">
            <div class="loading-spinner"></div>
        </div>
        <div class="col-md-12" style="margin-top:20px">
            <div class="loading-label">Please Wait!</div>
        </div>
    </div>

    <div id="app">
        <div class="page-wrapper">
            <header class="main-header main-header-auth">
                <div class="nav-outer">
                    <div class="logo-box" style="margin-right: auto;">
                        <div class="logo">
                            <a href="{{ route('login') }}">
                                <img src="https://login.petra.ac.id/images/logo-ukp.png" alt="PCU Logo">
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <div class="row">
                <div class="col-sm-4 px-0 d-none d-sm-block">
                    <div class="login-img-register"></div>
                </div>
                <div class="col-sm-8 login-section-wrapper">
                    <div class="row d-flex justify-content-center flex-nowrap">
                        <div class="col-sm-6">
                            <div class="login-wrapper">
                                <div class="tab-content mt-4">
                                    <h1 class="login-title">Register Your LDAP Account</h1>
                                    <p style="margin-top: 10px; margin-bottom: 10px;">Only for Petra Christian
                                        University Staff and Students</p>

                                    @if (session('status'))
                                        <div class="alert alert-success">{{ session('status') }}</div>
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

                                    <form method="POST" action="{{ route('ldap.register') }}">
                                        @csrf

                                        <!-- UID -->
                                        <input type="text" name="uid" class="form-control"
                                            placeholder="LDAP UID (e.g. johndoe)" value="{{ old('uid') }}" required>

                                        <!-- Domain -->
                                        <select name="domain" class="form-control" required>
                                            <option value="">Select Domain</option>
                                            <option value="john.petra.ac.id"
                                                {{ old('domain') == 'john.petra.ac.id' ? 'selected' : '' }}>
                                                john.petra.ac.id</option>
                                            <option value="peter.petra.ac.id"
                                                {{ old('domain') == 'peter.petra.ac.id' ? 'selected' : '' }}>
                                                peter.petra.ac.id</option>
                                            <option value="petra.ac.id"
                                                {{ old('domain') == 'petra.ac.id' ? 'selected' : '' }}>petra.ac.id
                                            </option>
                                        </select>

                                        <!-- Email -->
                                        <input type="email" name="email_confirmation" class="form-control"
                                            placeholder="Your Active Email (Confirmation)"
                                            value="{{ old('email_confirmation') }}" required>

                                        <!-- Submit -->
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-lg login-btn">Register</button>
                                        </div>
                                    </form>

                                    <div>
                                        <p class="login-wrapper-footer-text" style="margin-bottom: 0; display: block;">
                                            Not Petra staff or student? <a href="{{ route('register') }}"
                                                class="text-reset"><strong>here</strong></a>
                                        </p>
                                        <p class="login-wrapper-footer-text"
                                            style="margin-top: 20px; margin-bottom: 10px; display: block;">
                                            Already have an account?
                                            <a href="{{ route('login') }}" class="text-reset"><strong>Login
                                                    here</strong></a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <span>
            <strong>&copy; {{ now()->year }} <a href="https://petra.ac.id">Petra Christian University</a>.</strong>
            All rights reserved.
        </span>
    </div>

    <script src="https://login.petra.ac.id/js/jquery.js"></script>
    <script src="https://login.petra.ac.id/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form");
            const loadingScreen = document.querySelector(".loading-screen");

            if (form && loadingScreen) {
                form.addEventListener("submit", function() {
                    loadingScreen.style.display = "block";
                });
            }
        });
    </script>
</body>

</html>
