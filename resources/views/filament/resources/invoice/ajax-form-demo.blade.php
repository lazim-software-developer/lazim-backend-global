<x-filament::page>
    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="{{ asset('assets/custom-plugins/ajax-modal/ajax-modal.css') }}" rel="stylesheet">
    @endpush
    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons@1.74.0/iconfont/tabler-icons.min.css" rel="stylesheet">

    {{-- <script src="{{ asset('assets/custom-plugins/utilities/utilities.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/custom-plugins/ajax-grid/ajax-grid.js') }}"></script> --}}
    <script src="{{ asset('assets/custom-plugins/toast/toast.js') }}"></script>
    <script src="{{ asset('assets/custom-plugins/ajax-modal/ajax-modal.js') }}"></script>
    <script>
        $(document).ready(function() {

            $(".openModalBtn").on("click", function(event) {
                event.preventDefault();
                var url = $(this).attr("href");
                var title = $(this).data("title");
                $(this).ajaxModal({
                    url: url,
                    title: title,
                    size: "xl",
                    success: function(response) {
                        Toast.info("Form loaded successfully");
                    },
                    error: function(xhr, status, error) {
                        Toast.error("Error loading form:");
                        console.log("Error loading form:", error);
                    },
                    onClose: function() {
                        console.log("Modal closed");
                    },
                    afterSubmit: function(response) {
                        console.log(response);
                        Toast.success(response.message);
                        console.log("Form submitted successfully");
                    },
                });
                return;
            });

        });
    </script>
    @endpush
    <button onclick="Toast.show('Info message displayed!', 'info')">Show Info</button>
    <button onclick="Toast.show('Success! Operation completed.', 'success')">Show Success</button>
    <button onclick="Toast.show('Warning: Check your inputs.', 'warning')">Show Warning</button>
    <button onclick="Toast.show('Error: Something went wrong.', 'error')">Show Error</button>
    {{-- @include('filament.resources.invoice.partials.invoice-grid') --}}
    {{-- @include('filament.resources.create-building') --}}
    <a class="openModalBtn" data-title="Create Building Test" href="{{ route('building.ajax-create') }}">Create
        Building
    </a>


</x-filament::page>