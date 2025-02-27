<!-- resources/views/emails/pm-building-detachment.blade.php -->
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

            .warning {
                color: #721c24;
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                padding: 10px;
                border-radius: 4px;
                margin: 10px 0;
            }

        </style>
    </head>

    <body>
        <div class="container">
            @if($type === 'due')
            <h2>Property Management Contract Expiring Tomorrow</h2>
            <p>Dear Property Manager,</p>
            <p>This is a notification regarding the property management contract for <strong>{{ $buildingName }}</strong>.</p>
            <div class="warning">
                <strong>Important:</strong> Your contract will expire tomorrow ({{ $dueDate }}).
            </div>
            <p>Please note:</p>
            <ul>
                <li>If the contract expires without renewal, you will lose access to manage this property</li>
                <li>Any pending tasks or approvals should be completed before expiration</li>
            </ul>
            <p>If you wish to continue managing this property, please contact the building owner or administrator to renew your contract before it expires.</p>
            @else
            <h2>Property Management Contract Expired</h2>
            <p>Dear Property Manager,</p>
            <p>This is to inform you that your property management contract for <strong>{{ $buildingName }}</strong> has expired.</p>
            <div class="warning">
                <strong>Important:</strong> Your access to manage this property has been revoked.
            </div>
            <p>As a result:</p>
            <ul>
                <li>You no longer have access to manage this property</li>
                {{-- <li>All associated users have lost their access</li> --}}
                <li>Any pending tasks or approvals will need to be handled by the new property manager</li>
            </ul>
            <p>If you believe this is in error or wish to renew your contract, please contact the building owner or administrator immediately.</p>
            @endif

            <p>Best regards,<br>Your Building Management System</p>
        </div>
    </body>

</html>
