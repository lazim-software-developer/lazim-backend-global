<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Receipt Generated</title>
    </head>

    <body>
        <h1>New Receipt Generated</h1>
        <p>Dear Resident,</p>
        <p>A new receipt has been generated for you. Please find the details below:</p>
        <ul>
            <li>Receipt Number: {{ $receipt->receipt_number ?? 'N/A' }}</li>
            <li>Date: {{ $receipt->date ?? 'N/A' }}</li>
            <li>Amount: {{ ($receipt->amount) ?? 'N/A' }}</li>
        </ul>
        <p>The full receipt is attached to this email as a PDF.</p>
        <p>Thank you for your prompt attention to this matter.</p>
    </body>

</html>
