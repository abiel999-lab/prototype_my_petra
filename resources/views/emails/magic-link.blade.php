<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Login Without Password</title>
    <style>
        img {
            display: block;
            margin: auto;
            padding: 20px;
            height: 75px;
        }

        a.button {
            display: inline-block;
            background-color: #007bff;
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

    <table width="100%" cellspacing="0" cellpadding="0"
        style="color: #000; max-width: 600px; margin: auto; background: #ffffff; padding: 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <tr>
            <td>
                <h2>Hello!</h2>
                <p>You requested to log in without using a password.</p>

                <div style="text-align: center; margin: 20px 0;">
                    <a href="{{ $link }}" class="button">Login Now</a>
                </div>

                <p>This link will expire in 15 minutes.</p>
                <p>If you didn’t request this, you can safely ignore this email.</p>

                <p style="margin-top: 20px;">Regards,<br><strong>Petra Christian University</strong></p>
            </td>
        </tr>
    </table>
</body>

</html>
