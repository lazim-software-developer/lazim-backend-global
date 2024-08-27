<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            overflow: hidden;
        }

        .top-half {
            background-color: #E6EEF2;
            padding: 20px;
            text-align: center;
        }

        .bottom-half {
            background-color: #085B86;
            padding: 20px;
            text-align: center;
            color: #FFFFFF;
        }

        .header-image {
            width: 80px;
            display: inline-block;
            position: relative;
            top: -20px;
        }

        .icon-circle {
            border-radius: 50%;
            background-color: #FFFFFF;
            display: inline-block;
            padding: 10px;
        }

        .header-image::after {
            content: '';
            display: block;
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 50%;
            background-color: #ADD8E6;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="top-half">
            <h3>Hello {{$OaName}},</h3>
            <p>This is a test email to verify that the email system is working correctly.</p>
            <br>
            <img src="{{ asset('images\Lazim horizontl.png') }}" alt="Logo" class="header-image">
        </div>
        <div class="bottom-half">
            <h3>Email System Test Successful!</h3>
            <p>If you received this email, it means the test was successful and your email configuration is working as expected.</p>
            <p>Thank you for testing.</p>
            <p>Best regards,<br>Lazim Team</p>
        </div>
    </div>
</body>

</html>
