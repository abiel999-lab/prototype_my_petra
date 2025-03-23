<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>MFA Verification</title>
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

        a.button {
            display: inline-block;
            background-color: #28a745;
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
                <p>You are receiving this email because we received a login verification request from a new device.</p>

                <div style="text-align: center;">
                    <a href="{{ $link }}" class="button">Verify Login</a>
                </div>

                <p style="margin-top: 20px;">If you did not initiate this request, you can safely ignore this email.</p>
                <p style="margin-top: 20px;">Regards,<br><strong>Petra Christian University</strong></p>
            </td>
        </tr>
    </table>
</body>

</html>
