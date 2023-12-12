<x-filament-panels::page>
    <div class="space-y-10 divide-y divide-gray-900/10">
        <form id="form" class="bg-white shadow-sm ring-2 ring-offset-slate-700 sm:rounded-xl" method="post"
            enctype="multipart/form-data" action="/admin/{{$budgetId}}/tender/create">
            @csrf
            <div class="px-4 py-6 sm:p-8">
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 md:grid-cols-4">
                    <!-- Date Field -->
                    <div class="md:col-span-1 max-w-md">
                        <label for="end-date" class="block text-sm font-medium leading-6 text-gray-900">End date</label>
                        <div class="mt-2">
                            <input type="date" name="end_date" id="end-date"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder="Enter end date to submit proposal" required>
                        </div>
                    </div>

                    <div class="md:col-span-1 max-w-md">
                        <label for="tender-type" class="block text-sm font-medium leading-6 text-gray-900">Tender
                            Type</label>
                        <div class="mt-2">
                            <select name="tender_type" id="tender-type"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                required>
                                <option value="" selected disabled>Select Tender Type</option>
                                <option value="One time">One time</option>
                                <option value="AMC">AMC</option>
                            </select>
                        </div>
                    </div>


                    <div class="md:col-span-1 max-w-md my-5">
                        <label for="end-date" class="block text-sm font-medium leading-6 text-gray-900">Tender
                            document</label>
                        <div class="mt-2">
                            <input id="file-upload" name="document" type="file"
                                class="mt-2 block w-full text-sm text-gray-900 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100"
                                accept=".png, .jpg, .jpeg, .pdf" required>
                        </div>
                    </div>


                    <div class="md:col-span-1 max-w-md">
                        <select id="subcategory-dropdown" name="subcategory" class="w-full" required>
                            <option value="" selected disabled>Select Subcategory</option>
                            @foreach ($subcategories as $subcategory)
                            <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-1 max-w-md">
                        <select id="services-dropdown" name="service" disabled onChange="loadVendors()" class="w-full"
                            required>
                            <option value="" selected disabled>Select Service</option>
                        </select>
                    </div>

                    {{-- Vendors List --}}
                    <div id="vendors-list" class="mt-6"></div>


                    <!-- Form Submission Buttons -->
                    <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                        <button type="submit"
                            class="rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
                    </div>
                </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        // Get the current date in the format "YYYY-MM-DD"
        var currentDate = new Date().toISOString().split("T")[0];
        // Set the min attribute of the date input field
        document.getElementById("end-date").min = currentDate;
        // Document validation
        document.getElementById('form').addEventListener('submit', function (event) {
            var inputFile = document.getElementById('file-upload');
            var allowedTypes = [".png", ".jpg", ".jpeg", ".pdf"];

            if (!checkFileType(inputFile.value, allowedTypes)) {
                alert('Invalid file type. Please upload a valid file.');
                event.preventDefault(); // Prevent form submission
            }
        });
        function checkFileType(filename, allowedTypes) {
            var ext = filename.substring(filename.lastIndexOf('.')).toLowerCase();
            return allowedTypes.indexOf(ext) !== -1;
        }
        // Checking vendors for Service
        function loadVendors() {
            const servicesDropdown = document.getElementById('services-dropdown');
            const selectedServiceId = servicesDropdown.value;

            if (selectedServiceId) {
                console.log("Selected Service ID:", selectedServiceId);

                axios.post(`/get-vendors-based-on-services`, {
                    service_id: selectedServiceId
                })
                    .then(response => {
                        document.getElementById('vendors-list').innerHTML = response.data;
                    })
                    .catch(error => {
                        console.error('Error fetching vendors:', error);
                    });
            } else {

                console.log("No service selected");
                // You might want to clear or reset the vendors list here
                document.getElementById('vendors-list').innerHTML = '';
            }
        }

        // Populate services for a sub category
        document.getElementById('subcategory-dropdown').addEventListener('change', function () {
            var subcategoryId = this.value;
            var budgetId = @json($budgetId);

            var servicesDropdown = document.getElementById('services-dropdown');

            axios.get('/budget/' + budgetId + '/available-services/' + subcategoryId)
                .then(function (response) {
                    // Clear current options in services dropdown
                    servicesDropdown.innerHTML = '<option value="">Select Service</option>';
                    servicesDropdown.disabled = false;

                    // Populate new options from response
                    response.data.forEach(function (service) { // Assuming response.data is an array
                        var option = new Option(service.name, service.id);
                        servicesDropdown.appendChild(option);
                    });
                })
                .catch(function (error) {
                    console.error('Error fetching services:', error);
                });
        });
    </script>
</x-filament-panels::page>