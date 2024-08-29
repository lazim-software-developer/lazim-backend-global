<div id="table-full">
        <table class="text-left w-full">
            <!-- Table Body -->
            <tbody class="bg-white">
                <!-- Income Section -->
                <tr>
                    <th class="py-3 text-sm font-bold text-gray-900 w-full"  style="background-color: #f2f2f2; ">Account</th>
                    <th  style="background-color: #f2f2f2; ">Total</th>
                </tr>
                <tr style="border-bottom: 2px solid;">
                    <th class="py-1 text-sm font-bold text-gray-900 w-full">INCOME</th>
                </tr>
                <tr>
                    <td>S.1.1-General Fund</td>
                    <td class="text-right">{{$generals?->sum('credited_amount')}}</td>
                </tr>
                <tr>
                    <td>S.1.2-Reserved Fund</td>
                    <td class="text-right">{{$reserves?->sum('credited_amount')}}</td>
                </tr>
                <tr>
                    <td class="py-3 "> </td>
                </tr>
                <tr style="border-bottom: 2px solid;">
                    <th class="py-1 text-sm font-bold text-gray-900 w-full">EXPENSE</th>
                </tr>
                @foreach($expenses as $expence)
                <tr>
                    <td>{{$expence?->description}}</td>
                    <td class="text-right">{{number_format($expence?->debited_amount,2)}}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="py-3 "> </td>
                </tr>
                <tr style="border-bottom: 2px solid;">
                    <th class="py-1 text-sm font-bold text-gray-900 w-full">ASSET</th>
                </tr>
                @foreach($assets as $asset)
                <tr>
                    <td>{{$asset?->building->name.'-'.$asset?->type}}</td>
                    <td class="text-right">{{number_format($asset?->invoice_amount,2)}}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="py-3 "> </td>
                </tr>
                <tr style="border-bottom: 2px solid;">
                    <th class="py-1 text-sm font-bold text-gray-900 w-full">LIABILITY</th>
                </tr>
                @foreach($invoices as $invoice)
                <tr>
                    <td>{{$invoice?->contract->service->name.'-Payable'}}</td>
                    <td class="text-right">{{number_format($invoice?->invoice_amount,2)}}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="py-3 "> </td>
                </tr>
                <tr style="border-bottom: 2px solid;">
                    <th class="py-1 text-sm font-bold text-gray-900 w-full">EQUITY</th>
                </tr>
                <tr>
                    <td>General Fund - Deficit/(Surplus)</td>
                    <td class="text-right">{{$generalSurplus}}</td>
                </tr>
                <tr>
                    <td>Reserved Fund - Deficit/(Surplus)</td>
                    <td class="text-right">{{$reserveSurplus}}</td>
                </tr>
                
            </tbody>
        </table>
    </div>