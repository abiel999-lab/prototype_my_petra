<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>LDAP OTP Verification</title>
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
                <h2>LDAP Access Verification</h2>
                <p>You are receiving this email because an OTP was requested to access the Manage LDAP User section.</p>

                <h3 align="center" style="font-size: 30px; font-weight: bold; margin-top: 30px;">
                    {{ $otpCode }}
                </h3>

                <p style="margin-top: 10px;">This OTP will expire in 10 minutes.</p>
                <p>If this request was not made by you, please contact the security team immediately.</p>

                <p style="margin-top: 20px;">Regards,<br><strong>Petra Christian University IT System</strong></p>
            </td>
        </tr>
    </table>

</body>

</html>
