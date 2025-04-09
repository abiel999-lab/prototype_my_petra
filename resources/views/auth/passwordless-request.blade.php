<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <title>Login Without Password</title>
    <link href="https://login.petra.ac.id/css/bootstrap.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/style.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/mmenu.css" rel="stylesheet">
    <link rel="stylesheet" href="https://login.petra.ac.id/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="shortcut icon" href="https://login.petra.ac.id/images/favicon.png" type="image/x-icon">
    <link rel="icon" href="https://login.petra.ac.id/images/favicon.png" type="image/x-icon">
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
                    <div class="login-img-register"></div>
                </div>

                <div class="col-sm-8 login-section-wrapper">
                    <div class="row d-flex justify-content-center flex-nowrap">
                        <div class="col-sm-6">
                            <div class="login-wrapper">
                                <h1 class="login-title">Login Without Password</h1>
                                <p style="margin-bottom: 15px;">Enter your email to receive a secure login link.</p>

                                @if(session('status'))
                                    <div class="alert alert-success">{{ session('status') }}</div>
                                @endif

                                <form action="{{ route('passwordless.send') }}" method="POST">
                                    @csrf
                                    <input type="email" name="email" class="form-control" placeholder="your.email@john.petra.ac.id" required>
                                    <button type="submit" class="btn btn-lg login-btn">Send Passwordless Link</button>
                                </form>

                                <br>

                                <a href="{{ route('login') }}" class="text-reset"><strong>Back to Login</strong></a>
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
            </strong> All rights reserved.
        </span>
    </div>

    <!-- Scripts -->
    <script src="https://login.petra.ac.id/js/jquery.js"></script>
    <script src="https://login.petra.ac.id/js/bootstrap.min.js"></script>
    <script src="https://login.petra.ac.id/adminlte/plugins/sweetalert2/sweetalert2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('form').submit(function() {
                $('.loading-screen').show();
            });
        });
    </script>

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ $errors->first("email") }}',
                confirmButtonText: 'OK',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
        </script>
    @endif
    <script>
        window.addEventListener("pageshow", function (event) {
            if (event.persisted || window.performance && window.performance.getEntriesByType("navigation")[0].type === "back_forward") {
                document.querySelector('input[name="email"]').value = '';
            }
        });
    </script>

</body>

</html>
