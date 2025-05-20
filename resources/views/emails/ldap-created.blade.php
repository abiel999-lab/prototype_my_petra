<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your My Petra LDAP Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }

        img {
            display: block;
            margin: 0 auto 20px auto;
            height: 75px;
        }

        .email-container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            color: #333;
        }

        .button {
            display: inline-block;
            color: white;
            padding: 12px 24px;
            font-size: 16px;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
        }

        .btn-login {
            background-color: #007BFF;
        }

        .btn-password {
            background-color: #28a745;
        }

        ul {
            margin: 15px 0;
            padding-left: 20px;
        }

        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #777;
            text-align: center;
        }
    </style>
</head>

<body>
    <img src="https://login.petra.ac.id/images/logo-ukp.png" alt="Petra Christian University">

    <div class="email-container">
        <h2>Hello!</h2>
        <p>Welcome to <strong>My Petra</strong>. Your LDAP-based account has been successfully registered.</p>

        <p>Here are your account details:</p>
        <ul>
            <li><strong>Email:</strong> {{ $email }}</li>
            <li><strong>Password:</strong> changeme</li>
        </ul>

        <p style="margin-top: 20px;">You can now log in using the button below:</p>

        <div style="text-align: center;">
            <a href="{{ url('/login') }}" class="button btn-login">Login to My Petra</a>
        </div>

        <p style="margin-top: 25px;">🔒 For your security, we strongly recommend that you change your password immediately:</p>

        <div style="text-align: center;">
            <a href="{{ url('/forgot-password') }}" class="button btn-password">Change Password</a>
        </div>

        <p style="margin-top: 30px;">Regards,<br><strong>Petra Christian University</strong></p>
    </div>

    <div class="footer">
        &copy; {{ now()->year }} Petra Christian University. All rights reserved.
    </div>
</body>
</html>
