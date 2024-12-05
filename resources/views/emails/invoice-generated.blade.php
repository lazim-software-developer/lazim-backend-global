<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Invoice Notification and Payment Instructions</title>
    </head>

    <body>
        <p>Dear {{ $invoice->resident_name ?? 'Resident' }},</p>
        <p>We have generated a new invoice for your account.</p>
        <p>Below are the invoice details:</p>
        <ul>
            <li>Invoice Number: {{ $invoice->invoice_number ?? 'N/A' }}</li>
            <li>Date of Issue: {{ $invoice->date ?? 'N/A' }}</li>
            <li>Due Date: {{ $invoice->due_date ?? 'N/A' }}</li>
            <li>Amount Due: AED {{ ($invoice->rate) ?? 'N/A' }}</li>
        </ul>
        <p>The full invoice is attached to this email as a PDF.</p>
        <p>Please ensure the payment is completed by the due date to avoid any late fees or service interruptions.</p>
        <p>If you have any questions or require further assistance, feel free to reach out to us.</p>
        <p>Thank you for your cooperation.</p>
        <p>Regards,</p>
        <img src="{{url('images/logo.png')}}" alt="Lazim" style="max-width: 100px; height: 50px;">
        <p>{{$pm_oa}}</p>
    </body>

</html>
