<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <title>Auth | MFA challenge</title>
    <link href="https://login.petra.ac.id/css/bootstrap.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/style.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/mmenu.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://login.petra.ac.id/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
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
                                <div class="tab-content mt-4">
                                    <h1 class="login-title">Input OTP Code</h1>
                                    <p style="margin-top: 10px; margin-bottom: 10px;">
                                        Open your authenticator app, Email, WhatsApp, or SMS and enter the 6-digit code
                                        to log in. When using Email, WhatsApp, or SMS, wait for Email, WhatsApp, or SMS
                                        notification to arrive.
                                        <b>If the correct OTP is showing an error, try resending the OTP
                                            again.</b><br><br>
                                        <b>Note:</b> If you are not receiving the message,
                                        please click <b>Resend OTP</b> until the OTP arrives on your Email/Whatsapp/SMS.
                                    </p>
                                    <form action="{{ route('mfa-challenge-external.verify') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="redirect"
                                            value="{{ $redirect ?? request('redirect') }}">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" name="code"
                                                placeholder="OTP code" id="code" required>
                                        </div>

                                        <div class="form-group">
                                            <button type="submit" class="btn btn-lg login-btn">Verify</button>
                                        </div>
                                    </form>
                                    <!-- Tombol Resend OTP dan Cancel dalam satu baris -->
                                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                                        <form id="resend-form" action="{{ route('mfa-challenge.resend') }}"
                                            method="POST" onsubmit="handleResend(event)">
                                            @csrf
                                            <button id="resend-button" type="submit" class="btn btn-secondary">Resend
                                                OTP</button>
                                        </form>
                                        <form action="{{ route('mfa-challenge.cancel') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">Cancel</button>
                                        </form>
                                    </div>


                                    </br>
                                    <!-- support -->
                                    Need support? click
                                    <a href="{{ route('customer-support') }}"
                                        class="text-reset"><strong>here</strong></a>.


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
            All rights reserved.
        </span>
    </div>
    <script src="https://login.petra.ac.id/js/jquery.js"></script>
    <script src="https://login.petra.ac.id/js/popper.min.js"></script>
    <script src="https://login.petra.ac.id/js/jquery-ui.min.js"></script>
    <script src="https://login.petra.ac.id/js/chosen.min.js"></script>
    <script src="https://login.petra.ac.id/js/bootstrap.min.js"></script>
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
    <script type="text/javascript">
        $(document).ready(function() {
            $('form').submit(function(e) {
                $('.loading-screen').show();
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->any())
                let errorMessage = '{{ $errors->first('code') }}';
                let remainingAttempts = "{{ session('remaining_otp_attempts ') }}";
                let banUntil = "{{ session('otp_banned_until') }}";

                let displayMessage = errorMessage;

                if (remainingAttempts > 0) {
                    displayMessage += `\nYou have ${remainingAttempts} attempts left before a temporary ban.`;
                }

                if (banUntil) {
                    let banTime = new Date(banUntil);
                    let currentTime = new Date();
                    let diff = Math.round((banTime - currentTime) / 1000); // Convert to seconds

                    if (diff > 0) {
                        let minutes = Math.floor(diff / 60);
                        let seconds = diff % 60;
                        displayMessage =
                            `Too many failed OTP attempts. You are temporarily banned for ${minutes} minutes and ${seconds} seconds.`;
                    }
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Invalid OTP',
                    text: displayMessage,
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            @endif
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#otp-form').submit(function(e) {
                $('.loading-screen').show();
                let form = $(this);
                $.post(form.attr('action'), form.serialize())
                    .done(function(response) {
                        window.location.href = response.redirect;
                    })
                    .fail(function(jqXHR) {
                        $('.loading-screen').hide();

                        let responseText = jqXHR.responseText;
                        let jsonResponse = JSON.parse(responseText);
                        let errorMessage = jsonResponse.message ||
                            "The OTP code you entered is incorrect.";
                        let remainingAttempts = jsonResponse.remaining_otp_attempts || 10;
                        let banUntil = jsonResponse.otp_banned_until || null;

                        let displayMessage = errorMessage;

                        if (remainingAttempts > 0) {
                            displayMessage +=
                                `\nYou have ${remainingAttempts} attempts left before a temporary ban.`;
                        }


                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid OTP',
                            text: displayMessage,
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        });
                    });
            });
        });
    </script>

    <script>
        window.addEventListener("popstate", function(event) {
            window.location.href = "{{ route('mfa-challenge.cancel') }}";
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            @if (session('otp_failed_limit'))
                Swal.fire({
                    icon: 'error',
                    title: 'Sorry, you cannot input anymore.',
                    text: 'You have reached the maximum OTP attempts.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // ✅ Create a form dynamically to send a POST request
                    let form = document.createElement("form");
                    form.method = "POST";
                    form.action = "{{ route('mfa-challenge.cancel') }}";

                    // ✅ Add CSRF token for Laravel security
                    let csrfInput = document.createElement("input");
                    csrfInput.type = "hidden";
                    csrfInput.name = "_token";
                    csrfInput.value = "{{ csrf_token() }}";

                    form.appendChild(csrfInput);
                    document.body.appendChild(form);

                    // ✅ Submit the form to send a POST request
                    form.submit();
                });
            @endif
        });
    </script>
    <script>
        function handleResend(event) {
            event.preventDefault();

            const button = document.getElementById('resend-button');
            const form = document.getElementById('resend-form');
            const csrfToken = form.querySelector('input[name="_token"]').value;

            // Show loading screen
            document.querySelector('.loading-screen').style.display = 'block';

            // Disable button and start countdown
            let timeLeft = 15;
            button.disabled = true;
            const originalText = "Resend OTP";
            button.innerText = `Wait ${timeLeft}s`;

            const countdown = setInterval(() => {
                timeLeft--;
                button.innerText = `Wait ${timeLeft}s`;
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    button.disabled = false;
                    button.innerText = originalText;
                }
            }, 1000);

            // Send AJAX POST request
            fetch(form.action, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({}) // optional: add payload here if needed
                })
                .then(response => {
                    document.querySelector('.loading-screen').style.display = 'none';
                    if (!response.ok) {
                        throw new Error("Resend OTP failed.");
                    }

                    // Optional: show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP has been resent!'
                    });
                })
                .catch(error => {
                    document.querySelector('.loading-screen').style.display = 'none';
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: 'Could not resend OTP. Please try again.',
                        confirmButtonText: 'OK'
                    });

                    // Reset button immediately if failed
                    clearInterval(countdown);
                    button.disabled = false;
                    button.innerText = originalText;
                });
        }
    </script>
</body>

</html>
