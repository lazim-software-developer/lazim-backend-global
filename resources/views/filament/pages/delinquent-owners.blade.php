<x-filament-panels::page>
    <div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold leading-6 text-gray-900">Users</h1>
                    <p class="mt-2 text-sm text-gray-700">A list of all the users in your account including their name, title, email and role.</p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <button type="button" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Add user</button>
                </div>
            </div>
        </div>
        <div class="mt-8 flow-root overflow-hidden">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <table class="w-full text-left">
                    <thead class="bg-white">
                        <tr>
                            <th scope="col" class="relative isolate py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">
                                Unit
                                <div class="absolute inset-y-0 right-full -z-10 w-screen border-b border-b-gray-200"></div>
                                <div class="absolute inset-y-0 left-0 -z-10 w-screen border-b border-b-gray-200"></div>
                            </th>
                            <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">Owner</th>
                            <th scope="col" colspan="2" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 md:table-cell">Last Payment</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Outstanding Balance</th>
                            <th scope="col" colspan="4" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Quarters</th>
                            <th scope="col" colspan="4" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Invoice file</th>

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
                                <a href="#" class="text-indigo-600 hover:text-indigo-900">Link<span class="sr-only">, {{$category['Q1_invoices']['invoice_pdf_link']}}</span></a>
                                @else
                                NA
                                @endif
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-500">
                                @if($category['Q2_invoices'])
                                <a href="#" class="text-indigo-600 hover:text-indigo-900">Link<span class="sr-only">, {{$category['Q2_invoices']['invoice_pdf_link']}}</span></a>
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
        </div>
    </div>

</x-filament-panels::page>