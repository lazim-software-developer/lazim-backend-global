<x-filament::page>
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
            integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link href="{{ asset('assets/custom-plugins/ajax-modal/ajax-modal.css') }}" rel="stylesheet">
        <!-- Custom CSS File Import -->
        <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">
    @endpush
    @push('scripts')
        <!-- Include Your Plugin Script -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/@tabler/icons@1.74.0/iconfont/tabler-icons.min.css" rel="stylesheet">

        <script src="{{ asset('assets/custom-plugins/utilities/utilities.js') }}"></script>
        <script src="{{ asset('assets/custom-plugins/ajax-grid/ajax-grid.js') }}"></script>
        <script src="{{ asset('assets/custom-plugins/toast/toast.js') }}"></script>
        <script src="{{ asset('assets/custom-plugins/ajax-modal/ajax-modal.js') }}"></script>
        <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



        <script>
            document.addEventListener("click", function(event) {
                if (event.target.closest(".delete-btn")) {
                    event.preventDefault();

                    Swal.fire({
                        title: "Are you sure?",

                        text: "This action cannot be undone!",
                        icon: "warning",

                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire(
                                "Deleted!",
                                "Your item has been deleted.",
                                "success"
                            );
                            console.log("Item deleted!");
                            // Here, you can call your delete function or AJAX request
                        }
                    });
                }
            });
        </script>


        <script>
            $('#invoice-grid').ajaxGridPlugin({
                url: "{{ route('invoice.ajax-load-invoices') }}", // Your URL
                extraParams: {
                    search: ""
                }, // Initial extra params
                // showSearchField: false, // Whether to show search field
                searchFormId: '#search-form', // Form ID for search
                autoTriggerSearchOnInit: true, // This will auto-trigger the search click when the grid initializes
                // searchInputSelector: '#search-invoice' // The search input selector
            });
        </script>
    @endpush
    {{-- <div class="button-container">
        <a class="custom-button" data-modal-link="true" data-title="Create Building"
            href="{{ route('building.ajax-create') }}">Create
            Building
        </a>
    </div> --}}
    <div
        class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">

        <div class="flex justify-end p-4">
            <div class="flex gap-2">
                <form id="search-form">
                    <input type="date" name="from_date" class="form-control" value="2024-01-01">
                    <input type="date" name="to_date" class="form-control" value="2024-12-31">
                    <input type="text" name="customer" class="form-control">
                    <button data-button-search="true" class="btn btn-sm btn-primary" id="filter-btn">Filter</button>
                </form>
            </div>
            {{-- <a class="btn btn-sm btn-success custom-button" id="export-table" data-bs-toggle="tooltip" title="Export Data">
            <i class="ti ti-download"></i> Export
        </a> --}}
        </div>
        <div id="invoice-grid">
        </div>
    </div>

    {{-- @include('filament.resources.create-building') --}}

</x-filament::page>
