<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0">
    <title>Auth | Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://login.petra.ac.id/css/bootstrap.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/style.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/mmenu.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://login.petra.ac.id/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="shortcut icon" href="https://login.petra.ac.id/images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://login.petra.ac.id/css/loading.css">

    <style>
        .login-wrapper .login-title {
            font-weight: 800;
            font-size: 36px;
            color: #1E3258;
            margin-top: 30px;
        }

        .login-wrapper .login-btn {
            padding: 13px 20px;
            background-color: #1E3258;
            font-size: 16px;
            font-weight: 800;
            color: #fff;
            width: 100%;
            margin-top: 10px;
        }

        .footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            color: #162238;
            background-color: transparent;
            text-align: right;
            padding-right: 10px;
            font-size: 12px;
        }

        @media (max-width: 767px) {
            .footer {
                display: none;
            }
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
                                <img src="https://login.petra.ac.id/images/logo-ukp.png" alt="Logo">
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <div class="row">
                <div class="col-sm-4 px-0 d-none d-sm-block">
                    <div class="login-img"></div>
                </div>
                <div class="col-sm-8 login-section-wrapper">
                    <div class="row d-flex justify-content-center flex-nowrap">
                        <div class="col-sm-6">
                            <div class="login-wrapper">
                                <h1 class="login-title">Welcome Student and Staff</h1>
                                <p style="margin-top: 10px;">Log in to access our full features.</p>

                                <!-- Login Form -->
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf

                                    <!-- Email and Domain -->
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="emailLocalPart"
                                            name="emailLocalPart" placeholder="Username"
                                            value="{{ old('emailLocalPart') }}" required>

                                        <select class="form-control" id="emailDomain" name="emailDomain" required>
                                            <option value="@john.petra.ac.id">@john.petra.ac.id</option>
                                            <option value="@peter.petra.ac.id">@peter.petra.ac.id</option>
                                            <option value="@petra.ac.id">@petra.ac.id</option>
                                        </select>
                                    </div>

                                    <!-- Hidden Email Field -->
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="{{ old('email') }}" required style="display: none;">

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
                                <a href="{{ route('google.login') }}" class="btn btn-lg login-btn"
                                    style="margin-top: 10px;">
                                    <img src="https://login.petra.ac.id/img/logo-google.png" alt="Auth"
                                        width="24" style="margin-right: 10px;">
                                    Sign In with Google Mail
                                </a>

                                <p class="login-wrapper-footer-text" style="margin-top: 20px;">
                                    Forgot your password? Reset it
                                    <a href="{{ route('password.request') }}"
                                        class="text-reset"><strong>here</strong></a>.
                                    <br />
                                    You are not a student or staff? Click
                                    <a href="{{ route('login.public') }}" class="text-reset"><strong>here</strong></a>.
                                    <br />
                                    Are you an admin? Click
                                    <a href="{{ route('login.admin') }}" class="text-reset"><strong>here</strong></a>.
                                    <br />
                                    Need support? Click
                                    <a href="{{ route('customer-support') }}"
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
    <script src="https://login.petra.ac.id/js/bootstrap.min.js"></script>
    <script src="https://login.petra.ac.id/js/popper.min.js"></script>
    <script src="https://login.petra.ac.id/js/jquery-ui.min.js"></script>
    <script src="https://login.petra.ac.id/js/chosen.min.js"></script>
    <script src="https://login.petra.ac.id/js/jquery.fancybox.js"></script>
    <script src="https://login.petra.ac.id/js/jquery.modal.min.js"></script>
    <script src="https://login.petra.ac.id/js/mmenu.polyfills.js"></script>
    <script src="https://login.petra.ac.id/js/mmenu.js"></script>
    <script src="https://login.petra.ac.id/js/appear.js"></script>
    <script src="https://login.petra.ac.id/js/ScrollMagic.min.js"></script>
    <script src="https://login.petra.ac.id/js/rellax.min.js"></script>
    <script src="https://login.petra.ac.id/js/owl.js"></script>
    <script src="https://login.petra.ac.id/js/wow.js"></script>
    <script src="https://login.petra.ac.id/js/script.js"></script>
    <script src="https://login.petra.ac.id/js/lottie-player.js"></script>
    <script src="https://login.petra.ac.id/adminlte/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script type="text/javascript">
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const emailLocalPart = document.getElementById("emailLocalPart");
            const emailDomain = document.getElementById("emailDomain");
            const email = document.getElementById("email");

            function updateEmail() {
                email.value = emailLocalPart.value + emailDomain.value;
            }

            emailLocalPart.addEventListener("input", updateEmail);
            emailDomain.addEventListener("change", updateEmail);
        });

        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->any())
                let errorMessage = "{{ $errors->first() }}";
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: errorMessage,
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            @endif
        });

        $(document).ready(function() {
            $('form').submit(function() {
                $('.loading-screen').show();
            });
        });
    </script>
</body>

</html>
