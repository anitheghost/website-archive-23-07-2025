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
            max-width: 95%;
            width: 600px;
            box-sizing: border-box;
            text-align: left;
        }
        h1 {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            overflow-x: auto; /* Enable horizontal scrolling on smaller screens */
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            word-wrap: break-word; /* Allow long words to wrap */
        }
        th {
            background-color: #f9f9f9;
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
        .view-button {
            display: inline-block;
            padding: 5px 10px;
            font-size: 0.9em;
            color: #fff;
            background-color: #28a745;
            border: none;
            border-radius: 3px;
            text-decoration: none;
            text-align: center;
        }
        .view-button:hover {
            background-color: #218838;
        }
        @media (max-width: 600px) {
            .container {
                width: 95%;
                padding: 15px;
            }
            h1 {
                font-size: 1.25em;
            }
            table {
                font-size: 0.9em;
                display: block;
                overflow-x: auto; /* Ensure table is scrollable on small screens */
            }
            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Directory Listing</h1>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Filename</th>
                        <th>Filetype</th>
                        <th>Size</th>
                        <th>Timestamp (IST)</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    date_default_timezone_set('Asia/Kolkata'); // Set timezone to IST

                    $excluded_files = ['.', '..', '.htaccess', 'custom-index.php'];

                    $dir = opendir('.');
                    $id = 1;
                    while (($file = readdir($dir)) !== false) {
                        if (!in_array($file, $excluded_files)) {
                            $size = filesize($file) / (1024 * 1024); // Convert to MB
                            $timestamp = date('Y-m-d H:i:s', filemtime($file)); // Get file modification time in IST
                            $filetype = pathinfo($file, PATHINFO_EXTENSION); // Get file extension
                            echo '<tr>';
                            echo '<td>' . $id . '</td>'; // Display ID
                            echo '<td><a href="' . htmlspecialchars($file) . '">' . htmlspecialchars($file) . '</a></td>';
                            echo '<td>' . htmlspecialchars($filetype) . '</td>'; // Display file type
                            echo '<td>' . number_format($size, 2) . ' MB</td>'; // Display size with 2 decimal places
                            echo '<td>' . htmlspecialchars($timestamp) . '</td>'; // Display timestamp in IST
                            echo '<td><a href="' . htmlspecialchars($file) . '" class="view-button" target="_blank">View</a></td>'; // View button
                            echo '</tr>';
                            $id++;
                        }
                    }
                    closedir($dir);
                    ?>
                </tbody>
            </table>
        </div>
        <br>
        <a href="/" class="button">Return to Homepage</a>
    </div>
</body>
</html>
