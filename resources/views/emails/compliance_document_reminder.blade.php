<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Compliance Document Expiry Reminder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            background-color: white;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .document-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 15px 0;
        }
        .warning {
            color: #dc3545;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Compliance Document Expiry Reminder</h2>
    </div>

    <div class="content">
        <p>Dear {{$complianceDocument->vendor->name}},</p>

        <p>This is an important notification regarding your compliance document.</p>

        <div class="document-info">
            <p><strong>Document Name:</strong> {{$complianceDocument->doc_name}}</p>
            <p><strong>Expiry Date:</strong> {{ \Carbon\Carbon::parse($complianceDocument->expiry_date)->format('m-d-Y') }}</p>
            <p class="warning">⚠️ Your document will expire in {{ $daysLeft }} days.</p>
        </div>

        <p>Please ensure to renew your document before the expiration date to maintain compliance.</p>

        <div class="footer">
            <p>Best regards,<br>The Lazim Team</p>
        </div>
    </div>
</body>
</html>
