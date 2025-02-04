<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contract Renewal Reminder</title>
</head>
<body style="font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #2d3748; margin: 0; padding: 0; background-color: #f7fafc;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 20px; margin-bottom: 20px;">
        <!-- Header -->
        <div style="background-color: #4299e1; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Contract Renewal Notice</h1>
        </div>

        <!-- Content -->
        <div style="padding: 30px;">
            <p style="margin-bottom: 20px; font-size: 16px;">
                Dear <span style="font-weight: 600;">{{$contract->name}}</span>,
            </p>

            <p style="margin-bottom: 25px; color: #4a5568;">
                We are pleased to inform you that your contract is about to get expired. Below are the details:
            </p>

            <!-- Contract Details Card -->
            <div style="background-color: #f8fafc; border-radius: 6px; padding: 20px; margin-bottom: 25px; border-left: 4px solid #4299e1;">
                <div style="margin-bottom: 12px;">
                    <p style="margin: 8px 0;"><strong style="color: #2d3748; width: 140px; display: inline-block;">Company:</strong>
                        <span style="color: #4a5568;">{{$contract->company_name}}</span>
                    </p>
                    <p style="margin: 8px 0;"><strong style="color: #2d3748; width: 140px; display: inline-block;">Services:</strong>
                        <span style="color: #4a5568;">
                            {{ $contract->services->pluck('name')->implode(', ') }}
                        </span>
                    </p>
                    <p style="margin: 8px 0;"><strong style="color: #2d3748; width: 140px; display: inline-block;">Start Date:</strong>
                        <span style="color: #4a5568;">{{ \Carbon\Carbon::parse($contract->start_date)->format('m-d-Y') }}</span>
                    </p>
                    <p style="margin: 8px 0;"><strong style="color: #2d3748; width: 140px; display: inline-block;">End Date:</strong>
                        <span style="color: #4a5568;">{{ \Carbon\Carbon::parse($contract->end_date)->format('m-d-Y') }}</span>
                    </p>
                </div>
            </div>

            <!-- Alert Box -->
            <div style="background-color: #fed7d7; border-radius: 6px; padding: 15px; margin-bottom: 25px; color: #c53030;">
                <strong>Important:</strong> Your license will expire in {{ $daysLeft }} days. Please take action before it expires.
            </div>

            <p style="margin-bottom: 25px; color: #4a5568;">
                If you have any questions or require further assistance, please feel free to reach out.
            </p>

            <p style="margin-bottom: 5px; color: #4a5568;">
                Best regards,
            </p>
            <p style="margin-bottom: 0; font-weight: 600; color: #2d3748;">
                The Lazim Team
            </p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8fafc; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0; font-size: 14px; color: #718096;">© {{date('Y')}} Lazim. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
