<?php
http_response_code(500); // Ensure the server sends the correct 500 status code
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Server Error - EasyShop</title>
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
            color: #dc3545;
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
    <p class="error-code">Error 500</p>
    <h1 class="error-message">Oops! Something went wrong.</h1>
    <p class="error-info">
        We're sorry, but our server encountered an unexpected error. We've been notified about this issue and will look into it shortly.
    </p>
    <p class="error-info">
        In the meantime, you can try <a href="javascript:history.back()">going back</a> or return to our <a href="/">homepage</a>.
    </p>
</div>
</body>
</html>