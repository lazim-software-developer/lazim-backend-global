<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome to Lazim</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <p>Your Account has been created with the following details:</p>
        <ul>
            <li>Name: {{ $subContractor->name }}</li>
            <li>Email: {{ $subContractor->email }}</li>
            <li>Phone: {{ $subContractor->phone }}</li>
            <li>Company: {{ $subContractor->company_name }}</li>
            <li>Service Provided: {{ $subContractor->service_provided }}</li>
            <li>Start Date: {{ \Carbon\Carbon::parse($subContractor->start_date)->format('m-d-Y') }}</li>
            <li>End Date: {{ \Carbon\Carbon::parse($subContractor->end_date)->format('m-d-Y') }}</li>
        </ul>
    </div>
</body>

</html>
