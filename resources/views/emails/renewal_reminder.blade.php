<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Contract Renewal Reminder</title>
</head>
<body>
    <p>Dear {{ $contract->sub_contractor_name }},</p>
    <p>Your license will expire in {{ $daysLeft }} days. Please take action before it expires.</p>
    <p>Thank you!</p>
</body>
</html>
