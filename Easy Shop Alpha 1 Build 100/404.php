<?php
http_response_code(404); // Ensure the server sends the correct 404 status code
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Page Not Found - EasyShop</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        .error-container {
            max-width: 600px;
            padding: 2rem;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 700;
            color: #6c757d;
            margin: 0;
            line-height: 1;
        }
        .error-message {
            font-size: 1.5rem;
            margin: 1rem 0;
        }
        .error-info {
            font-size: 1rem;
            color: #6c757d;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="error-container">
    <p class="error-code">404</p>
    <h1 class="error-message">Page Not Found</h1>
    <p class="error-info">
        Oops! The page you are looking for doesn't exist. It may have been moved, deleted, or you entered the wrong address.
    </p>
    <p class="error-info">
        You can try <a href="javascript:history.back()">going back</a> or return to our <a href="/">homepage</a>.
    </p>
</div>
</body>
</html>