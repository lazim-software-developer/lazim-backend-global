<div class="container mx-auto p-4">
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="mx-auto mb-4" style="max-width: 150px;">
        <h1 class="text-2xl font-bold text-red-600">403 - Unauthorized</h1>
        <p class="mt-4 text-gray-600">Sorry, you are not authorized to access this page.</p>
        <div class="mt-6 flex justify-center space-x-4">
            <a href="javascript:history.back()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700 no-underline">Go Back</a>
            <a href="{{ env('APP_URL').'/app' }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-700 no-underline">Go to Homepage</a>
        </div>
    </div>
</div>
