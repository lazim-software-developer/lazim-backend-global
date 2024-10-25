<!-- resources/views/emails/building-detachment.blade.php -->
<!DOCTYPE html>
<html>

    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
            }

            .container {
                padding: 20px;
                max-width: 600px;
                margin: 0 auto;
            }

            .button {
                display: inline-block;
                padding: 10px 20px;
                background-color: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 15px;
            }

        </style>
    </head>

    <body>
        <div class="container">
            @if($type === 'due')
            <h2>Building Contract Expiring Tomorrow</h2>
            <p>Dear Facility Manager,</p>
            <p>This is to inform you that your contract for managing <strong>{{ $buildingName }}</strong> will expire tomorrow ({{ $dueDate }}).</p>
            <p>If you wish to continue managing this building, please renew your contract before it expires. Otherwise, the building will be automatically detached from your portfolio tomorrow.</p>
            {{-- <a href="{{ config('app.url') }}/buildings/renew" class="button">Renew Contract</a> --}}
            @else
            <h2>Building Contract Expired</h2>
            <p>Dear Facility Manager,</p>
            <p>This is to inform you that your contract for managing <strong>{{ $buildingName }}</strong> has expired and the building has been detached from your portfolio.</p>
            <p>If you wish to manage this building again, please submit a new contract request.</p>
            {{-- <a href="{{ config('app.url') }}/buildings/request" class="button">Request New Contract</a> --}}
            @endif

            <p>If you have any questions, please contact your administrator.</p>
            <p>Best regards,<br>Your Building Management System</p>
        </div>
    </body>

</html>
