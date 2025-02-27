<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #4a5568; }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 10px 20px 20px 20px;
        }
        .header { text-align: left; padding: 10px 0; }
        .logo { max-width: 150px; }
        .header-divider {
            border-top: 1px solid #e2e8f0;
            margin: 0 0 15px 0;
        }
        .content {
            background-color: #ffffff;
            padding: 15px 30px 30px 30px;
            border-radius: 8px;
        }
        .title { color: #2b6cb0; font-size: 18px; font-weight: bold; margin: 15px 0; }
        .feature-list { margin-left: 20px; color: #4a5568; }
        .footer {
            text-align: center;
            padding: 10px;
            color: #718096;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2b6cb0;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://lazim-dev.s3.ap-south-1.amazonaws.com/dev/01JN2XZW0YY3YDSS9W7DSKETMV.png"
             alt="Lazim Logo" class="logo">
        </div>
        <hr class="header-divider">
        <div class="content">
            @yield('content')
        </div>
        <div class="footer">
            © {{ date('Y') }} Lazim. All rights reserved.
        </div>
    </div>
</body>
</html>
