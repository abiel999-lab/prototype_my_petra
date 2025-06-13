<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Ticket Confirmation [{{ $ticket_code }}]</title>
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

        .code {
            display: inline-block;
            background-color: #eef;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
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
                <h2>Ticket Confirmation</h2>

                <p>Hello {{ $name }},</p>
                <p>Your support ticket has been created successfully. Here are the details:</p>

                <p><strong>Ticket Code:</strong> <span class="code">{{ $ticket_code }}</span></p>
                <p><strong>Issue Type:</strong> {{ $issue_type }}</p>
                <p><strong>Date:</strong> {{ $submitted_at }}</p>
                <p><strong>Message:</strong></p>
                <p style="background-color: #f2f2f2; padding: 10px; border-radius: 5px;">{{ $message_body }}</p>

                <p>Please keep this code safe and use it when following up on your request.</p>

                <p style="margin-top: 20px;">Regards,<br><strong>Petra Christian University Support System</strong></p>
            </td>
        </tr>
    </table>
</body>

</html>
