<!-- File: resources/views/admin/ldap/mfa-challenge-ldap.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>LDAP Access Verification</title>
    <link href="https://login.petra.ac.id/css/bootstrap.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/style.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/mmenu.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://login.petra.ac.id/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="stylesheet" href="https://login.petra.ac.id/css/loading.css">
    <link rel="icon" href="https://login.petra.ac.id/images/favicon.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <div class="col-md-12 mt-2">
            <div class="loading-label">Please Wait!</div>
        </div>
    </div>

    <div class="page-wrapper">
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
                <div class="row d-flex justify-content-center">
                    <div class="col-sm-8">
                        <div class="login-wrapper">
                            <h1 class="login-title">Verify LDAP Access</h1>
                            <p>Please enter the OTP code and upload your identity photo/selfie. The OTP was sent to your
                                email.</p>

                            <form action="{{ route('ldap.otp.verify') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" name="code" placeholder="OTP code"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label for="attachment">Upload ID/Selfie (jpg, png, pdf)</label>
                                    <input type="file" class="form-control" name="attachment"
                                        accept=".jpg,.jpeg,.png,.pdf" required>
                                </div>

                                <div class="form-group mt-3">
                                    <button type="submit" class="btn btn-lg login-btn">Verify Access</button>
                                </div>
                            </form>

                            <div style="display: flex; gap: 10px; margin-top: 15px;">
                                <form id="resend-form" action="{{ route('ldap.otp.resend') }}" method="POST"
                                    onsubmit="handleResend(event)">
                                    @csrf
                                    <button id="resend-button" type="submit" class="btn btn-secondary">Resend
                                        OTP</button>
                                </form>
                                <form action="{{ route('ldap.otp.cancel') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Cancel</button>
                                </form>
                            </div>

                            <p class="mt-3">Need support? Click <a
                                    href="{{ route('customer-support') }}"><strong>here</strong></a>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer mt-5">
            <span>
                <strong>&copy; 2023 <a href="https://petra.ac.id">Petra Christian University</a>.</strong>
                All rights reserved.
            </span>
        </div>
    </div>

    <script src="https://login.petra.ac.id/js/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    if (!response.ok) throw new Error("Failed to resend OTP");
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP resent successfully'
                    });
                })
                .catch(() => {
                    document.querySelector('.loading-screen').style.display = 'none';
                    clearInterval(countdown);
                    button.disabled = false;
                    button.innerText = "Resend OTP";
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to resend OTP'
                    });
                });
        }
    </script>
    @if ($errors->has('code'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'OTP Incorrect',
                text: '{{ $errors->first('code') }}',
            });
        </script>
    @endif
    @if ($errors->has('attachment'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Attachment Error',
                text: '{{ $errors->first('attachment') }}',
            });
        </script>
    @endif


</body>

</html>
