{{-- <a class="openModalBtn" data-title="Create Building Test" href="{{ route('building.ajax-create') }}">Create
        Building
    </a> --}}


@php
    $cssClassHeader =
        'fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-contract-type';
    $cssClassRow = 'fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6';
    $tr_row = 'bg-gray-50 dark:bg-white/5';
    $tableClass = 'fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5';
    $theadClass = 'divide-y divide-gray-200 dark:divide-white/5';
@endphp

<div class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
    <table class="{{ $tableClass }}">
        <thead class="{{ $theadClass }}">
            <tr class="{{ $tr_row }}">
                <th class="{{ $cssClassHeader }}">
                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                            ID
                        </span>
                    </span>
                </th>
                <th class="{{ $cssClassHeader }}">
                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                            Customer Name
                        </span>
                    </span>
                </th>
                <th class="{{ $cssClassHeader }}">
                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                            Issue Date
                        </span>
                    </span>
                </th>
                <th class="{{ $cssClassHeader }}">
                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                            Due Date
                        </span>
                    </span>
                </th>
                <th class="{{ $cssClassHeader }}">
                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                            Send Date
                        </span>
                    </span>
                </th>
                <th class="{{ $cssClassHeader }}">
                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                            Ref Number
                        </span>
                    </span>
                </th>
                <th class="{{ $cssClassHeader }}">
                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                            Invoice Period
                        </span>
                    </span>
                </th>
                <th class="{{ $cssClassHeader }}">
                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                            Status Updated By
                        </span>
                    </span>
                </th>

            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
            @foreach ($paginationResponse['items'] as $invoice)
                <tr>
                    <td class="{{ $cssClassRow }}">
                        <div class="fi-ta-col-wrp">
                            <div class="fi-ta-text grid w-full gap-y-1 px-3 py-2">
                                <div class="flex">
                                    <div class="flex max-w-max">
                                        <div class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                            <span
                                                class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  ">
                                                {{ $invoice['invoice_id'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="{{ $cssClassRow }}">
                        <div class="fi-ta-col-wrp">
                            <div class="fi-ta-text grid w-full gap-y-1 px-3 py-2">
                                <div class="flex">
                                    <div class="flex max-w-max">
                                        <div class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                            <span
                                                class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  ">
                                                {{ $invoice['customer']['name'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="{{ $cssClassRow }}">
                        <div class="fi-ta-col-wrp">
                            <div class="fi-ta-text grid w-full gap-y-1 px-3 py-2">
                                <div class="flex">
                                    <div class="flex max-w-max">
                                        <div class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                            <span
                                                class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  ">
                                                {{ $invoice['issue_date'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>


                    <td class="{{ $cssClassRow }}">
                        <div class="fi-ta-col-wrp">
                            <div class="fi-ta-text grid w-full gap-y-1 px-3 py-2">
                                <div class="flex">
                                    <div class="flex max-w-max">
                                        <div class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                            <span
                                                class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  ">
                                                {{ $invoice['due_date'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="{{ $cssClassRow }}">
                        <div class="fi-ta-col-wrp">
                            <div class="fi-ta-text grid w-full gap-y-1 px-3 py-2">
                                <div class="flex">
                                    <div class="flex max-w-max">
                                        <div class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                            <span
                                                class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  ">
                                                {{ $invoice['send_date'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="{{ $cssClassRow }}">
                        <div class="fi-ta-col-wrp">
                            <div class="fi-ta-text grid w-full gap-y-1 px-3 py-2">
                                <div class="flex">
                                    <div class="flex max-w-max">
                                        <div class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                            <span
                                                class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  ">
                                                {{ $invoice['ref_number'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="{{ $cssClassRow }}">
                        <div class="fi-ta-col-wrp">
                            <div class="fi-ta-text grid w-full gap-y-1 px-3 py-2">
                                <div class="flex">
                                    <div class="flex max-w-max">
                                        <div class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                            <span
                                                class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  ">
                                                {{ $invoice['discount_apply'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="{{ $cssClassRow }}">
                        <div class="fi-ta-col-wrp">
                            <div class="fi-ta-text grid w-full gap-y-1 px-3 py-2">
                                <div class="flex">
                                    <div class="flex max-w-max">
                                        <div class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                            <span
                                                class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  ">
                                                {{ $invoice['invoice_period'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination Links -->
    {{-- <div id="pagination" class="flex items-center justify-center mt-4">
            {{-- {{ $invoices->links() }} --}
    </div> --}}
    @include('filament.resources.common.pagination', ['paginationData' => $paginationResponse])

</div>
