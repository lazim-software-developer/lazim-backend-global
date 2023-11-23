<x-filament-panels::page>
    <div class="lg:border-b lg:border-t lg:border-gray-200">
        <form method="post" enctype="multipart/form-data" action="/admin/1/tender/create">
            @csrf
            <div class="space-y-12">
                {{$errors}}
                <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-b border-gray-900/10 pb-12 md:grid-cols-3 mt-8">
                    <div>
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Tender details</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">This information will be displayed publicly so be careful what you share.</p>
                    </div>

                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <!-- <div class="sm:col-span-4">
                            <label for="website" class="block text-sm font-medium leading-6 text-gray-900">End date</label>
                            <div class="mt-2">
                                <livewire:datepicker />

                                @error('end_date')
                                <p class="text-sm">{{ $message }}</p>
                                @enderror
                            </div>
                        </div> -->

                        <div class="relative">
                            <div class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                <input type="date" id="datepicker" class="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6" name="end_date">
                            </div>
                            @error('end_date')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>


                        <fieldset>
                            @foreach($subcategoryServices as $category)
                            <legend>{{$category['subcategory_name']}}</legend>
                            @foreach($category['services'] as $data)
                            <div>
                                <div class="relative flex items-start py-4">
                                    <div class="flex h-6 items-center">
                                        <div class="flex h-6 items-center">
                                            <input id="{{ $data['id'] }}" aria-describedby="candidates-description" name="services[]" value="{{ $data['id']}}" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" onChange="loadVendors()">

                                        </div>

                                        <div class="px-2 text-sm leading-6 ">
                                            <label for={{$data['id']}} class="font-medium text-gray-900">{{$data['name']}}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @endforeach

                            @error('services')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        {{-- Vendors List --}}
                        <div id="vendors-list" class="mt-6">
                            {{-- Vendors will be inserted here by JavaScript after form submission --}}
                        </div>

                        <input id="file-upload" name="document" type="file" class="mt-2 block w-full text-sm text-gray-900 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">

                        @error('document')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <div class="mt-6">
                            <button type="submit" class="px-4 py-2 bg-blue text-black font-bold rounded hover:bg-blue-600">
                                Submit Tender
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        function loadVendors() {
            const selectedServices = Array.from(document.querySelectorAll('input[name="candidates[]"]:checked')).map(checkbox => checkbox.id);
            axios.post(`/get-vendors-based-on-services`, {
                    services: selectedServices
                })
                .then(response => {
                    document.getElementById('vendors-list').innerHTML = response.data;
                })
                .catch(error => {
                    console.error('Error fetching vendors:', error);
                });
        }

        function validateForm() {
            let isValid = true;
            const fileInput = document.getElementById('file-upload');
            const checkboxes = document.querySelectorAll('input[name="candidates[]"]:checked');

            if (!fileInput.files.length) {
                alert('Please upload a file.');
                isValid = false;
            }

            if (!checkboxes.length) {
                alert('Please select at least one service.');
                isValid = false;
            }

            return isValid;
        }
    </script>
</x-filament-panels::page>