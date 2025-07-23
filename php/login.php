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

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: admin_panel.php");
    exit;
}

// Load configuration
$config = require __DIR__ . '/../includes/config.php';

// Database credentials
$db_config = $config['db'];
$dsn = 'mysql:host=' . $db_config['host'] . ';dbname=' . $db_config['dbname'];
$db_username = $db_config['username'];
$db_password = $db_config['password'];

// Admin credentials (plain-text password)
$admin_credentials = $config['admin'];
$admin_username = $admin_credentials['username'];
$admin_password = $admin_credentials['password']; // Plain-text password

$error = '';

try {
    $pdo = new PDO($dsn, $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Create the login_logs table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS login_logs (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NOT NULL,
    os VARCHAR(50),
    username VARCHAR(100),
    status VARCHAR(10)
)";
try {
    $pdo->exec($createTableSQL);
} catch (PDOException $e) {
    die("Table creation failed: " . $e->getMessage());
}

date_default_timezone_set('Asia/Kolkata');

// Function to log login attempts
function log_login_attempt($pdo, $username, $status) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $os = 'Unknown OS';

    if (preg_match('/linux/i', $user_agent)) {
        $os = 'Linux';
    } elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
        $os = 'Mac';
    } elseif (preg_match('/windows|win32/i', $user_agent)) {
        $os = 'Windows';
    }

    $timestamp = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("INSERT INTO login_logs (timestamp, ip_address, os, username, status) VALUES (:timestamp, :ip, :os, :username, :status)");
    $stmt->execute([
        ':timestamp' => $timestamp,
        ':ip' => $ip,
        ':os' => $os,
        ':username' => $username,
        ':status' => $status
    ]);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));

    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    } else {
        // Verify admin credentials (plain-text comparison)
        if ($username === $admin_username && $password === $admin_password) {
            // Set session variables for the logged-in admin
            $_SESSION['loggedin'] = true;
            $_SESSION['user_role'] = 'admin'; // Set the role to admin

            log_login_attempt($pdo, $username, 'Success');
            header("Location: admin_panel.php");
            exit;
        } else {
            $error = "Wrong credentials";
            log_login_attempt($pdo, $username, 'Failed');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body {
            font-family: 'Consolas', 'Courier New', Courier, monospace;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
            color: #e0e0e0;
            overflow-x: hidden; /* Prevent horizontal scrolling */
        }
        .login-container {
            background: rgba(44, 44, 44, 0.8); /* Slightly transparent background for the form */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.7);
            max-width: 90%;
            width: 100%;
            border: 1px solid #333;
            position: relative;
            z-index: 1;
            text-align: center; /* Center the content */
            box-sizing: border-box; /* Ensure padding is included in width calculation */
        }
        h2 {
            margin-top: 0;
            color: #00aaff;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #ccc;
        }
        .form-group input {
            width: calc(100% - 20px); /* Adjust for padding and borders */
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #444;
            border-radius: 5px;
            background: #333;
            color: #fff;
        }
        .form-group input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .form-group input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .error {
            color: #ff4d4d;
            margin-bottom: 15px;
            text-align: center; /* Center the error message */
        }
        .serious-line {
            border-top: 3px solid #00aaff;
            margin-top: 30px;
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
        }
        .security-message {
            color: #ff4d4d; /* Default text color */
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }
        .warning-message {
            color: #ffff00; /* Yellow color */
        }
        .order-message {
            color: #ff4d4d; /* Red color */
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .badge {
            display: inline-block;
            width: 100px; /* Adjust width as needed */
            height: 100px; /* Should be equal to width */
            border-radius: 50%;
            background: linear-gradient(135deg, #00aaff, #0056b3);
            color: #fff;
            font-weight: bold;
            font-size: 0.6em; /* Adjust font size to fit better */
            text-transform: uppercase;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            position: relative;
            text-align: center;
            line-height: 100px; /* Aligns text vertically */
            padding: 0; /* Remove padding */
            overflow: hidden; /* Ensures text doesn’t overflow */
        }
        .badge::before {
            content: 'Phantom Ops';
            display: block;
            font-size: 0.9em; /* Adjust font size to fit inside the badge */
            color: #e0e0e0;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%); /* Center text horizontally and vertically */
            width: 100%; /* Ensure text uses full width of the badge */
            text-align: center;
            white-space: nowrap; /* Prevent text from wrapping */
        }
        /* Adding a grid overlay for a digital effect */
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            background: rgba(0, 0, 0, 0.3);
            background-image: linear-gradient(90deg, transparent 1px, rgba(255, 255, 255, 0.1) 1px),
                              linear-gradient(180deg, transparent 1px, rgba(255, 255, 255, 0.1) 1px);
            background-size: 40px 40px;
            z-index: 0;
        }
        /* Button Style */
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
            cursor: pointer;
        }
        .button:hover {
            background-color: #0056b3;
        }
        /* Responsive Styles */
        @media (max-width: 600px) {
            .login-container {
                padding: 15px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.7);
                max-width: 95%;
            }
            .badge {
                width: 80px;
                height: 80px;
                line-height: 80px;
            }
            .badge::before {
                font-size: 0.8em;
            }
            .form-group {
                margin-bottom: 10px;
            }
            .form-group input {
                padding: 8px;
            }
            .form-group input[type="submit"] {
                padding: 10px;
            }
            .security-message {
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="login-container">
        <div class="header">
            <div class="badge"></div>
        </div>
        <h2>Admin Login</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required aria-label="Username" placeholder="Enter username">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required aria-label="Password" placeholder="Enter password">
            </div>
            <div class="security-message">
                <p>
                    <span class="warning-message">Authorized personnel only. Unauthorized access is prohibited and monitored.</span><br>
                    <span class="order-message">- By order of Animesh, CEO, Phantom Ops.</span>
                </p>
            </div>
            <div class="form-group">
                <input type="submit" value="Login">
            </div>
        </form>  
        <a href="/" class="button">Return to Homepage</a>
        <div class="serious-line"></div>
    </div>
</body>
</html>
