<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Unauthorized</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .error-container {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 28rem;
            width: 90%;
        }
        .error-icon {
            color: #ef4444;
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .error-title {
            color: #111827;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .error-message {
            color: #6b7280;
            margin-bottom: 1.5rem;
        }
        .button-container {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        .button {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .button-primary {
            background-color: #3b82f6;
            color: white;
        }
        .button-secondary {
            background-color: #6b7280;
            color: white;
        }
        .button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">Access Denied</h1>
        <p class="error-message">Sorry, you are not authorized to access this page.</p>
        <div class="button-container">
            <a href="javascript:history.back()" class="button button-secondary">Go Back</a>
            <a href="{{ env('APP_URL').'/app' }}" class="button button-primary">Home</a>
        </div>
    </div>
</body>
</html>
