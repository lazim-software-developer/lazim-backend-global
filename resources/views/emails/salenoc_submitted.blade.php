<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <p>Dear {{$data->signing_authority_name}},</p>
        <p>We are pleased to inform you that the SaleNOC process has been successfully completed. Attached to this email, you will find the signed copy of the Sale NOC document.</p>
        <p>Please review the attached SaleNOC document for your records and further action as required.</p>
        <p>Best regards,</p>
        <p> The Lazim Team</p>
    </div>
</body>
</html>
