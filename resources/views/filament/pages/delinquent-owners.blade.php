<x-filament-panels::page>
    <x-filament::card>
        <div class="inline-block min-w-full py-2 align-middle">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f3f3f3;">
                        <th style="border: 1px solid #ddd; padding: 8px;">Unit</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Owner</th>
                        <th colspan="2" style="border: 1px solid #ddd; padding: 8px;">Last Payment</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Outstanding Balance</th>
                        <th colspan="4" style="border: 1px solid #ddd; padding: 8px;">Quarters</th>
                        <th colspan="4" style="border: 1px solid #ddd; padding: 8px;">Invoice file</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $category)
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{$category['flat']['property_number']}}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{$category['owner']['name']}}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            @if($category['lastReceipt']['receipt_date'])
                            {{ \Carbon\Carbon::parse($category['lastReceipt']['receipt_date'])->format('d-m-Y') }}
                            @else
                            {{ 'N/A' }}
                            @endif
                        </td>
                        <td style="border: 1px solid #ddd; padding: 8px;">AED {{$category['lastReceipt']['receipt_amount']}}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">AED {{$category['invoice_amount']}}</td>
                        @if($category['Q1_invoices'])
                        <td style="border: 1px solid #ddd; padding: 8px;">AED {{$category['Q1_invoices']['invoice_amount']}}</td>
                        @else
                        <td style="border: 1px solid #ddd; padding: 8px;">NA</td>
                        @endif
                        @if($category['Q2_invoices'])
                        <td style="border: 1px solid #ddd; padding: 8px;">AED {{$category['Q2_invoices']['invoice_amount']}}</td>
                        @else
                        <td style="border: 1px solid #ddd; padding: 8px;">NA</td>
                        @endif
                        @if($category['Q3_invoices'])
                        <td style="border: 1px solid #ddd; padding: 8px;">AED {{$category['Q3_invoices']['invoice_amount']}}</td>
                        @else
                        <td style="border: 1px solid #ddd; padding: 8px;">NA</td>
                        @endif
                        @if($category['Q4_invoices'])
                        <td style="border: 1px solid #ddd; padding: 8px;">AED {{$category['Q4_invoices']['invoice_amount']}}</td>
                        @else
                        <td style="border: 1px solid #ddd; padding: 8px;">NA</td>
                        @endif
                        @if($category['Q1_invoices'])
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            <a href="{{$category['Q1_invoices']['invoice_pdf_link']}}">PDF link</a>
                        </td>
                        @else
                        <td style="border: 1px solid #ddd; padding: 8px;">NA</td>
                        @endif
                        @if($category['Q2_invoices'])
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            <a href="{{$category['Q2_invoices']['invoice_pdf_link']}}">PDF link</a>
                        </td>
                        @else
                        <td style="border: 1px solid #ddd; padding: 8px;">NA</td>
                        @endif
                        @if($category['Q3_invoices'])
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            <a href="{{$category['Q3_invoices']['invoice_pdf_link']}}">PDF link</a>
                        </td>
                        @else
                        <td style="border: 1px solid #ddd; padding: 8px;">NA</td>
                        @endif
                        @if($category['Q4_invoices'])
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            <a href="{{$category['Q4_invoices']['invoice_pdf_link']}}">PDF link</a>
                        </td>
                        @else
                        <td style="border: 1px solid #ddd; padding: 8px;">NA</td>
                        @endif
                    </tr>
                    @endforeach
                    <!-- Repeat the above <tr> block for each row of data -->
                </tbody>
            </table>

            <!-- Pagination Links -->
            <div class="mt-4">
                {{ $data->links() }}
            </div>
        </div>
    </x-filament::card>

</x-filament-panels::page>