<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Committe Meeting</title>
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
        <h1>Owner Committe Meeting</h1>
        <p>Dear {{$user->first_name}},</p>
        <p>I hope this message finds you well. We would like to inform you about an upcoming Meeting scheduled to take place on {{$meeting->date_time}}. Your participation is crucial, and we look forward to your valuable insights and contributions.</p>
        <p>Your Meeting Details:</p>
        <p>Agenda: {!!$agenda!!}</p>
        <p>Date Time: {{$meeting->date_time}}</p>
        <p>Please come prepared to share your insights on the agenda items mentioned above. If you have any additional topics you would like to discuss, please feel free to discuss.

            If you are unable to attend, wait for the mail where you can find summary of meeting.</p>
        <p>Best regards,</p>
        <p>Lazim Team</p>
    </div>
</body>

</html>