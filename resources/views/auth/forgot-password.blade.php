<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <title>Auth | Forgot your Password</title>
    <link href="https://login.petra.ac.id/css/bootstrap.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/style.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/mmenu.css" rel="stylesheet">
    <link rel="stylesheet" href="https://login.petra.ac.id/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="shortcut icon" href="https://login.petra.ac.id/images/favicon.png" type="image/x-icon">
    <link rel="icon" href="https://login.petra.ac.id/images/favicon.png" type="image/x-icon">
    <link ref="fontawesome" href="https://login.petra.ac.id/adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://login.petra.ac.id/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://login.petra.ac.id/css/loading.css">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
    <div class="loading-screen">
        <div class="col-md-12">
            <div class="loading-spinner"></div>
        </div>
        <div class="col-md-12" style="margin-top:20px">
            <div class="loading-label">Please Wait!</div>
        </div>
    </div>
    <div id="app">
        <div class="page-wrapper">
            <span class="header-span header-auth"></span>
            <header class="main-header main-header-auth">
                <div class="nav-outer">
                    <div class="logo-box" style="margin-right: auto;">
                        <div class="logo">
                            <a href="{{ route('login') }}">
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
                    <div class="row d-flex justify-content-center flex-nowrap">
                        <div class="col-sm-6">
                            <div class="login-wrapper">
                                <div class="tab-content mt-4">
                                    <h1 class="login-title">Forgot your password?</h1>
                                    <p style="margin-top: 10px; margin-bottom: 10px;">Please tell us your email.</p>

                                    <!-- Laravel CSRF Form -->
                                    <form action="{{ route('password.email') }}" method="POST">
                                        @csrf


                                        <div class="input-group mb-3">

                                            <!-- Email -->
                                            <input type="text" class="form-control" id="username" name="username"
                                                placeholder="Email" value="{{ old('username') }}" required autofocus>

                                        </div>

                                        <!-- Current Password -->
                                        <div class="form-group">

                                            <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter your current password" required>
                                        </div>

                                        <!-- New Password -->
                                        <div class="form-group">

                                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Enter your new password" required>
                                        </div>

                                        <!-- Confirm New Password -->
                                        <div class="form-group">

                                            <input type="password" name="confirm_new_password" id="confirm_new_password" class="form-control" placeholder="Confirm your new password" required>
                                        </div>



                                        <!-- Submit Button -->
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-lg login-btn">Change Password</button>
                                        </div>
                                    </form>

                                    <div>
                                        <p class="login-wrapper-footer-text" style="margin-top: 20px; margin-bottom: 10px; display: block;">
                                            Have an account? Go to the login page
                                            <a href="{{ route('login') }}" class="text-reset"><strong>here</strong></a>
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
            <strong>Copyright &copy; 2023
                <a href="https://petra.ac.id">Petra Christian University</a>.
            </strong>
            All rights reserved. version: v1.0.20
        </span>
    </div>
</body>
</html>
