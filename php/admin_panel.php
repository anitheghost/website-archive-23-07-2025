<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '.animeshyellow.online',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Load configuration
$config = require __DIR__ . '/../includes/config.php';

// Database connection parameters
$host = $config['db']['host'];
$dbname = $config['db']['dbname'];
$username = $config['db']['username'];
$password = $config['db']['password'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch contact form submissions
$sqlSubmissions = "SELECT id, `timestamp`, name, email, mobile, address, message FROM contact_form_submissions ORDER BY id DESC";
$stmtSubmissions = $pdo->prepare($sqlSubmissions);

try {
    $stmtSubmissions->execute();
    $resultsSubmissions = $stmtSubmissions->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

// Fetch login logs
$sqlLogs = "SELECT id, timestamp, ip_address, os, username, status FROM login_logs ORDER BY timestamp DESC";
$stmtLogs = $pdo->prepare($sqlLogs);

try {
    $stmtLogs->execute();
    $resultsLogs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
  <style>
    body {
        font-family: 'Consolas', 'Courier New', Courier, monospace;
        margin: 0;
        background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
        color: #e0e0e0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }
    .container {
        background: rgba(44, 44, 44, 0.8);
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.7);
        max-width: 100%;
        width: 100%;
        position: relative;
        color: #e0e0e0;
        overflow-x: auto; /* Ensures horizontal scroll if content overflows */
    }
    .header {
        margin-bottom: 20px;
        text-align: center;
    }
    .logout {
        position: absolute;
        top: 10px;
        right: 10px;
        text-decoration: none;
        color: #fff;
        background-color: #dc3545;
        padding: 8px 16px;
        border-radius: 5px;
        font-size: 0.9em;
        text-align: center;
        border: none;
    }
    .logout:hover {
        background-color: #c82333;
    }
    h1, h2 {
        color: #00aaff;
        text-align: center;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    th, td {
        border: 1px solid #444;
        padding: 8px;
        text-align: left;
        font-size: 0.9em;
    }
    th {
        background-color: #007bff;
        color: #fff;
    }
    .file-list {
        margin-bottom: 20px;
    }
    .file-list a {
        text-decoration: none;
        color: #00aaff;
    }
    .file-list a:hover {
        color: #007bff;
    }
    textarea {
        width: 100%;
        height: 200px;
        margin-bottom: 20px;
        padding: 10px;
        border: 1px solid #444;
        border-radius: 5px;
        background: #333;
        color: #e0e0e0;
        box-sizing: border-box;
    }
	  /* Basic modal styling */
.modal {
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Transparent black background */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000; /* Ensure it appears above other elements */
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    text-align: center;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    width: 300px;
}

.modal-content p {
    font-size: 16px;
    margin-bottom: 20px;
    color: #333; /* Darker text color */
}

.action-button {
    background-color: #dc3545; 
    color: white; 
    padding: 10px; 
    border: none; 
    border-radius: 5px; 
    cursor: pointer;
}

.action-button:hover {
    background-color: #c82333;
}

.message {
    text-align: center; 
    margin-top: 10px;
}

    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .container {
            padding: 15px;
        }
    }
    @media (max-width: 992px) {
        .logout {
            top: 5px;
            right: 5px;
            padding: 6px 12px;
            font-size: 0.8em;
        }
        table {
            font-size: 0.8em;
        }
    }
    @media (max-width: 768px) {
        .container {
            padding: 10px;
        }
        .logout {
            top: 5px;
            right: 5px;
            padding: 5px 10px;
            font-size: 0.7em;
        }
        table {
            font-size: 0.7em;
            overflow-x: auto;
            display: block;
        }
        th, td {
            padding: 6px;
        }
    }
    @media (max-width: 480px) {
        .container {
            padding: 10px;
        }
        .logout {
            top: 5px;
            right: 5px;
            padding: 4px 8px;
            font-size: 0.6em;
        }
        table {
            font-size: 0.6em;
        }
        th, td {
            padding: 4px;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <a href="?logout" class="logout">Logout</a>
        <div class="header">
            <h1>Welcome to Phantom Ops Bureau</h1>
        </div>

        <!-- Contact Form Submissions -->
        <h2>Contact Form Submissions</h2>

        <?php if (isset($resultsSubmissions) && count($resultsSubmissions) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Timestamp</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Address</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultsSubmissions as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
		<!-- Clear Contact Form Submissions Button -->
<button type="button" id="clearSubmissionsBtn" class="action-button">
    Clear Contact Form Submissions
</button>
<!-- Placeholder for showing success or error message -->
<p id="clearSubmissionsMsg" class="message" style="display: none;"></p>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="modal" style="display:none;">
    <div class="modal-content">
        <p id="modalMessage">Are you sure you want to clear all contact form submissions? This action cannot be undone.</p>
        <button id="confirmYes" class="action-button">Yes</button>
        <button id="confirmCancel" class="action-button">Cancel</button>
    </div>
</div>

<script>
    // Get the modal and buttons
    var modal = document.getElementById('confirmationModal');
    var confirmYes = document.getElementById('confirmYes');
    var confirmCancel = document.getElementById('confirmCancel');
    var clearSubmissionsBtn = document.getElementById('clearSubmissionsBtn');
    
    // Show modal when button is clicked
    clearSubmissionsBtn.addEventListener('click', function() {
        modal.style.display = 'flex'; // Show the modal
    });

    // If 'Yes' is clicked
    confirmYes.addEventListener('click', function() {
        modal.style.display = 'none'; // Hide the modal
        clearContactSubmissions();    // Call the function to clear submissions
    });

    // If 'Cancel' is clicked
    confirmCancel.addEventListener('click', function() {
        modal.style.display = 'none'; // Simply hide the modal
    });

    // Function to clear contact form submissions
    function clearContactSubmissions() {
        clearSubmissionsBtn.disabled = true; // Disable button to prevent multiple clicks

        // Send AJAX request to clear contact form submissions
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'clear_submissions.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var msgElement = document.getElementById('clearSubmissionsMsg');
                if (xhr.status === 200) {
                    // Success message
                    msgElement.innerText = 'Contact form submissions deleted successfully';
                    msgElement.style.color = 'red';
                } else {
                    // Error message
                    msgElement.innerText = 'Failed to delete submissions: ' + xhr.responseText;
                    msgElement.style.color = 'red';
                }
                msgElement.style.display = 'block';

                // Redirect to the admin panel after a delay if successful
                if (xhr.status === 200) {
                    setTimeout(function() {
                        window.location.href = 'admin_panel.php';
                    }, 2000); // 2 seconds delay
                }

                // Clear the message after 5 seconds
                setTimeout(function() {
                    msgElement.style.display = 'none';
                    msgElement.innerText = ''; // Clear message text
                }, 5000);

                clearSubmissionsBtn.disabled = false; // Re-enable the button after response
            }
        };
        xhr.send();
    }
</script>
        <?php else: ?>
            <p>No submissions found.</p>
        <?php endif; ?>

        <hr>

        <!-- Login Logs -->
        <h2>Login Logs</h2>

        <?php if (isset($resultsLogs) && count($resultsLogs) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Timestamp</th>
                        <th>IP Address</th>
                        <th>OS</th>
                        <th>Username</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultsLogs as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                            <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
                            <td><?php echo htmlspecialchars($row['os']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
		<!-- Clear Login Logs Button -->
<button type="button" id="clearLogsBtn" class="action-button">
    Clear Login Logs
</button>
<!-- Placeholder for showing success or error message -->
<p id="clearLogsMsg" style="color: red; display: none; text-align: center;">Login logs deleted successfully</p>

<!-- Confirmation Modal for Clear Login Logs -->
<div id="clearLogsModal" class="modal" style="display:none;">
    <div class="modal-content">
        <p id="modalLogsMessage">Are you sure you want to clear all login logs? This action cannot be undone.</p>
        <button id="confirmLogsYes" class="action-button">Yes</button>
        <button id="confirmLogsCancel" class="action-button">Cancel</button>
    </div>
</div>

<script>
    // Get the modal and buttons
    var clearLogsModal = document.getElementById('clearLogsModal');
    var confirmLogsYes = document.getElementById('confirmLogsYes');
    var confirmLogsCancel = document.getElementById('confirmLogsCancel');
    var clearLogsBtn = document.getElementById('clearLogsBtn');

    // Show modal when button is clicked
    clearLogsBtn.addEventListener('click', function() {
        clearLogsModal.style.display = 'flex'; // Show the modal
    });

    // If 'Yes' is clicked
    confirmLogsYes.addEventListener('click', function() {
        clearLogsModal.style.display = 'none'; // Hide the modal
        clearLoginLogs();    // Call the function to clear login logs
    });

    // If 'Cancel' is clicked
    confirmLogsCancel.addEventListener('click', function() {
        clearLogsModal.style.display = 'none'; // Simply hide the modal
    });

    // Function to clear login logs
    function clearLoginLogs() {
        // Send AJAX request to clear login logs
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'erase.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var msgElement = document.getElementById('clearLogsMsg');
                if (xhr.status === 200) {
                    // Display success message
                    msgElement.style.display = 'block';

                    // Redirect to the admin panel after a delay
                    setTimeout(function() {
                        window.location.href = 'admin_panel.php'; // Adjust the URL as needed
                    }, 2000); // 2000 milliseconds = 2 seconds
                } else {
                    // Handle errors if necessary
                    msgElement.innerText = 'Failed to delete logs: ' + xhr.responseText;
                    msgElement.style.color = 'red';
                    msgElement.style.display = 'block';
                }
            }
        };
        xhr.send();
    }
</script>
        <?php else: ?>
            <p>No login logs found.</p>
        <?php endif; ?>

    </div>
</body>
</html>
