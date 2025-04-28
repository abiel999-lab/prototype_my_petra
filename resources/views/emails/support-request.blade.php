<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Customer Support Request</title>
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
                <h2>Hello, Support Team!</h2>
                <p>A new customer support request has been submitted:</p>

                <p><strong>Submitted At:</strong> {{ $submitted_at }}</p>
                <p><strong>Name:</strong> {{ $name }}</p>
                <p><strong>Email:</strong> {{ $email }}</p>
                <p><strong>Issue Type:</strong> {{ $issue_type }}</p>
                <p><strong>Message:</strong></p>
                <p style="background-color: #f2f2f2; padding: 10px; border-radius: 5px;">{{ $message_body }}</p>

                <h3 align="center" style="font-size: 18px; font-weight: bold; color: #007bff; margin-top: 20px;">
                    Please follow up with the user as soon as possible.
                </h3>

                <p style="margin-top: 20px;">Regards,<br><strong>Petra Christian University Support System</strong></p>
            </td>
        </tr>
    </table>
</body>

</html>
