<x-filament-panels::page >

    <div>
        <div class="inline-block py-2 align-middle w-full">
            <div class=""> <!-- Enables horizontal scrolling -->
                <!-- Filters -->
                    <div class="mb-4">
                        <select id="year-dropdown" name="year" class="mr-2" onChange="loadDelinquent()">
                            @foreach($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>

                        <select id="building-dropdown" name="building" searchable onChange="loadDelinquent()" class="mr-2">
                            <option value="">Select a Building</option>
                            @foreach($buildings as $building)
                                <option value="{{ $building->id }}">{{ $building->name }}</option>
                            @endforeach
                        </select>
                    </div>
            <div id="table-full">
                <table class="text-left " style="width: 100%;">
                    <thead class="bg-white ">
                        <tr>
                            <th style="padding: 8px; padding-left: 30px; min-width: 100px;" scope="col" class="relative isolate py-3.5 pr-3 text-left text-sm font-semibold text-gray-900">
                                Unit
                                <div class="absolute inset-y-0 right-full -z-10 w-screen border-b border-b-gray-200"></div>
                                <div class="absolute inset-y-0 left-0 -z-10 w-screen border-b border-b-gray-200"></div>
                            </th>
                            <th style="padding: 8px; min-width: 100px;" scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">Owner</th>
                            <th style="padding: 8px; min-width: 100px;" scope="col" colspan="2" class="hidden px-3 py-3.5 text-center text-sm font-semibold text-gray-900 md:table-cell">Last Payment</th>
                            <th style="padding: 8px; min-width: 100px;" scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Outstanding Balance</th>
                            <th style="padding: 8px; min-width: 100px;" scope="col" colspan="4" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Quarters</th>
                            <th style="padding: 8px; min-width: 100px;" scope="col" colspan="4" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Invoice file</th>

                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th class="relative isolate py-3.5 px-1 text-left text-sm font-semibold text-gray-900">Date</th>
                            <th class="relative isolate py-3.5 px-2 text-left text-sm font-semibold text-gray-900">Amount</th>
                            <th></th>
                            <th class="relative isolate py-3.5 px-3 text-left text-sm font-semibold text-gray-900">Q1</th>
                            <th class="relative isolate py-3.5 px-3 text-left text-sm font-semibold text-gray-900">Q2</th>
                            <th class="relative isolate py-3.5 px-3 text-left text-sm font-semibold text-gray-900">Q3</th>
                            <th class="relative isolate py-3.5 px-3 text-left text-sm font-semibold text-gray-900">Q4</th>
                            <th class="relative isolate py-3.5 px-3 text-left text-sm font-semibold text-gray-900"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $flat)
                        <tr>
                            <td class="relative py-4 px-3 px-4 text-sm font-medium text-gray-900">
                                {{$flat['property_number']}}
                                <div class="absolute bottom-0 right-full h-px w-screen bg-gray-100"></div>
                                <div class="absolute bottom-0 left-0 h-px w-screen bg-gray-100"></div>
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-500 sm:table-cell">
                            {{ $flat['owner']['name'] ?? 'N/A' }}
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-500 sm:table-cell">
                                @if($flat['lastReceipt'])
                                {{ \Carbon\Carbon::parse($flat['lastReceipt']['receipt_date'])->format('d-m-Y') }}
                                @else
                                {{ 'N/A' }}
                                @endif
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-500 md:table-cell">
                                @if($flat['lastReceipt'])
                                AED {{$flat['lastReceipt']['receipt_amount'] }}
                                @else
                                N/A
                                @endif
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-500">
                                AED {{$flat['balance']}}
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-500">
                                @if($flat['Q1_receipts'])
                                AED {{$flat['Q1_receipts']}}
                                @else
                                0
                                @endif
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-500">
                                @if($flat['Q2_receipts'])
                                AED {{$flat['Q2_receipts']}}
                                @else
                                0
                                @endif
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-500">
                                @if($flat['Q3_receipts'])
                                AED {{$flat['Q3_receipts']}}
                                @else
                                0
                                @endif
                            </td>

                            <td class="px-3 py-4 text-sm text-gray-500">
                                @if($flat['Q4_receipts'])
                                AED {{$flat['Q4_receipts']}}
                                @else
                                0
                                @endif
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500">
                                @if($flat['invoice_file'])
                                <a href="#" class="text-primary">Link<span class="sr-only">, {{$flat['invoice_file']}}</span></a>
                                @else
                                NA
                                @endif
                            </td>
                            
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- Pagination Links -->
                <div class="mt-10 flex justify-center ">
                {{ $data->links() }}
                </div>
        </div>  
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
            function loadDelinquent() {
                    const yearDropdown = document.getElementById('year-dropdown');
                    const buildingDropdown = document.getElementById('building-dropdown');
                    const selectedYear = yearDropdown.value;
                    const selectedBuilding = buildingDropdown.value;

                    // Construct the data payload based on what is selected
                    let dataPayload = {};
                    if (selectedYear !== "") dataPayload.year = selectedYear;
                    if (selectedBuilding !== "") dataPayload.building_id = selectedBuilding;

                    axios.post(`/get-delinquent-owners`, dataPayload)
                        .then(response => {
                            document.getElementById('table-full').innerHTML = response.data;
                        })
                        .catch(error => {
                            console.error('Error fetching data:', error);
                        });
                }
        </script>

</x-filament-panels::page>