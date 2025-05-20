<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>LDAP Registration Confirmed</title>
    <style>
        img {
            display: block;
            margin-left: auto;
            margin-right: auto;
            padding: 20px;
            max-width: 100%;
            height: 75px;
        }
        a.button {
            display: inline-block;
            background-color: #007BFF;
            color: white;
            padding: 12px 24px;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px;">
    <img src="https://login.petra.ac.id/images/logo-ukp.png" alt="Petra Logo">
    <table width="100%" cellspacing="0" cellpadding="0" style="color: #000; max-width: 600px; margin: auto; background: #ffffff; padding: 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <tr>
            <td>
                <h2>Hello!</h2>
                <p>Your LDAP-based My Petra account has been created successfully.</p>
                <p>Here are your credentials:</p>
                <ul>
                    <li>Email: <strong>{{ $email }}</strong></li>
                    <li>Password: <strong>changeme</strong></li>
                </ul>
                <p>You may now <a href="{{ url('/login') }}" class="button">Login to My Petra</a></p>
                <p style="margin-top: 20px;">For your security, we recommend changing your password immediately through the <a href="{{ url('/forgot-password') }}">Forgot Password</a> page.</p>
                <p>Regards,<br><strong>Petra Christian University</strong></p>
            </td>
        </tr>
    </table>
</body>
</html>
