<x-filament-panels::page>

    @section('content')
    <div class="container mx-auto p-4">
        {{-- Services Selection Form --}}
        <form id="services-form" action="{{ route('vendors.based.on.services') }}" method="post">
            @csrf
            <div class="mb-4">
                <label for="services" class="block text-sm font-medium text-gray-700">Select Services</label>
                <div class="mt-2">
                    @foreach($services as $service)
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" class="form-checkbox" name="services[]" value="{{ $service->id }}">
                            <span class="ml-2">{{ $service->name }}</span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-red rounded hover:bg-blue-700">Show Vendors</button>
        </form>

        {{-- Vendors List --}}
        <div id="vendors-list" class="mt-6">
            {{-- Vendors will be inserted here by JavaScript after form submission --}}
        </div>

        <form wire:submit.prevent="submit">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-red rounded hover:bg-blue-700">Submit</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        document.getElementById('services-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            axios.post(this.action, formData)
                .then(response => {
                    // Assuming the response contains HTML to insert
                    document.getElementById('vendors-list').innerHTML = response.data;
                })
                .catch(error => {
                    console.error('Error fetching vendors:', error);
                });
        });
    </script>

</x-filament-panels::page>