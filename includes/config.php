<?php
// includes/config.php
// NRSC ENTERPRISE CORE - Version 5.0 (Final Robust)

// 1. Session & Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 3600);
session_start();

// 2. Database Configuration Logic
// Priority: MYSQL_PUBLIC_URL > MYSQL_URL > Individual Variables

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'nrsc_portal_db';
$db_port = 3306;

// Helper to parse connection string
function parse_db_url($url_string) {
    if (!$url_string) return null;
    $p = parse_url($url_string);
    return [
        'host' => $p['host'] ?? null,
        'user' => $p['user'] ?? null,
        'pass' => $p['pass'] ?? null,
        'name' => ltrim($p['path'] ?? '', '/'),
        'port' => $p['port'] ?? 3306
    ];
}

// Logic to determine best credentials
if ($p = parse_db_url(getenv('MYSQL_PUBLIC_URL'))) {
    // 1. Public Proxy (Best for reliability)
    extract($p, EXTR_PREFIX_ALL, 'db');
} elseif ($p = parse_db_url(getenv('MYSQL_URL'))) {
    // 2. Internal Connection String
    extract($p, EXTR_PREFIX_ALL, 'db');
} else {
    // 3. Individual Env Vars (Fallback)
    if (getenv('MYSQLHOST')) $db_host = getenv('MYSQLHOST');
    if (getenv('MYSQLUSER')) $db_user = getenv('MYSQLUSER');
    if (getenv('MYSQLPASSWORD')) $db_pass = getenv('MYSQLPASSWORD');
    if (getenv('MYSQLDATABASE')) $db_name = getenv('MYSQLDATABASE');
    if (getenv('MYSQLPORT')) $db_port = getenv('MYSQLPORT');
}

// 3. Connect Engine (SSL Enhanced)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_init();
    
    // Fix for "Gone Away" / Greeting Packet: Disable strict Cert check & Increase Timeout
    if (defined('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT')) {
        $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
    }
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 20);
    
    // Force TCP
    if (strpos($db_host, 'tcp://') === false) {
        $target_host = 'tcp://' . $db_host; // Force Network Mode
    } else {
        $target_host = $db_host;
    }
    
    // Attempt Connection with SSL Flag (Client Flag 64 or MYSQLI_CLIENT_SSL)
    // We pass NULL for keys to use default/auto-negotiation
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL); 
    
    // Connect
    $connected = @$conn->real_connect($target_host, $db_user, $db_pass, $db_name, (int)$db_port, NULL, MYSQLI_CLIENT_SSL);
    
    if (!$connected) {
        // Retry without SSL if first attempt fails (Fallback)
        $conn = mysqli_init();
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 20);
        $conn->real_connect($target_host, $db_user, $db_pass, $db_name, (int)$db_port);
    }

} catch (Exception $e) {
    // ERROR HANDLER
    $safe_host = htmlspecialchars($db_host);
    $safe_port = htmlspecialchars($db_port);
    
    die("
    <div style='font-family: sans-serif; padding: 2rem; max-width: 600px; margin: 0 auto; border: 1px solid #ccc; border-radius: 8px; margin-top: 50px;'>
        <h2 style='color: #de350b; margin-top: 0;'>Secure Connection Failed</h2>
        <div style='background: #f4f5f7; padding: 1rem; border-radius: 4px; font-family: monospace;'>
            Target: <strong>$safe_host</strong> : <strong>$safe_port</strong><br>
            Error: " . $e->getMessage() . "
        </div>
        <p style='margin-top: 1rem; color: #666;'>
           Attempted SSL connection. Ensure 'MYSQL_PUBLIC_URL' is correct.
        </p>
    </div>
    ");
}

// 4. Schema Deployment (Only runs if tables are missing)
try {
    $tbl_check = $conn->query("SHOW TABLES LIKE 'users'");
    if ($tbl_check->num_rows == 0) {
        deploy_schema($conn);
    }
} catch (Exception $e) {
    // Silent fail on schema check to avoid crashing if DB is read-only
}

// 5. Schema Definition
function deploy_schema($conn) {
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS student_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL UNIQUE,
        full_name VARCHAR(100) NOT NULL,
        college_id VARCHAR(50) UNIQUE NOT NULL,
        branch VARCHAR(100),
        year VARCHAR(20),
        dob DATE,
        phone VARCHAR(20),
        address TEXT,
        bio TEXT,
        profile_pic VARCHAR(255) DEFAULT 'default.png',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Determine Admin Pass (Only if creating new)
    $admin_pass = password_hash('admin', PASSWORD_DEFAULT);
    $conn->query("INSERT IGNORE INTO users (username, email, password, role) VALUES ('admin', 'admin@nrsc.gov.in', '$admin_pass', 'admin')");
}

// Global Helpers
function sanitize($conn, $input) {
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($input))));
}
?>
