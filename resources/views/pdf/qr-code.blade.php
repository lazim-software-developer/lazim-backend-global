<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Ensure proper rendering and touch zooming -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        /* Center the content */
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
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
@if(empty($data))
<script type="text/javascript">
    window.location = "/admin/assets";
</script>
@endif
    <div>
        <div>
            <h2>Asset Code : 
            @if($data)
            {{$data['asset_code']}}
            @else
            {{'NA'}}
            @endif
            </h2>
        </div>
        <div>
        <h2>QR Code:</h2>
        <h2>@if($data)
            {!! $data['qr_code'] !!}
            @else
            {{'NA'}}
            @endif</h2>         
        </div>
        <!-- Print button -->
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
