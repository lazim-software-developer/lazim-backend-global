<div id="table-full">
        <table class="text-left w-full">
            <!-- Table Body -->
            <tbody class="bg-white">
                <!-- Income Section -->
                <tr>
                    <th class="py-3 text-sm font-semibold text-gray-900 w-full"  style="background-color: #f2f2f2; ">INCOME</th>
                    <th  style="background-color: #f2f2f2; "></th>
                </tr>
                @foreach($generals as $general)
                <tr>
                    <td>{{$general?->description}}</td>
                    <td class="text-right">{{number_format($general?->credited_amount,2)}}</td>
                </tr>
                @endforeach
                <tr style="border-top: 2px solid;">
                    <td class="font-semibold text-gray-900">Total Income:</td>
                    <td class="text-right font-semibold text-gray-900">{{$generals->sum('credited_amount')}}</td>
                </tr>
                <tr>
                    <td colspan="2" style="height: 50px;"></td>
                </tr>
                <!-- Expense Section -->
                <tr>
                    <th class="pt-4 font-semibold text-gray-900" style="background-color: #f2f2f2;">EXPENSE</th>
                    <th  style="background-color: #f2f2f2; "></th>
                </tr>
                @foreach($expenses as $expence)
                <tr>
                <td>{{$expence?->description}}</td>
                    <td class="text-right">{{number_format($expence?->debited_amount,2)}}</td>
                </tr>
                @endforeach
                <tr style="border-top: 2px solid;">
                    <td class="font-semibold text-gray-900">Total Expense:</td>
                    <td class="text-right font-semibold text-gray-900">{{$expenses->sum('debited_amount')}}</td>
                </tr>
                <tr>
                    <td colspan="2" style="height: 50px;"></td>
                </tr>
                <tr style="border-top: 2px solid;">
                    <td class="font-semibold text-gray-900">Surplus/(Deficit)</td>
                    <td class="text-right font-semibold text-gray-900">{{$generals->sum('credited_amount') - $expenses->sum('debited_amount')}}</td>
                </tr>
            </tbody>
        </table>
    </div>