<x-filament::page>
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
            integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link href="{{ asset('assets/custom-plugins/ajax-modal/ajax-modal.css') }}" rel="stylesheet">
        <!-- Custom CSS File Import -->
        <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
                        }
                    });
                }
            });
        </script>
        <script>
            document.getElementById("toggle-filter").addEventListener("click", function() {
                let filterContainer = document.getElementById("filter-container");
                filterContainer.classList.toggle("hidden");
            });
        </script>

        <script>
            $('#receipt-grid').ajaxGridPlugin({
                url: "{{ route('receipt.ajax-load-receipts') }}",
                extraParams: {
                    search: ""
                },
                searchFormId: '#search-form',
                autoTriggerSearchOnInit: true,
            });
        </script>
    @endpush
    <div
        class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">

        <div class="flex justify-end p-4">
            <!-- Filter Icon Button -->
            <button id="toggle-filter" class="btn btn-sm btn-secondary flex items-center gap-2">
                <i class="fas fa-filter h-5 w-5 filter-gray"></i>
            </button>

            <!-- Filter Form (Initially Hidden) -->

        </div>

        <div class="flex gap-2 hidden p-4" id="filter-container">

            <form id="search-form" class="flex gap-4 w-full items-center">
                <div class="flex flex-col w-1/4">
                    <label for="from_date" class="text1">From Date</label>
                    <input type="date" id="from_date" name="from_date" class="form-control" value="2024-01-01">
                </div>


                <div class="flex flex-col w-1/4">
                    <label for="to_date" class="text1">To Date</label>
                    <input type="date" id="to_date" name="to_date" class="form-control" value="2024-12-31">
                </div>


                <div class="flex flex-col w-1/4">
                    <label for="customer" class="text1">Customer Name</label>
                    <input type="text" id="customer" name="customer" class="form-control">
                </div>


                <div class="flex flex-col w-1/4">
                    <button data-button-search="true" class="custom-buttons" id="filter-btn">Filter</button>
                </div>
            </form>
        </div>
        <div id="receipt-grid">
        </div>
    </div>
</x-filament::page>
