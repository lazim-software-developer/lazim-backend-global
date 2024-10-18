<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Template</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .header {
            background-color: #085b86;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        .body-content {
            padding: 20px;
            color: #333333;
            text-align: left;
        }

        .remarks-history {
            background-color: #e6eef2;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }

        .remarks-history p {
            margin: 10px 0;
            color: #333333;
        }

        .remarks-history strong {
            color: #085b86;
        }

        .footer {
            background-color: #f4f4f4;
            color: #555555;
            text-align: center;
            padding: 20px;
            font-size: 14px;
            border-top: 1px solid #eeeeee;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>{{$complaintType}} Status</h2>
        </div>

        <div class="body-content">
            <p>Dear {{$first_name}},</p>
            <p>We are pleased to inform you that your {{$complaintType}} has been successfully addressed and closed.</p>

            <div class="remarks-history">
                <h4>Remarks History:</h4>
                @foreach ($remarks as $remark)
                    <p>
                        <strong>{{ $remark->created_at->format('d M Y') }}</strong> - Your {{$complaintType}} has been moved to <strong>{{ $remark->status }}</strong>.
                        <br>
                        <strong style="color: black;">Remarks:</strong> {{ $remark->remarks }}
                    </p>
                @endforeach
            </div>

            <p>Thank you for your patience and cooperation.</p>
            <p>Best regards,<br>Lazim Team</p>
        </div>
    </div>
</body>

</html>
