<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Login Violation Alert</title>
    <style>
        img {
            display: block;
            margin-left: auto;
            margin-right: auto;
            padding: 20px;
            max-width: 100%;
            border: none;
            height: 75px;
            max-height: 75px;
        }
    </style>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px;">
    <img src="https://login.petra.ac.id/images/logo-ukp.png" alt="Petra Logo">

    <table width="100%" cellspacing="0" cellpadding="0"
        style="color: #000; max-width: 600px; margin: auto; background: #ffffff; padding: 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">

        <tr>
            <td>
                <h2>Hello, Security Team!</h2>
                <p>We detected multiple failed login attempts for an account:</p>

                <p><strong>Email:</strong> {{ $email }}</p>
                <p><strong>Failed Attempts:</strong> {{ $attempts }}</p>
                <p><strong>Time of Violation:</strong> {{ $timestamp }}</p>

                <h3 align="center" style="font-size: 18px; font-weight: bold; color: red; margin-top: 20px;">
                    Immediate action may be required.
                </h3>

                <p style="margin-top: 10px;">If this was not an authorized attempt, please investigate this login.</p>

                <p style="margin-top: 20px;">Regards,<br><strong>Petra Christian University Security Team</strong></p>
            </td>
        </tr>
    </table>
</body>

</html>
