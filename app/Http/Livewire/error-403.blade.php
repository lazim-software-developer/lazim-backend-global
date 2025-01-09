<div class="container mx-auto p-4">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold text-red-600">403 - Unauthorized</h1>
        <p class="mt-4 text-gray-600">Sorry, you are not authorized to access this page.</p>
        <div class="mt-6">
            <a href="{{ env('APP_URL').'/dashboard' }}" class="text-blue-500 hover:underline">Go to Homepage</a>
            <br>
            <a href="javascript:history.back()" class="text-blue-500 hover:underline">Go Back</a>
        </div>
    </div>
</div>
