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
        }
        
        img {
            padding: 20px;

        }
        .headding{
            padding-bottom: 20px;
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
            @foreach($data as $qr)
                <span class="inner_container">
                    @php
                        $qrCodeData = strlen($qr['qr_code']) > 200 ? substr($qr['qr_code'], 0, 200) : $qr['qr_code'];
                    @endphp
                    <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::size(150)->generate($qrCodeData)) }}"/>
                    </span>
            @endforeach
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
