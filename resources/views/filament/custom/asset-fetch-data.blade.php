<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Ensure proper rendering and touch zooming -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        /* Center the content */
        .qr-codes-list {
            display: flex;
            flex-wrap: wrap;
            list-style: none;
            padding: 0;
        }

        .qr-code-item {
            margin: 10px;
            flex: 1 1 150px; /* Adjust the width as needed */
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .qr-code-item span {
            margin-top: 5px;
        }

        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: flex-start; /* Align items at the start */
            align-items: center;
            flex-direction: column;
            box-sizing: border-box; /* Ensure padding doesn't affect the width */
        }

        .container {
            padding: 50px 20px 20px 20px; /* Padding: top, right, bottom, left */
            width: 100%; /* Ensure container takes full width */
            box-sizing: border-box; /* Ensure padding doesn't affect the width */
        }

        /* Stylish print button */
        .print-button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #4F46E5; /* blue background */
            color: white; /* White text */
            border: none; /* No border */
            border-radius: 5px; /* Rounded corners */
            transition: background-color 0.3s ease; /* Smooth background color change */
        }

        .print-button:hover {
            background-color: #362DCC; /* Darker shade of green on hover */
        }

        /* Hide elements from printing */
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @if(empty($data))
        <script type="text/javascript">
            window.location = "/admin/assets";
        </script>
        @else
        <h1>QR Codes</h1>
        <ul class="qr-codes-list">
            @foreach($data as $qr)
                <li class="qr-code-item">
                    {!! $qr['qr_code'] !!}
                    <span>{{$qr['asset_code']}}</span>
                </li>
            @endforeach
        </ul>
        @endif
        <button onclick="printDocument()" class="print-button">Print</button>
        <button onclick="window.location.href='/admin/assets'" class="print-button">Go Back</button>
    </div>
    <script>
        // Function to open the print dialog
        function printDocument() {
            window.print();
        }
    </script>
</body>
</html>
