<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Invoice Generated</title>
    </head>

    <body>
        <h1>New Invoice Generated</h1>
        <p>Dear Resident,</p>
        <p>A new invoice has been generated for you. Please find the details below:</p>
        <ul>
            <li>Invoice Number: {{ $invoice->invoice_number ?? 'N/A' }}</li>
            <li>Date: {{ $invoice->date ?? 'N/A' }}</li>
            <li>Due Date: {{ $invoice->due_date ?? 'N/A' }}</li>
            <li>Amount: {{ ($invoice->rate) ?? 'N/A' }}</li>
        </ul>
        <p>The full invoice is attached to this email as a PDF.</p>
        <p>Thank you for your prompt attention to this matter.</p>
    </body>

</html>
