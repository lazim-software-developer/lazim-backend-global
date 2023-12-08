<!-- resources/views/pdf.blade.php -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Service Charge and Utility Bills Payment Undertaking</title>
    <style>
        /* Add any additional styles you need */
        body {
            font-family: 'Arial', sans-serif;
        }
    </style>
</head>

<body>

    <h4>Subject: Service Charge and Utility Bills Payment Undertaking</h4>

    <p>
        Date: {{ now()->format('d-F-Y') }}
    </p>

    <p>
        To, <br>
        {{$data['ownerAssociation']}} <br>
        Dubai, UAE
    </p>

    <p>
        Dear Sir,
    </p>

    <p>
        I, {{$data['username']}}, the Buyer for Unit/Shop No/s, {{$data['flat']}} <br>
        for Building {{$data['building']}} as explained to me by the Seller, am aware of the service charge which will be due to me from the next invoice onwards and utility bills with immediate effect as of this letter date and agree/undertake full responsibility to pay.
    </p>

    <p>
        With due respect on this undertaking, I do hereby confirm to comply with the Community Rules for the safety and convenient living.
    </p>

    <p>
        I can be reached at my contact number/s {{$data['phone']}} and email address {{$data['email']}}.
    </p>

    <p>
        Sincerely Yours,
    </p>

    <p>
        Buyer’s Signature ____________________
    </p>
    <p>
        Seller's Signature ____________________
    </p>

</body>

</html>