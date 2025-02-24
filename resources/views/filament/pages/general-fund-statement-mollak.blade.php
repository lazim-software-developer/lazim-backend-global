<x-filament-panels::page>
    <div class="mb-4 flex space-x-2">
        <select id="building-dropdown" name="building" searchable onChange="loadGeneralFund()" class="bg-white border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mr-2">
            <option value="">Select a Building</option>
            @foreach($buildings as $building)
                <option value="{{ $building->id }}">{{ $building->name }}</option>
            @endforeach
        </select>

        <input type="date" id="date-dropdown" name="date" onchange="loadGeneralFund()" class="bg-white border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"> 
    </div>
    <div id="table-full">
        <h3>{{$message}}</h3>
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
                    <td class="text-right">N/A</td>
                </tr>
                <tr style="border-top: 2px solid;">
                    <td class="font-semibold text-gray-900">Total Income:</td>
                    <td class="text-right font-semibold text-gray-900">N/A</td>
                </tr>
                <tr>
                    <td colspan="2" style="height: 50px;"></td>
                </tr>
                <!-- Expense Section -->
                <tr>
                    <th class="pt-4 font-semibold text-gray-900" style="background-color: #f2f2f2;">EXPENSE</th>
                    <th  style="background-color: #f2f2f2; "></th>
                </tr>
                <tr style="border-top: 2px solid;">
                    <td class="font-semibold text-gray-900">Total Expense:</td>
                    <td class="text-right font-semibold text-gray-900">N/A</td>
                </tr>
                <tr>
                    <td colspan="2" style="height: 50px;"></td>
                </tr>
                <tr style="border-top: 2px solid;">
                    <td class="font-semibold text-gray-900">Surplus/(Deficit)</td>
                    <td class="text-right font-semibold text-gray-900">N/A</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
            function loadGeneralFund() {
                    const dateDropdown = document.getElementById('date-dropdown');
                    const buildingDropdown = document.getElementById('building-dropdown');
                    const selectedDate = dateDropdown.value;
                    const selectedBuilding = buildingDropdown.value;

                    // Construct the data payload based on what is selected
                    let dataPayload = {};
                    if (selectedDate !== "") dataPayload.date = selectedDate;
                    if (selectedBuilding !== "") dataPayload.building_id = selectedBuilding;

                    axios.post(`/get-general-fund-mollak`, dataPayload)
                        .then(response => {
                            document.getElementById('table-full').innerHTML = response.data;
                        })
                        .catch(error => {
                            console.error('Error fetching data:', error);
                        });
                }
        </script>

</x-filament-panels::page>
