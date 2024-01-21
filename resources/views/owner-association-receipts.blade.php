<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt Voucher</title>
</head>
<body>
    <div >
        <!-- Header -->
        <div style="text-align: center;">
            <h2 style="font-size: 18px; " class="text-2xl font-bold text-gray-900">{{$data->type == 'building' ? $data->building?->name.' - '.$data->flat?->property_number : $data->receipt_to}}</h2>
            <h2 style="font-size: 18px; " class="text-2xl font-bold text-gray-900">{{$data->owner?->name}}</h2>
            <p style="font-size: 12px; ">{{$data->owner?->address}}</p>
            <p style="font-size: 12px;">E-Mail: {{$data->owner?->email}}</p>
            <h3 style="font-size: 16px; margin-top: 10px;">Receipt Voucher</h3>
        </div>
        
        <!-- Voucher Info -->
        <div style="display: flex; justify-content: space-between; padding-top: 2mm;">
            <p style="font-size: 12px;"><strong>No. :</strong> {{$data->receipt_number}}</p>
            <p style="font-size: 12px;"><strong>Dated :</strong> {{$data->date}}</p>
        </div>
        
        <!-- Through -->
        <p style="font-size: 12px; margin: 0;"><strong>Through :</strong> {{$data->received_in}}</p>

        <!-- Table for Particulars and Amount -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 5mm;">
            <tr>
                <td style="border-bottom: 1px solid #000; border-top: 1px solid #000; padding: 2mm 0; font-size: 12px;" ><strong>Particulars</strong></td>
                <td style=" border-top: 1px solid #000; padding: 2mm 0; font-size: 12px; text-align: right;"><strong>Amount</strong></td>
            </tr>
            <tr>
                <td style="padding: 2mm 0; font-size: 12px; "><strong>Account :</strong> </td>
                <td style="padding: 2mm 0; font-size: 12px; border-top: 1px solid #000;"> </td>
                
            </tr>
            <tr>
                <td style="padding: 2mm 0; font-size: 12px;">{{$data->payment_reference}}</td>
                <td style="padding: 2mm 0; font-size: 12px; text-align: right;">{{$data->amount}}</td>
            </tr>
        </table>

        <!-- Spacer -->
        <div style="flex-grow: 1;"></div>
        <table style="position: absolute; bottom: 80mm;  right: 1mm; ">
            <tr>
                <td style="padding: 2mm 0; font-size: 12px; text-align: right;"><strong>Total AED :</strong>  {{$data->amount}}</td>
            </tr>
        </table>

        <!-- Footer -->
        <div style="position: absolute; bottom: 20mm; left: 15mm; right: 5mm;">
            <p style="font-size: 12px;"><strong>On Account of :</strong> {{$data->on_account_of}} received in {{$data->received_in}} </p>
            <p style="font-size: 12px; padding-top: 2mm;"><strong>Amount (in words) :</strong> {{$data->totalWords}}</p>
            <div style="display: flex; justify-content: space-between; padding-top: 10mm;">
                <p style="font-size: 12px;">&nbsp;</p> <!-- Spacer -->
                <p style="font-size: 12px;">Authorised Signatory</p>
            </div>
        </div>
    </div>
</body>
</html>
