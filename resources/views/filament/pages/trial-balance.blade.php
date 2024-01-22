<x-filament-panels::page>
<div class="mb-4 flex space-x-2">
        <select id="building-dropdown" name="building" searchable onChange="loadTrialBalance()" class="bg-white border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mr-2">
            <option value="">Select a Building</option>
            @foreach($buildings as $building)
                <option value="{{ $building->id }}">{{ $building->name }}</option>
            @endforeach
        </select>

        <input type="date" id="date-dropdown" name="date" onchange="loadTrialBalance()" class="bg-white border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"> 
    </div>
    <div id="table-full">
        <h3>{{$message}}</h3>
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
                    <td class="text-right">----</td>
                </tr>
                <tr>
                    <td>S.1.2-Reserved Fund</td>
                    <td class="text-right">----</td>
                </tr>
                <tr>
                    <td class="py-3 "> </td>
                </tr>
                <tr style="border-bottom: 2px solid;">
                    <th class="py-1 text-sm font-bold text-gray-900 w-full">EXPENSE</th>
                </tr>
                <tr>
                    <td>-----</td>
                    <td class="text-right">----</td>
                </tr>
                <tr>
                    <td class="py-3 "> </td>
                </tr>
                <tr style="border-bottom: 2px solid;">
                    <th class="py-1 text-sm font-bold text-gray-900 w-full">ASSET</th>
                </tr>
                <tr>
                    <td>-----</td>
                    <td class="text-right">----</td>
                </tr>
                <tr>
                    <td class="py-3 "> </td>
                </tr>
                <tr style="border-bottom: 2px solid;">
                    <th class="py-1 text-sm font-bold text-gray-900 w-full">LIABILITY</th>
                </tr>
                <tr>
                    <td>-----</td>
                    <td class="text-right">----</td>
                </tr>
                <tr>
                    <td class="py-3 "> </td>
                </tr>
                <tr style="border-bottom: 2px solid;">
                    <th class="py-1 text-sm font-bold text-gray-900 w-full">EQUITY</th>
                </tr>
                <tr>
                    <td>General Fund - Deficit/(Surplus)</td>
                    <td class="text-right">----</td>
                </tr>
                <tr>
                    <td>Reserved Fund - Deficit/(Surplus)</td>
                    <td class="text-right">----</td>
                </tr>
                
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
            function loadTrialBalance() {
                    const dateDropdown = document.getElementById('date-dropdown');
                    const buildingDropdown = document.getElementById('building-dropdown');
                    const selectedDate = dateDropdown.value;
                    const selectedBuilding = buildingDropdown.value;

                    // Construct the data payload based on what is selected
                    let dataPayload = {};
                    if (selectedDate !== "") dataPayload.date = selectedDate;
                    if (selectedBuilding !== "") dataPayload.building_id = selectedBuilding;

                    axios.post(`/get-trial-balance`, dataPayload)
                        .then(response => {
                            document.getElementById('table-full').innerHTML = response.data;
                        })
                        .catch(error => {
                            console.error('Error fetching data:', error);
                        });
                }
        </script>
</x-filament-panels::page>
