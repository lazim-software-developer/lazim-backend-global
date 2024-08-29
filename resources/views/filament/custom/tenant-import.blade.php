<div>
    <x-filament::breadcrumbs :breadcrumbs="[
        '/admin/building/flat-tenants' => 'Tenants',
        '' => 'List',
    ]" />
    <div class="flex justify-between mt-1">
        <div class="font-bold text-3xl">Tenants</div>
    </div>
    <form id="uploadForm" enctype="multipart/form-data" class="w-full max-w-sm flex mt-2">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="fileInput">
                Import File
            </label>
            <input style="width:auto"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="file" name="file" type="file">
        </div>
        <div class="flex items-center justify-between mt-3">
            <button id="myButton" style="margin-left:30px;background-color: rgba(216,116,17,255);color: white;border-radius: 9px;text-align: center;width:95px;height:37px;"
                class="bg-blue-500 hover:bg-blue-708 text-black py-2 px-4 rounded
                    focus:outline-none focus:shadow-outline"
                    type="button" onclick="uploadFile()">
                Import
            </button>
        </div>
    </form>
</div>
<script>
    var button = document.getElementById("myButton");

    // Set the background color on hover
    button.onmouseover = function() {
        button.style.backgroundColor = "#efa107"; // Change to the desired hover color
    };

    // Reset the background color on mouseout
    button.onmouseout = function() {
        button.style.backgroundColor = "#d8760e"; // Change back to the default color
    };
    function uploadFile() {
        
        //api call
        const formData = new FormData(document.getElementById('uploadForm'));
        console.log(formData);
        const appUrl = `{{ config('app.url') }}`;
        console.log(appUrl);
        fetch(appUrl+'/api/formspeaker', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            location.reload();
            // Handle success
        })
        .catch(error => {
            console.error('Error:', error);
            // Handle error
        });
    }
</script>