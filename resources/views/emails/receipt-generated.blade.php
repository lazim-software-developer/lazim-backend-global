<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Receipt Confirmation for Your Payment</title>
    </head>

    <body>
        <p>Dear {{ $receipt->user->name}},</p>

        <p>We are pleased to confirm that we have received your payment. Please find the details of your receipt below:</p>

        <h3>Receipt Details:</h3>
        <ul>
            <li>Receipt Number: {{ $receipt->receipt_number ?? 'N/A' }}</li>
            <li>Date: {{ $receipt->date ?? 'N/A' }}</li>
            @if($receipt->building?->name)
            <li>Building: {{ $receipt->building->name }}</li>
            @endif
            @if($receipt->flat?->property_number)
            <li>Flat Number: {{ $receipt->flat->property_number }}</li>
            @endif
            <li>Amount Paid: {{ $receipt->amount ?? 'N/A' }}</li>
            <li>Payment Method: {{ $receipt->payment_method ?? 'N/A' }}</li>
        </ul>

        <p>Thank you for your prompt payment. If you have any questions or require further assistance, please feel free to contact us.</p>

        <p>We appreciate your continued trust in our services.</p>

        <p>Regards,</p>
        <img src="{{url('images/logo.png')}}" alt="Company Logo" style="max-width: 100px; height: 50px;">
        <p>{{ auth()->user()?->first_name}}</p>
    </body>

</html>
