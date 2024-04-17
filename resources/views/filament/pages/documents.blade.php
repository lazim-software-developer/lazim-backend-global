<x-filament-panels::page>

<div>
    <!-- Button trigger dropdown -->
    <button type="button" class="btn btn-primary" id="downloadButton" style="background-color: #007bff; color: white; padding: 10px 20px; border-radius: 5px; border: none; font-size: 16px; cursor: pointer;">
        Download Templates
    </button>
    <!-- Button to trigger modal -->
    <button type="button" class="btn btn-primary" id="uploadButton" data-bs-toggle="uploadModal" data-bs-target="#uploadModal" style="background-color: #007bff; color: white; padding: 10px 20px; border-radius: 5px; border: none; font-size: 16px; cursor: pointer;">
        Upload File
    </button>

    <!-- Hidden dropdown container -->
    <div class="dropdown-container" style="display: none; margin-top: 20px;" >
        <form action="{{ route('download') }}" method="POST" style="margin-top: 20px;">
            @csrf
            <div class="mb-3">
                <label for="templateSelect" class="form-label"><strong>Select Template</strong></label>
                <select class="form-select" id="templateSelect" name="template" required>
                    <option value="all" selected>All</option>
                    @foreach($services as $service)
                            <option value="{{ $service->value }}">{{ $service->name }}</option>
                    @endforeach
                    <!-- Add other options here -->
                </select>
            </div>
            <button type="submit" class="btn btn-success" style="padding: 10px 20px; border-radius: 5px; font-size: 16px; background-color: #007bff; color: white;" >Submit</button>
        </form>
    </div>
</div>

<!-- Modal -->
<div class="upload-container" style="display: none; max-width: 600px; margin: 20px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" >
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true" style="margin-top: 20px;">
    <div class="modal-dialog" style="margin-bottom: 20px;">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #007bff; color: white; padding: 16px 24px;">
                <h5 class="modal-title" id="uploadModalLabel" style="font-size: 18px; margin: 0;" >Upload Your Report Here</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 21px; color: white;"></button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <form action="{{ route('uploadAll') }}" method="POST" id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <!-- Property Group Dropdown -->
                    <div class="mb-3">
                        <label for="propertyGroupSelect" class="form-label">Select Property Group *</label>
                        <select required class="form-select" id="propertyGroupSelect" name="property_group" style="min-height: 38px; display: block; width: 100%; margin-bottom: 10px;">
                        
                        @foreach($propertyGroups as $propertyGroup)
                            <option value="{{ $propertyGroup['propertyGroupId'] }}">{{ $propertyGroup['propertyGroupName']['englishName'] }}</option>
                        @endforeach
                        </select>
                        <input type="hidden" name="property_name" value="{{ $propertyGroup['propertyGroupName']['englishName'] }}">

                    </div>

                    <!-- Service Period Dropdown -->
                    <div class="mb-3">
                        <label for="servicePeriodSelect" class="form-label">Select Service Period *</label>
                        
                        <select required class="form-select" id="servicePeriodSelect" name="service_period" style="min-height: 38px; display: block; width: 100%; margin-bottom: 10px;">
                            <!-- Options will be populated based on Property Group selection -->
                        </select>
                    </div>

                    <!-- Service Files Upload -->
                    @foreach($services as $service)
                    <div class="mb-3" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <label style="flex-basis: 40%; margin-right: 10px;">{{ $service->name }}*</label>
                        <div style="flex-basis: 58%;">
                            <input type="file" id="file_{{ $service->value }}" name="{{$service->value}}" class="form-control" style="display: block; width: 100%;" required>
                        </div>
                    </div>
                    @endforeach

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" id="submitUpload" style="padding: 10px 20px; border-radius: 5px; font-size: 16px; background-color: #007bff; color: white;" >Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.getElementById('downloadButton').addEventListener('click', function() {
        var dropdownContainer = document.querySelector('.dropdown-container');
        dropdownContainer.style.display = 'block';

        var uploadContainer = document.querySelector('.upload-container');
        uploadContainer.style.display = 'none'; 
    });

    document.getElementById('uploadButton').addEventListener('click', function() {
        var uploadContainer = document.querySelector('.upload-container');
        uploadContainer.style.display = 'block'; // This will show the container

        var dropdownContainer = document.querySelector('.dropdown-container');
        dropdownContainer.style.display = 'none';

        
});

document.getElementById('propertyGroupSelect').addEventListener('change', function() {
    const propertyId = this.value;
    const servicePeriodSelect = document.getElementById('servicePeriodSelect');

    // Clear existing options in service period dropdown
    servicePeriodSelect.innerHTML = '';

    // Make the API call with increased timeout
    axios.get(`https://qagate.dubailand.gov.ae/mollak/external/sync/invoices/${propertyId}/servicechargeperiods`, {
        headers: {
            'Content-Type': 'application/json',
            'Consumer-ID': '{{ env("MOLLAK_CONSUMER_ID") }}'
        },
        timeout: 10000  // Increase timeout to 10 seconds
    })
    .then(function(response) {
        console.log(response);
        // Populate the dropdown with new options
        response.data.forEach(function(period) {
            const option = document.createElement('option');
            option.value = period.name;
            option.textContent = period.name;
            servicePeriodSelect.appendChild(option);
        });

        // Enable the submit button once data is loaded
        document.getElementById('submitUpload').disabled = false;
    })
    .catch(function(error) {
        console.log('Error fetching service periods:', error);
        servicePeriodSelect.innerHTML = '<option>Error loading data</option>';
        document.getElementById('submitUpload').disabled = true;
    });
});


</script>
{{$this->table}}
</x-filament-panels::page>
