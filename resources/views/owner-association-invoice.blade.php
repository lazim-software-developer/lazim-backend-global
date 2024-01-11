<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl w-full bg-white shadow overflow-hidden sm:rounded-lg p-8">
            <!-- Top "INVOICE" Title -->
            <div class="text-center py-2">
                <span class="text-2xl font-bold text-gray-900">{{$data->type == 'building'? 'INVOICE' : 'TAX INVOICE'}}</span>
            </div>
            
            <!-- Header -->
            <div class="flex justify-between">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">{{$data->owner?->name}}</h2>
                    <p class="text-sm font-bold text-gray-900">{{$data->type == 'building'? $data->building?->name : ' '}}</p>
                    <p class="text-sm text-gray-600">P: {{$data->owner?->phone}}</p>
                    <p class="text-sm text-gray-600">{{$data->owner?->email}}</p>
                    <p class="text-sm text-gray-600">TRN:{{$data->owner?->trn_number}}</p>
                </div>
                <div class="text-right">
                    <img class="h-16" src=https://lazim-dev.s3.ap-south-1.amazonaws.com/{{$data->owner?->profile_photo}} alt="Company Logo">
                </div>
            </div>
            <div class="border-t border-gray-300"></div>
            <!-- Invoice Information -->
            <div class="flex justify-between items-center mt-6 mr-18">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Bill to: {{$data->type == 'building'? $data->building?->name : $data->bill_to}}</h3>
                    <p class="text-sm text-gray-600">Address: {{$data->type == 'building'? $data->building?->area : $data->address}}</p>
                    <p class="text-sm text-gray-600">TRN: {{$data->trn}}</p>
                </div>
                <div class="text-left">
                    <p class="text-sm text-gray-600">Invoice No: {{$data->invoice_number}}</p>
                    <p class="text-sm text-gray-600">Invoice Date: {{$data->date}}</p>
                    <p class="text-sm text-gray-600">Invoice Due Date: {{$data->due_date}}</p>
                    <p class="text-sm text-gray-600">Mode/Terms of Payment: {{$data->mode_of_payment}}</p>
                    <p class="text-sm text-gray-600">Supplier's Name: {{$data->supplier_name}}</p>
                    <p class="text-sm text-gray-600">Job: {{$data->job}}</p>
                    <p class="text-sm text-gray-600">Month: {{$data->month}}</p>


                </div>
            </div>

            <!-- Invoice Table -->
            <div class="mt-6">
                <table class="w-full text-left border-collapse" style="border: 1px solid #ddd; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th class="border-b-2 border-gray-300 p-2 text-xs text-gray-600" style="border: 1px solid #ddd; padding: 8px; text-align: center;">SL NO.</th>
                            <th class="border-b-2 border-gray-300 p-2 text-xs text-gray-600" style="border: 1px solid #ddd; padding: 8px; text-align: center;">Description</th>
                            <th class="border-b-2 border-gray-300 p-2 text-xs text-gray-600" style="border: 1px solid #ddd; padding: 8px; text-align: center;">Quantity</th>
                            <th class="border-b-2 border-gray-300 p-2 text-xs text-gray-600" style="border: 1px solid #ddd; padding: 8px; text-align: center;">Rate</th>
                            <th class="border-b-2 border-gray-300 p-2 text-xs text-gray-600" style="border: 1px solid #ddd; padding: 8px; text-align: center;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-2" style="border: 1px solid #ddd; padding: 8px; text-align: center;">1</td>
                            <td class="p-2" style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{$data->description}}</td>
                            <td class="p-2" style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{$data->quantity}}</td>
                            <td class="p-2" style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{$data->rate}}</td>
                            <td class="p-2" style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{$data->quantity * $data->rate}}</td>
                        </tr>
                        <!-- Add more rows as needed -->
                        <tr>
                            <td class="p-2" style="border: 1px solid #ddd; padding: 20px; text-align: center;"></td>
                            <td class="p-2" style="border: 1px solid #ddd; padding: 20px; text-align: center;"></td>
                            <td class="p-2" style="border: 1px solid #ddd; padding: 20px; text-align: center;"></td>
                            <td class="p-2" style="border: 1px solid #ddd; padding: 20px; text-align: center;"></td>
                            <td class="p-2" style="border: 1px solid #ddd; padding: 20px; text-align: center;"></td>
                        </tr>
                        <tr>
                            <td class="p-2" style="border: 1px solid #ddd; padding: 20px; text-align: center;"></td>
                            <td class="p-2" style="border: 1px solid #ddd; padding: 20px; text-align: center;"></td>
                            <td class="p-2" style="border: 1px solid #ddd; padding: 20px; text-align: center;"></td>
                            <td class="p-2" style="border: 1px solid #ddd; padding: 20px; text-align: center;"></td>
                            <td class="p-2" style="border: 1px solid #ddd; padding: 20px; text-align: center;"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Invoice Total -->
            <div class="flex justify-end mt-6">
                <table class="w-1/2 text-left border-collapse" style="border: 1px solid #ddd; border-collapse: collapse;">
                    <tr>
                        <td class="p-2 text-sm text-gray-600" style="border: 1px solid #ddd; padding: 8px; text-align: center;">Invoice Subtotal</td>
                        <td class="p-2 text-sm text-gray-600" style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{$data->quantity * $data->rate}}</td>
                    </tr>
                    <tr>
                        <td class="p-2 text-sm text-gray-600" style="border: 1px solid #ddd; padding: 8px; text-align: center;">VAT%</td>
                        <td class="p-2 text-sm text-gray-600" style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{$data->tax}}%</td>
                    </tr>
                    <tr>
                        <td class="p-2 text-sm text-gray-600" style="border: 1px solid #ddd; padding: 8px; text-align: center;">VAT Amount</td>
                        <td class="p-2 text-sm text-gray-600" style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{($data->quantity * $data->rate * $data->tax)/100 }}</td>
                    </tr>
                    <tr>
                        <td class="p-2 text-sm text-gray-600 font-bold" style="border: 1px solid #ddd; padding: 8px; text-align: center;">TOTAL AED</td>
                        <td class="p-2 text-sm text-gray-600 font-bold" style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{$data->total}}</td>
                    </tr>
                </table>
            </div>

            <!-- Bank Details -->
            <div class="mt-6">
                <p class="text-sm font-bold text-gray-900">Company Bank Details</p>
                <p class="text-sm text-gray-600">Bank Name: </p>
                <p class="text-sm text-gray-600">A/C No.: </p>
                <p class="text-sm text-gray-600">IBAN: </p>
                <p class="text-sm text-gray-600">Branch & SWIFT Code: </p>
            </div>

            <!-- Amount in Words -->
            <div class="mt-6">
                <p class="text-sm text-gray-600">Amount (in words): AED {{$data->totalWords}}</p>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 mt-6 pt-4 text-xs text-gray-500 text-center">
                **This is computer generated invoice no stamp and signature is required**
            </div>
        </div>
    </div>
    <div class="flex items-center justify-center py-1 ">
        <input type="button" onclick="test()" value="download" class="no-print" style="background-color: #4F46E5; color: #fff; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;"></input>
    </div>
</body>
    <script>
        function test(){
            window.print();
        }
    </script>
</html>
