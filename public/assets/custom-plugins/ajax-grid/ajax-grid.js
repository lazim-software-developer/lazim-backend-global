(function ($) {
    $.fn.ajaxGrid = function (options) {
        const settings = $.extend(
            {
                url: "",
                // URL for the AJAX request
                method: "GET",
                // METHOD for the AJAX request
                container: "",
                // Container where data will be displayed
                page: 1,
                // Initial page
                searchField: "",
                // Search field selector (optional)
                loader: null,
                // If not passed, the loader will be dynamically created
                extraParams: {},
                // Extra query parameters for AJAX request
                paginationSelector: "#pagination-container",
                // Pagination links selector
                showSearchField: true,
                // Whether to create the search field dynamically,
                searchFieldPlaceholder: "Search...",
            },
            options
        );

        const $table = this;
        const $container = $(settings.container);

        // Dynamically create the loader if it's not provided
        let $loader;
        if (settings.loader) {
            $loader = $(settings.loader);
        } else {
            const loaderHTML = `
                <div class="loader-container" style="position: absolute; height: 100%; background: #85858533; width: 100%; top: 0; left: 0; z-index: 111;">
                    <div class="spinner-border" role="status" style="position: absolute; top: 50%; left: 50%; ">
                    <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                `;

            // Create a loader element if none was provided
            $loader = $(loaderHTML);
            $container.prepend($loader);
            // Add the loader to the container
        }

        // Dynamically create search field if not provided
        let $searchField;
        if (settings.searchField && settings.showSearchField) {
            $searchField = $(settings.searchField);
        } else if (settings.showSearchField) {
            // Create the search input dynamically if it's not provided
            $searchField = $(`
                <div style="margin-top: 12px; max-width: 300px; margin-bottom: 35px;">
                    <input type="text" class="search form-control" placeholder="${settings.searchFieldPlaceholder}">
                </div>
            `);

            $container.before($searchField);
            // Add it before the container (you can change the location)
        }

        // Function to load data and handle pagination
        function loadData() {
            $loader.show();
            // Show loader

            // Build the query parameters, including search term and extraParams
            // Get the search term if the search field is visible, otherwise set it to an empty string
            const searchTerm = settings.showSearchField
                ? $searchField.find("input").val()?.trim() || ""
                : "";

            // Construct params object: always include page and extraParams,
            // and add 'search' only if searchTerm is not empty
            const params = {
                page: settings.page,
                ...settings.extraParams,
                ...(searchTerm && {
                    search: searchTerm,
                }),
                // Only add search param if searchTerm is not empty
            };

            $.ajax({
                url: settings.url,
                type: settings.method,
                data: params,
                success: function (response) {
                    // Update the table data and pagination
                    $container.html(response);
                    // Assuming the server returns the full HTML with table and pagination
                    $loader.hide();
                    // Hide loader
                },
                error: function () {
                    $loader.hide();
                    // Hide loader in case of error
                    alert("Error loading data!");
                },
            });
        }

        // Handle search input
        if ($searchField) {
            let debounceTimeout;
            $searchField.on("keyup", function () {
                clearTimeout(debounceTimeout);
                // Clear the previous timeout
                debounceTimeout = setTimeout(function () {
                    settings.page = 1;
                    // Reset to page 1 when search changes
                    loadData();
                }, 300);
                // 300ms delay before calling loadData (adjust as needed)
            });
        }

        // Handle pagination click (specific to the container)
        // $(document).on("click", `${settings.container} ${settings.paginationSelector} a.page-link`, function(e) {
        $(document).on(
            "click",
            `${settings.container} ${settings.paginationSelector} a`,
            function (e) {
                e.preventDefault();
                const url = $(this).attr("href");
                // Get the URL from the pagination link
                const urlParams = new URLSearchParams(url.split("?")[1]);
                // Extract the query string from the URL
                settings.page = urlParams.get("page");
                // Update the page number from the query string
                loadData();
                // Load the new data
            }
        );

        // Handle initial load
        loadData();

        // Add the refresh method to the jQuery object
        $table.data("ajaxGrid", {
            refresh: function (newParams, clearExtraParams = true) {
                // Update extraParams with the new parameters provided
                settings.extraParams = {
                    ...(clearExtraParams ? {} : settings.extraParams),
                    // Clear or retain extraParams based on clearExtraParams
                    ...newParams,
                };

                // Reset page to 1 on refresh
                settings.page = 1;

                // Reload the data with updated parameters
                loadData();
            },
        });

        // Return the jQuery object for chaining
        return this;
    };
})(jQuery);

// ajaxGridPlugin
(function ($) {
    // Define the generic AjaxGrid class
    class AjaxGrid {
        constructor(gridId, options) {
            this.gridId = gridId;
            this.options = $.extend(
                {
                    url: "", // Default URL
                    searchField: "", // Optional search field
                    extraParams: {}, // Extra parameters to be sent with the request
                    showSearchField: false, // Whether to show the search field
                    searchFormId: "", // Form ID for search
                    searchInputSelector: "#search-input", // Default search input selector
                    autoTriggerSearchOnInit: false, // Option to control auto-trigger of search button
                },
                options
            );

            this.$tableContainer = $(this.gridId).ajaxGrid({
                url: this.options.url,
                container: this.gridId,
                extraParams: this.options.extraParams,
                showSearchField: this.options.showSearchField,
            });

            this.init();
        }

        // Initialize the plugin
        init() {
            this.bindSearchEvent(this.options.searchInputSelector); // Bind search event to the specific input
            this.bindButtonSearchEvent();

            // Automatically trigger search if option is enabled
            if (
                this.options.autoTriggerSearchOnInit &&
                this.options.searchFormId
            ) {
                this.triggerSearchClick();
            }
        }

        // Bind the keyup event for a search input field
        bindSearchEvent(searchInputSelector) {
            $(searchInputSelector).on("keyup", (event) => {
                var searchValue = $(event.currentTarget).val();
                this.reloadGrid({ search: searchValue });
            });
        }

        // Bind the button search click event, works with the form ID provided
        bindButtonSearchEvent() {
            // Use document.find to find the form and its buttons inside the form
            $(document)
                .find(this.options.searchFormId)
                .on("click", "[data-button-search]", (event) => {
                    event.preventDefault(); // Prevent the default action (like form submission)

                    const type = $(event.currentTarget).data("button-search");
                    const tableInstance = $(this.gridId).data("ajaxGrid");

                    // Handle CLEAR button type
                    if (type === "CLEAR" && tableInstance) {
                        this.refreshGrid();
                        return;
                    }

                    // Serialize form data and refresh the table
                    const combinedParams = this.serializeFormToParams(
                        $(this.options.searchFormId)
                    );
                    if (tableInstance) {
                        tableInstance.refresh(combinedParams);
                    }
                });
        }

        // Reload the grid with new parameters
        reloadGrid(extraParams) {
            this.$tableContainer.ajaxGrid("reload", {
                extraParams: extraParams,
            });
        }

        // Refresh the grid with new parameters
        refreshGrid() {
            this.$tableContainer.refresh({});
        }

        // Serialize form to key-value params
        serializeFormToParams(formSelector) {
            var formData = $(formSelector).serializeArray();
            var params = {};

            $.each(formData, (_, field) => {
                params[field.name] = field.value;
            });

            return params;
        }

        // Trigger the search click event
        triggerSearchClick() {
            if (
                this.options.searchFormId &&
                $(this.options.searchFormId).length
            ) {
                // Trigger a click on the first search button to initiate the search
                $(this.options.searchFormId)
                    .find("[data-button-search]")
                    .first()
                    .click();
            }
        }
    }

    // Attach the plugin to jQuery
    $.fn.ajaxGridPlugin = function (options) {
        return this.each(function () {
            let gridId = "#" + this.id;
            new AjaxGrid(gridId, options);
        });
    };
})(jQuery);
