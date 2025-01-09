<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Unauthorized</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .text-red-600 {
            color: #e3342f;
        }
        .text-blue-500 {
            color: #3490dc;
        }
        .bg-blue-500 {
            background-color: #3490dc;
        }
        .bg-blue-700 {
            background-color: #2779bd;
        }
        .bg-gray-500 {
            background-color: #6c757d;
        }
        .bg-gray-700 {
            background-color: #495057;
        }
        .text-white {
            color: #fff;
        }
        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .rounded {
            border-radius: 0.25rem;
        }
        .hover\:bg-blue-700:hover {
            background-color: #2779bd;
        }
        .hover\:bg-gray-700:hover {
            background-color: #495057;
        }
        .flex {
            display: flex;
        }
        .space-x-4 > :not(:last-child) {
            margin-right: 1rem;
        }
        .no-underline {
            text-decoration: none;
        }
        .text-center {
            text-align: center;
        }
        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }
        .mb-4 {
            margin-bottom: 1rem;
        }
        .justify-center {
            justify-content: center;
        }
    </style>
    @livewireStyles
</head>
<body>
    @livewire('error-403')
    @livewireScripts
</body>
</html>
