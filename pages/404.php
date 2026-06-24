<?php
require_once ROOT_PATH . '/siteConfig.php';
require_once ROOT_PATH . '/includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>404 - Page Not Found</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }

        .container {
            max-width: 500px;
            padding: 2rem;
        }

        h1 {
            font-size: 4rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        p {
            color: #555;
            margin-bottom: 2rem;
        }

        a {
            display: inline-block;
            margin-bottom: 2rem;
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }

        a:hover {
            text-decoration: underline;
        }

        .search-bar {
            display: flex;
            max-width: 400px;
            margin: auto;
            border: 1px solid #ccc;
            border-radius: 5px;
            overflow: hidden;
        }

        .search-bar input {
            flex: 1;
            padding: 0.75rem;
            border: none;
            outline: none;
            font-size: 1rem;
        }

        .search-bar button {
            padding: 0.75rem 1rem;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .search-bar button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>404</h1>
        <p>Oops! The page you're looking for doesn't exist.</p>
        <a href="<?= BASE_URL ?>">← Go back to Homepage</a>

        <div class="search-bar">
            <input type="text" placeholder="Search our site..." />
            <button onclick="alert('Search feature coming soon!')">Search</button>
        </div>
    </div>
</body>

</html>