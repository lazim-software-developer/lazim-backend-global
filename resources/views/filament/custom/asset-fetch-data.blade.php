<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Codes</title>
    <style>
    .container {
    max-width: 1200px;
    padding: 20px;
    overflow: hidden; /* Clean up overflow */
}

.headding {
    padding-bottom: 20px;
}

.asset {
    display: flex;
    justify-content: space-between; /* Aligns items to the left */
}

table {
    width: 100%; /* Full width of the container */
    border-collapse: separate; /* Ensures that the spacing between cells can be adjusted */
    border-spacing: 10px 10px; /* Horizontal and vertical spacing between cells */
}

td {
    text-align: center; /* Centers the content within the cell */
    vertical-align: top; /* Aligns content to the top of the cell */
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
    <h1 class="headding">QR Codes</h1>
    <table>
    @php $i = 0; @endphp
    @foreach($data as $qr)
        @if ($i % 4 == 0) 
            @if ($i != 0) 
                </tr>
            @endif
            <tr>
        @endif
        @php
            $qrCodeData = strlen($qr['qr_code']) > 200 ? substr($qr['qr_code'], 0, 200) : $qr['qr_code'];
        @endphp
        <td>
            <h2>{{ $qr['asset_code'] }}</h2>
            <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::size(150)->generate($qrCodeData)) }}" alt="QR Code for {{ $qr['asset_code'] }}"/>
        </td>
        @php $i++; @endphp
    @endforeach
    @if ($i % 4 != 0) <!-- Ensures the last row is closed properly -->
        </tr>
    @endif
</table>

    <div >
    </div>
    @endif
</div>


    <script>
        // Function to open the print dialog
        function printDocument() {
            window.print();
        }
    </script>
</body>
</html>
