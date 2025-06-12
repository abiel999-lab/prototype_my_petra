<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>LDAP Access Notification</title>
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

        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }

        table {
            color: #000;
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
        }

        p {
            color: #555;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <img src="https://login.petra.ac.id/images/logo-ukp.png" alt="Petra Logo">

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <h2>LDAP Access Alert</h2>
                <p>The following user has accessed the <strong>Manage LDAP User</strong> section:</p>

                <p><strong>Name:</strong> {{ $name }}</p>
                <p><strong>Email:</strong> {{ $email }}</p>
                <p><strong>Access Time:</strong> {{ $time }}</p>

                <p>The user has also submitted a selfie or identification document (see attachment).</p>

                <p style="color: #d9534f;"><strong>Please verify if this access is authorized.</strong></p>

                <p style="margin-top: 20px;">Regards,<br><strong>Petra Christian University Security System</strong></p>
            </td>
        </tr>
    </table>
</body>

</html>
