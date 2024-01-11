<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt Voucher</title>
</head>
<body>
    <div style="width: 200mm; height: 270mm; margin: 0 auto; padding: 20mm 15mm 15mm; box-sizing: border-box; border: 1px solid #000; font-family: Arial, sans-serif; position: relative;">
        
        <!-- Header -->
        <div style="text-align: center;">
            <h2 style="font-size: 18px; ">{{$oam->name}} Owners Associat BO</h2>
            <p style="font-size: 12px; ">{{$oam->address}}</p>
            <p style="font-size: 12px; ">Emirate: Dubai</p>
            <p style="font-size: 12px;">E-Mail: {{$oam->email}}</p>
            <h3 style="font-size: 16px; margin-top: 10px;">Receipt Voucher</h3>
        </div>
        
        <!-- Voucher Info -->
        <div style="display: flex; justify-content: space-between; padding-top: 2mm;">
            <p style="font-size: 12px;"><strong>No. :</strong> {{$data['receipt_id']}}</p>
            <p style="font-size: 12px;"><strong>Dated :</strong> {{$data['date']}}</p>
        </div>
        
        <!-- Through -->
        <p style="font-size: 12px; margin: 0;"><strong>Through :</strong> {{$data['through']}}</p>

        <!-- Table for Particulars and Amount -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 5mm;">
            <tr>
                <td style="border-bottom: 1px solid #000; border-top: 1px solid #000; padding: 2mm 0; font-size: 12px;"><strong>Particulars</strong></td>
                <td style="border-bottom: 1px solid #000; border-top: 1px solid #000; padding: 2mm 0; font-size: 12px; text-align: right;"><strong>Amount</strong></td>
            </tr>
            <tr>
                <td style="padding: 2mm 0; font-size: 12px;"><strong>Account :</strong> </td>
                
            </tr>
            <tr>
                <td style="padding: 2mm 0; font-size: 12px;">{{$data['account']}}</td>
                <td style="padding: 2mm 0; font-size: 12px; text-align: right;">{{$data['amount']}}</td>
            </tr>
        </table>

        <!-- Spacer -->
        <div style="flex-grow: 1;"></div>

        <!-- Footer -->
        <div style="position: absolute; bottom: 20mm; left: 15mm; right: 15mm;">
            <p style="font-size: 12px;"><strong>On Account of :</strong> {{$data['on_account_of']}}</p>
            <p style="font-size: 12px; padding-top: 2mm;"><strong>Amount (in words) :</strong> {{$words}}</p>
            <div style="display: flex; justify-content: space-between; padding-top: 10mm;">
                <p style="font-size: 12px;">&nbsp;</p> <!-- Spacer -->
                <p style="font-size: 12px;">Authorised Signatory</p>
            </div>
        </div>

    </div>
</body>
</html>
