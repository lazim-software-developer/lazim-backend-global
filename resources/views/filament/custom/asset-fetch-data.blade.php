<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assets QR Codes</title>
    <style>
        .container {
            max-width: 1200px;
            padding: 20px;
            overflow: hidden;
            page-break-after: always; /* Ensure each set of 25 QR codes starts on a new page */
        }

        .headding {
            padding-bottom: 20px;
        }

        table {
            width: 100%; /* Full width of the container */
            border-collapse: separate; /* Ensures that the spacing between cells can be adjusted */
            border-spacing: 10px 10px; /* Horizontal and vertical spacing between cells */
        }

        td {
            text-align: center; /* Centers the content within the cell */
            vertical-align: top; /* Aligns content to the top of the cell */
            width: 20%; /* Ensures 5 QR codes per row */
        }

        img {
            max-width: 100%; /* Ensure the QR code image fits within the cell */
            height: auto; /* Maintain the aspect ratio */
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
    <!-- <h2 class="headding">Assets QR Code:</h2> -->
    <table>
    @php $i = 0; @endphp
    @foreach($data as $qr)
        @if ($i % 5 == 0) <!-- Adjust to make 5 QR codes per row -->
            @if ($i != 0) 
                </tr>
            @endif
            <tr>
        @endif
        <td>
            <h5>Name: {{$qr['name']}}</h5>
            <h5 style="margin-top: -20px;">Code: {{ $qr['asset_code'] }}</h5>
            <img src="{{ $qr['qr_code'] }}" alt="QR Code">
        </td>   
        @php $i++; @endphp
    @endforeach
    @if ($i % 5 != 0) <!-- Ensures the last row is closed properly -->
        </tr>
    @endif
    </table>
    @endif
</div>
<script>
    // Function to open the print dialog
    // function printDocument() {
    //     window.print();
    // }
</script>
</body>
</html>
