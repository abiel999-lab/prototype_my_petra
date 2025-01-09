<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0">
    <title>Auth | Public</title>

    <link href="https://login.petra.ac.id/css/bootstrap.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/style.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/mmenu.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://login.petra.ac.id/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="shortcut icon" href="https://login.petra.ac.id/images/favicon.png" type="image/x-icon">
    <link rel="icon" href="https://login.petra.ac.id/images/favicon.png" type="image/x-icon">
    <link ref="fontawesome" href="https://login.petra.ac.id/adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://login.petra.ac.id/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://login.petra.ac.id/css/loading.css">
    <style>
    .login-wrapper .login-title {
        font-style: normal;
        font-weight: 800;
        font-size: 36px;
        color: #1E3258;
        line-height: 1.4em;
        margin-top: 30px;
    }

    .login-wrapper .login-btn {
        padding: 13px 20px;
        background-color: #1E3258;
        border-radius: 0;
        font-size: 16px;
        font-weight: 800;
        color: #fff;
        width: 100%;
        margin-top: 10px;
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
                            <a href="https://login.petra.ac.id">
                                <img src="https://login.petra.ac.id/images/logo-ukp.png" alt="Logo">
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <div class="row">
                <div class="col-sm-4 px-0 d-none d-sm-block">
                    <div class="login-img-gedungw"></div>
                </div>
                <div class="col-sm-8 login-section-wrapper">
                    <div class="row d-flex justify-content-center flex-nowrap">
                        <div class="col-sm-6">
                            <div class="login-wrapper">
                                <h1 class="login-title">Welcome to login page</h1>
                                <p style="margin-top: 10px;">Log in to access our full features.</p>

                                <!-- Login Form -->
                                <form method="POST" action="{{ route('public.login') }}">
                                    @csrf

                                    <!-- Email -->
                                    <div class="input-group mb-3">
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="Email Address" value="{{ old('email') }}" required autofocus>
                                    </div>

                                    <!-- Password -->
                                    <div class="input-group mb-3">
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Password" required>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-lg login-btn">Sign In</button>
                                    </div>
                                </form>

                                <!-- Google Login -->
                                <form method="POST" action="{{ url('/login/google/redirect') }}"
                                    style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-lg login-btn" style="margin-top: 10px;">
                                        <img src="https://login.petra.ac.id/img/logo-google.png" alt="Auth" width="24"
                                            style="margin-right: 10px;">
                                        Sign In with Google Mail
                                    </button>
                                </form>

                                <!-- Forgot Password -->
                                <p class="login-wrapper-footer-text"
                                    style="margin-top: 20px; margin-bottom: 10px; display: block;">
                                    Forgot your password? Reset your password
                                    <a href="{{ route('password.request') }}"
                                        class="text-reset"><strong>here</strong></a>.
                                    <br />
                                    <!-- Register -->
                                    Doesn't have account? Register on
                                    <a href="{{ route('register') }}" class="text-reset"><strong>here</strong></a>.
                                    <br />
                                    <!-- Student or staff login -->
                                    You are student or staff, click
                                    <a href="{{ route('password.request') }}"
                                        class="text-reset"><strong>here</strong></a>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <span><strong>Copyright &copy; 2023 <a href="https://petra.ac.id">Petra Christian University</a>.</strong>
            All rights reserved. Version: v1.0.20</span>
    </div>

    <!-- Scripts -->
    <script src="https://login.petra.ac.id/js/jquery.js"></script>
    <script>
    $(document).ready(function() {
        $('form').submit(function(e) {
            $('.loading-screen').show();
        });
    });
    </script>
</body>

</html>