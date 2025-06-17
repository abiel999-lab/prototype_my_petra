<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Extended MFA Challenge</title>
    <link href="https://login.petra.ac.id/css/bootstrap.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/style.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/mmenu.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://login.petra.ac.id/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="stylesheet" href="https://login.petra.ac.id/css/loading.css">
    <link rel="icon" href="https://login.petra.ac.id/images/favicon.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0">
    <style>
        .login-wrapper .form-control {
            background: #fdfdfd;
            border: 1px solid #E6E6E6;
            border-radius: 0;
            height: 60px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="loading-screen" style="display: none;">
        <div class="col-md-12">
            <div class="loading-spinner"></div>
        </div>
        <div class="col-md-12 mt-3">
            <div class="loading-label">Please Wait!</div>
        </div>
    </div>

    <div id="app">
        <div class="page-wrapper">
            <header class="main-header main-header-auth">
                <div class="nav-outer">
                    <div class="logo-box" style="margin-right: auto;">
                        <div class="logo">
                            <a href="{{ route('dashboard') }}">
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
                    <div class="row d-flex justify-content-center">
                        <div class="col-sm-6">
                            <div class="login-wrapper">
                                <div class="tab-content mt-4">
                                    <h1 class="login-title">Input OTP Code</h1>
                                    <p style="margin-top: 10px; margin-bottom: 10px;">
                                    <p style="margin-top: 10px; margin-bottom: 10px;">
                                        Open your authenticator app, Email, WhatsApp, or SMS and enter the 6-digit code
                                        to log in. When using Email, WhatsApp, or SMS, wait for Email, WhatsApp, or SMS
                                        notification to arrive.
                                        <b>If the correct OTP is showing an error, try resending the OTP
                                            again.</b><br><br>
                                        @php
                                            $methodLabels = [
                                                'google_auth' => 'Mobile Authenticator',
                                                'whatsapp' => 'WhatsApp',
                                            ];
                                        @endphp
                                        <strong>Authentication method:</strong>
                                        {{ $methodLabels[strtolower($mfa->extended_mfa_method ?? '')] ?? 'Unknown' }}
                                        </br>
                                        <b>Note:</b> If you are not receiving the message,
                                        please click <b>Resend OTP</b> until the OTP arrives on your Email/Whatsapp/SMS.
                                    </p>

                                    <form method="POST" action="{{ route('extended-mfa.verify') }}">
                                        @csrf
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" name="otp_code"
                                                placeholder="OTP code" required>
                                        </div>

                                        @error('otp_code')
                                            <div class="text-danger mb-3">{{ $message }}</div>
                                        @enderror

                                        <div class="form-group">
                                            <button type="submit" class="btn btn-lg login-btn w-100">Verify</button>
                                        </div>
                                    </form>

                                    <!-- Resend & Cancel -->
                                    <div class="d-flex gap-2 mt-3">
                                        <form id="resend-form" action="{{ route('extended-mfa.resend') }}"
                                            method="POST" onsubmit="handleResend(event)">
                                            @csrf
                                            <button id="resend-button" type="submit" class="btn btn-secondary">Resend
                                                OTP</button>
                                        </form>
                                        <form action="{{ route('extended-mfa.cancel') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">Cancel</button>
                                        </form>
                                    </div>

                                    <br>
                                    Need support? Click <a
                                        href="{{ route('customer-support') }}"><strong>here</strong></a>.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer text-center mt-5">
            <strong>&copy; 2023 <a href="https://petra.ac.id">Petra Christian University</a></strong>. All rights
            reserved.
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://login.petra.ac.id/js/jquery.js"></script>
    <script src="https://login.petra.ac.id/js/bootstrap.min.js"></script>
    <script src="https://login.petra.ac.id/adminlte/plugins/sweetalert2/sweetalert2.min.js"></script>

    <script>
        function handleResend(event) {
            event.preventDefault();
            const button = document.getElementById('resend-button');
            const form = document.getElementById('resend-form');
            const csrfToken = form.querySelector('input[name="_token"]').value;

            document.querySelector('.loading-screen').style.display = 'block';
            button.disabled = true;
            let timeLeft = 15;
            button.innerText = `Wait ${timeLeft}s`;
            const countdown = setInterval(() => {
                timeLeft--;
                button.innerText = `Wait ${timeLeft}s`;
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    button.disabled = false;
                    button.innerText = "Resend OTP";
                }
            }, 1000);

            fetch(form.action, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({})
                })
                .then(response => {
                    document.querySelector('.loading-screen').style.display = 'none';
                    if (!response.ok) throw new Error("Failed");
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP has been resent!'
                    });
                })
                .catch(() => {
                    document.querySelector('.loading-screen').style.display = 'none';
                    clearInterval(countdown);
                    button.disabled = false;
                    button.innerText = "Resend OTP";
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: 'Could not resend OTP. Please try again.'
                    });
                });
        }

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Invalid OTP',
                text: '{{ $errors->first('otp_code') }}',
                confirmButtonText: 'OK'
            });
        @endif
    </script>
</body>

</html>
