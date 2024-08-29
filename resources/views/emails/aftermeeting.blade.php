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
        <p>I trust this email finds you well. Following our recent Meeting held on {{$meeting->date_time}} , I would like to provide you with a brief recap.</p>
        <p>Your Meeting Details:</p>
        <p>Agenda: {!!$agenda!!}</p>
        <p>Date Time: {{$meeting->date_time}}</p>
        <p>Recap of the Meeting: {!!$meeting_summary!!}</p>
        <p>We value your feedback. If you have any thoughts or additional input related to the meeting or its outcomes, please feel free to share them.
            Thank you to everyone who contributed to the meeting's success. If you were unable to attend and have any questions or need further clarification, please don't hesitate to reach out.

            Looking forward to our continued collaboration and success.</p>
        <p>Best regards,</p>
        <p>Lazim Team</p>
    </div>
</body>

</html>