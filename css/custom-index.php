<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directory Listing</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 90%;
            width: 600px;
            box-sizing: border-box;
            text-align: center;
        }
        h1 {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
        }
        p {
            margin: 5px 0;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 1em;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
        }
        .button:hover {
            background-color: #0056b3;
        }
        @media (max-width: 600px) {
            .container {
                width: 95%;
                padding: 15px;
            }
            h1 {
                font-size: 1.25em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Directory Listing</h1>
        <p>This directory contains the following files:</p>
        <?php
        $excluded_files = ['.', '..', '.htaccess','custom-index.php', 'styles.css', 'folder_icon.webp'];

        $dir = opendir('.');
        while (($file = readdir($dir)) !== false) {
            if (!in_array($file, $excluded_files)) {
                echo '<p><a href="' . htmlspecialchars($file) . '">' . htmlspecialchars($file) . '</a></p>';
            }
        }
        closedir($dir);
        ?>
        <br>
        <a href="/" class="button">Return to Homepage</a>
    </div>
</body>
</html>
