<x-filament-panels::page>
    <div>
        <div class="inline-block min-w-full py-2 align-middle">
            <div class="overflow-x-auto"> <!-- Enables horizontal scrolling -->
                <table class="min-w-max w-full text-left">
                    <thead class="bg-white">
                        <tr>
                            <th style="padding: 8px; min-width: 50px;" scope="col" class="relative isolate py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">
                                Unit
                                <div class="absolute inset-y-0 right-full -z-10 w-screen border-b border-b-gray-200"></div>
                                <div class="absolute inset-y-0 left-0 -z-10 w-screen border-b border-b-gray-200"></div>
                            </th>
                            <th style="padding: 8px; min-width: 50px;" scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">Owner</th>
                            <th style="padding: 8px; min-width: 50px;" scope="col" colspan="2" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 md:table-cell">Last Payment</th>
                            <th style="padding: 8px; min-width: 50px;" scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Outstanding Balance</th>
                            <th style="padding: 8px; min-width: 50px;" scope="col" colspan="4" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Quarters</th>
                            <th style="padding: 8px; min-width: 50px;" scope="col" colspan="4" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Invoice file</th>

                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th class="relative isolate py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Date</th>
                            <th class="relative isolate py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Amount</th>
                            <th></th>
                            <th class="relative isolate py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Q1</th>
                            <th class="relative isolate py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Q2</th>
                            <th class="relative isolate py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Q3</th>
                            <th class="relative isolate py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Q4</th>
                            <th class="relative isolate py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Q1</th>
                            <th class="relative isolate py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Q2</th>
                            <th class="relative isolate py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Q3</th>
                            <th class="relative isolate py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">Q4</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $category)
                        <tr>
                            <td class="relative py-4 pr-3 text-sm font-medium text-gray-900">
                                {{$category['flat']['property_number']}}
                                <div class="absolute bottom-0 right-full h-px w-screen bg-gray-100"></div>
                                <div class="absolute bottom-0 left-0 h-px w-screen bg-gray-100"></div>
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500 sm:table-cell">
                                {{$category['owner']['name']}}
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500 sm:table-cell">
                                @if($category['lastReceipt'])
                                {{ \Carbon\Carbon::parse($category['lastReceipt']['receipt_date'])->format('d-m-Y') }}
                                @else
                                {{ 'N/A' }}
                                @endif
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500 md:table-cell">
                                @if($category['lastReceipt'])
                                AED {{$category['lastReceipt']['receipt_amount'] }}
                                @else
                                NA
                                @endif
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500">
                                AED {{$category['invoice_amount']}}
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500">
                                @if($category['Q1_invoices'])
                                AED {{$category['Q1_invoices']['invoice_amount']}}
                                @else
                                NA
                                @endif
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-500">
                                @if($category['Q2_invoices'])
                                AED {{$category['Q2_invoices']['invoice_amount']}}
                                @else
                                NA
                                @endif
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-500">
                                @if($category['Q3_invoices'])
                                AED {{$category['Q3_invoices']['invoice_amount']}}
                                @else
                                NA
                                @endif
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-500">
                                @if($category['Q4_invoices'])
                                AED {{$category['Q4_invoices']['invoice_amount']}}
                                @else
                                NA
                                @endif
                            </td>

                            <!-- invocies Links -->
                            <td class="px-3 py-4 text-sm text-gray-500">
                                @if($category['Q1_invoices'])
                                <a href="#" class="text-primary">Link<span class="sr-only">, {{$category['Q1_invoices']['invoice_pdf_link']}}</span></a>
                                @else
                                NA
                                @endif
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-500">
                                @if($category['Q2_invoices'])
                                <a href="#" class="text-primary hover:text-indigo-900">Link<span class="sr-only">, {{$category['Q2_invoices']['invoice_pdf_link']}}</span></a>
                                @else
                                NA
                                @endif
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-500">
                                @if($category['Q3_invoices'])
                                <a href="#" class="text-indigo-600 hover:text-indigo-900">Link<span class="sr-only">, {{$category['Q3_invoices']['invoice_pdf_link']}}</span></a>
                                @else
                                NA
                                @endif
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-500">
                                @if($category['Q4_invoices'])
                                <a href="#" class="text-indigo-600 hover:text-indigo-900">Link<span class="sr-only">, {{$category['Q4_invoices']['invoice_pdf_link']}}</span></a>
                                @else
                                NA
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="mt-10">
                {{ $data->links() }}
            </div>
        </div>
    </div>

</x-filament-panels::page>