<div id="table-full">
        <table class="text-left w-full">
            <!-- Table Body -->
            <tbody class="bg-white">
                <!-- Income Section -->
                <tr>
                    <th class="py-3 text-sm font-semibold text-gray-900 w-full"  style="background-color: #f2f2f2; ">INCOME</th>
                    <th  style="background-color: #f2f2f2; "></th>
                </tr>
                <tr>
                    <td>General Fund</td>
                    <td class="text-right">{{$receipt}}</td>
                </tr>
                <tr style="border-top: 2px solid;">
                    <td class="font-semibold text-gray-900">Total Income:</td>
                    <td class="text-right font-semibold text-gray-900">{{$receipt}}</td>
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
                    <td>{{$expence?->contract->service->name}}</td>
                    <td class="text-right">{{number_format($expence?->payment,2)}}</td>
                </tr>
                @endforeach
                <tr style="border-top: 2px solid;">
                    <td class="font-semibold text-gray-900">Total Expense:</td>
                    <td class="text-right font-semibold text-gray-900">{{$totalExpense}}</td>
                </tr>
                <tr>
                    <td colspan="2" style="height: 50px;"></td>
                </tr>
                <tr style="border-top: 2px solid;">
                    <td class="font-semibold text-gray-900">Surplus/(Deficit)</td>
                    <td class="text-right font-semibold text-gray-900">{{ (float)str_replace(',', '', $receipt) - (float)str_replace(',', '', $totalExpense) }}</td>
                </tr>
            </tbody>
        </table>
    </div>