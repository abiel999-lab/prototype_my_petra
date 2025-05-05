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
    <link rel="icon" href="https://login.petra.ac.id/images/favicon.png" type="image/x-icon">
    <link ref="fontawesome" href="https://login.petra.ac.id/adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://login.petra.ac.id/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://login.petra.ac.id/css/loading.css">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0">
    <style>
        .login-wrapper .login-title {
            font-style: normal;
            font-weight: 800;
            font-size: 36px;
            color: #1E3258;
            line-height: 1.4em;
            margin-top: 30px;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            position: relative;
            font-weight: normal;
            margin: 0px;
            background: none;
            line-height: 1.2em;
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

        p.login-wrapper-footer-text {
            font-size: 16px;
            color: #000;
            margin-top: 30px;
        }

        p,
        .text {
            font-size: 15px;
            color: #696969;
            line-height: 24px;
            font-weight: 400;
            margin: 0;
        }

        b,
        strong {
            font-weight: 800;
        }

        .nav-tabs .nav-item .nav-link.active {
            border-top: #FFFFFF;
            border-right: #FFFFFF;
            border-left: #FFFFFF;
            border-bottom: 3px solid #F8AD3D;
            color: #162238;
            font-weight: 800;
        }

        .nav-tabs .nav-item .nav-link {
            color: rgba(52, 58, 64, 0.4);
            font-weight: 500;
        }

        @font-face {
            font-family: "Inter";
            src: url("https://login.petra.ac.id/fonts/Inter-Bold.woff2") format("woff2"),
                url("https://login.petra.ac.id/fonts/Inter-Bold.woff") format("woff");
            font-weight: bold;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: "Inter";
            src: url("https://login.petra.ac.id/fonts/Inter-Black.woff2") format("woff2"),
                url("https://login.petra.ac.id/fonts/Inter-Black.woff") format("woff");
            font-weight: 900;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: "Inter";
            src: url("https://login.petra.ac.id/fonts/Inter-ExtraBold.woff2") format("woff2"),
                url("https://login.petra.ac.id/fonts/Inter-ExtraBold.woff") format("woff");
            font-weight: bold;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: "Inter";
            src: url("https://login.petra.ac.id/fonts/Inter-Light.woff2") format("woff2"),
                url("https://login.petra.ac.id/fonts/Inter-Light.woff") format("woff");
            font-weight: 300;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: "Inter";
            src: url("https://login.petra.ac.id/fonts/Inter-ExtraLight.woff2") format("woff2"),
                url("https://login.petra.ac.id/fonts/Inter-ExtraLight.woff") format("woff");
            font-weight: 200;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: "Inter";
            src: url("https://login.petra.ac.id/fonts/Inter-Medium.woff2") format("woff2"),
                url("https://login.petra.ac.id/fonts/Inter-Medium.woff") format("woff");
            font-weight: 500;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: "Inter";
            src: url("https://login.petra.ac.id/fonts/Inter-Regular.woff2") format("woff2"),
                url("https://login.petra.ac.id/fonts/Inter-Regular.woff") format("woff");
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: "Inter";
            src: url("https://login.petra.ac.id/fonts/Inter-Thin.woff2") format("woff2"),
                url("https://login.petra.ac.id/fonts/Inter-Thin.woff") format("woff");
            font-weight: 100;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: "Inter";
            src: url("https://login.petra.ac.id/fonts/Inter-SemiBold.woff2") format("woff2"),
                url("https://login.petra.ac.id/fonts/Inter-SemiBold.woff") format("woff");
            font-weight: 600;
            font-style: normal;
            font-display: swap;
        }

        .footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            color: #162238 !important;
            background-color: transparent;
            z-index: 1;
            text-align: right;
            padding-right: 10px;
            font-size: 12px;
        }

        @media (max-width: 767px) {
            .footer {
                display: none;
            }
        }

        .mb-3 {
            margin-bottom: 0px !important;
        }
    </style>

    <script defer="defer" src="https://login.petra.ac.id/js/chunk-vendors.f2b7dbd6.js"></script>

    <link href="https://login.petra.ac.id/css/chunk-vendors.f76ef4e6.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/app.2a14bc1c.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://login.petra.ac.id/css/870.fb36812f.css">
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
                    <div class="login-img-company"></div>
                </div>
                <div class="col-sm-8 login-section-wrapper">
                    <div class="row d-flex justify-content-center flex-nowrap">
                        <div class="col-sm-6">
                            <div class="login-wrapper">
                                <h1 class="login-title">Welcome Admin</h1>
                                <p style="margin-top: 10px;">Log in to access our full features.</p>

                                <!-- Login Form -->
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf

                                    <!-- Email and Domain -->
                                    <div class="input-group mb-3">
                                        <!-- Email Local Part -->
                                        <input type="text" class="form-control" id="emailLocalPart"
                                            name="emailLocalPart" placeholder="Username"
                                            value="{{ old('emailLocalPart') }}" required>

                                        <!-- Domain Selection -->
                                        <select class="form-control" id="emailDomain" name="emailDomain" required>
                                            <option value="@petra.ac.id">@petra.ac.id</option>
                                            <option value="@john.petra.ac.id">@john.petra.ac.id</option>
                                            <option value="@peter.petra.ac.id">@peter.petra.ac.id</option>
                                        </select>
                                    </div>

                                    <!-- Hidden Email Field -->
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="Email (e.g., user@domain.com)" value="{{ old('email') }}" required
                                        style="display: none;">

                                    <!-- Password -->
                                    <div class="input-group mb-3">
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Password" required>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-lg login-btn">Sign In</button>
                                        <!-- Sign In with Google Button -->


                                    </div>
                                </form>




                                <div>
                                    <a href="{{ route('google.login') }}" class="btn btn-lg login-btn"
                                        style="margin-top: 10px;">
                                        <img src="https://login.petra.ac.id/img/logo-google.png" alt="Auth"
                                            width="24" style="margin-right: 10px;">
                                        Sign In with Google Mail
                                    </a>
                                    <p class="login-wrapper-footer-text"
                                        style="margin-top: 20px; margin-bottom: 10px; display: block;">
                                        Try Passwordless Login?
                                        <a href="{{ route('passwordless.request') }}"
                                            class="text-reset"><strong>here</strong></a>.
                                        <br />
                                        Forgot your password? Reset your password
                                        <a href="{{ route('password.request') }}"
                                            class="text-reset"><strong>here</strong></a>.

                                        <br />
                                        <!-- Register -->
                                        Doesn't have account? Register on
                                        <a href="{{ route('register') }}"
                                            class="text-reset"><strong>here</strong></a>.
                                        <br />
                                        <!-- Student or staff login -->
                                        You are not a student or staff, click
                                        <a href="{{ route('login.public') }}"
                                            class="text-reset"><strong>here</strong></a>.
                                        <br />
                                        <!-- Student or staff login -->
                                        You are not an admin, click
                                        <a href="{{ route('login') }}" class="text-reset"><strong>here</strong></a>.
                                        <br />
                                        <!-- support -->
                                        Need support? click
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
