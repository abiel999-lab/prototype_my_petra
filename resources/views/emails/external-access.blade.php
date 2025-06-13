<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>External Access Notification</title>
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

        .alert {
            font-size: 18px;
            font-weight: bold;
            color: #d9534f;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <img src="https://login.petra.ac.id/images/logo-ukp.png" alt="Petra Logo">

    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <h2>Hello, Security Team!</h2>
                <p>A user has accessed the system from an <strong>unrecognized device or OS</strong>. Details are as follows:</p>

                <p><strong>Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Role:</strong> {{ $user->usertype }}</p>
                <p><strong>Time of Access:</strong> {{ $timestamp }}</p>
                <p><strong>IP Address:</strong> {{ $ip }}</p>
                <p><strong>Operating System:</strong> {{ $os }}</p>
                <p><strong>Device Type:</strong> {{ $device }}</p>

                <div class="alert">
                    Please verify if this access is legitimate.
                </div>

                <p style="margin-top: 20px;">Regards,<br><strong>Petra Christian University Security Team</strong></p>
            </td>
        </tr>
    </table>
</body>

</html>
