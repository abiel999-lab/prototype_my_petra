<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Customer Support</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://login.petra.ac.id/css/bootstrap.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/style.css" rel="stylesheet">
    <link href="https://login.petra.ac.id/css/mmenu.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://login.petra.ac.id/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="shortcut icon" href="https://login.petra.ac.id/images/favicon.png">
    <link rel="stylesheet" href="https://login.petra.ac.id/css/loading.css">
    {!! NoCaptcha::renderJs() !!}
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
                <div class="login-img-forgot"></div>
            </div>
            <div class="col-sm-8 login-section-wrapper">
                <div class="row d-flex justify-content-center">
                    <div class="col-sm-6">
                        <div class="login-wrapper mt-4">
                            <h1 class="login-title">Need Help?</h1>
                            <p>Fill in the form below and we will contact you as soon as possible.</p>

                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('customer-support.send') }}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="name">Your Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>

                                <div class="mb-3" id="email_group">
                                    <label for="email" class="form-label">
                                        Your Email or Contact <small class="text-muted">(enter "-" if you dont have email)</small>
                                    </label>
                                    <input type="text" class="form-control" id="email" name="email" required
                                        placeholder="Enter email (or '-' if you don't have one)">
                                </div>

                                <div class="mb-3">
                                    <label for="issue_type">Issue Type</label>
                                    <select class="form-control" id="issue_type" name="issue_type" required>
                                        <option value="">Select an issue type</option>
                                        <option value="login problem">Login problem</option>
                                        <option value="mfa problem">MFA problem</option>
                                        <option value="service problem">Service problem</option>
                                        <option value="other problem">Other problem</option>
                                    </select>
                                </div>

                                <div class="mb-3 d-none" id="phone_number_group">
                                    <label for="phone_number">Phone Number</label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number">
                                </div>

                                <div class="mb-3 d-none" id="attachment_group">
                                    <label for="attachment">Upload Selfie Holding KTM/ or any Identification</label>
                                    <input type="file" class="form-control" id="attachment" name="attachment"
                                        accept=".jpg,.jpeg,.png,.pdf">
                                </div>

                                <div class="mb-3">
                                    <label for="message">Describe Your Issues</label>
                                    <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                                </div>

                                <div class="mb-3">
                                    {!! NoCaptcha::display() !!}
                                </div>

                                <button type="submit" class="btn btn-lg login-btn">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer text-center mt-5 mb-3">
            <strong>&copy; 2025 <a href="https://petra.ac.id">Petra Christian University</a>.</strong> All rights
            reserved.
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const issueType = document.getElementById('issue_type');
            const emailGroup = document.getElementById('email_group');
            const phoneGroup = document.getElementById('phone_number_group');
            const attachGroup = document.getElementById('attachment_group');

            function toggleFields() {
                const issue = issueType.value.toLowerCase();
                if (issue === 'login problem') {
                    phoneGroup.classList.remove('d-none');
                    attachGroup.classList.remove('d-none');
                    emailGroup.querySelector('input').required = false;
                } else if (issue === 'mfa problem') {
                    phoneGroup.classList.add('d-none');
                    attachGroup.classList.remove('d-none');
                    emailGroup.querySelector('input').required = true;
                } else {
                    phoneGroup.classList.add('d-none');
                    attachGroup.classList.add('d-none');
                    emailGroup.querySelector('input').required = true;
                }
            }

            issueType.addEventListener('change', toggleFields);
        });
    </script>
</body>

</html>
