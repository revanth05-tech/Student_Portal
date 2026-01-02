<?php
// includes/config.php
// NRSC ENTERPRISE CORE - Version 6.0 (Simple & Working)

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Database Configuration
$db_host = getenv('MYSQLHOST') ?: 'localhost';
$db_user = getenv('MYSQLUSER') ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('MYSQLDATABASE') ?: 'nrsc_portal_db';
$db_port = getenv('MYSQLPORT') ?: 3306;

// If MYSQL_PUBLIC_URL exists, parse it (Railway Public Proxy)
if (getenv('MYSQL_PUBLIC_URL')) {
    $url = parse_url(getenv('MYSQL_PUBLIC_URL'));
    $db_host = $url['host'] ?? $db_host;
    $db_user = $url['user'] ?? $db_user;
    $db_pass = $url['pass'] ?? $db_pass;
    $db_name = ltrim($url['path'] ?? '', '/') ?: $db_name;
    $db_port = $url['port'] ?? 3306;
}

// Use PDO (More Reliable for Cloud Connections)
try {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ];
    
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    
    // Create mysqli wrapper for compatibility with existing code
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, (int)$db_port);
    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }
    
} catch (Exception $e) {
    die("
    <div style='font-family: sans-serif; padding: 2rem; max-width: 600px; margin: 50px auto; border: 1px solid #e53e3e; border-radius: 8px; background: #fff5f5;'>
        <h2 style='color: #c53030; margin-top: 0;'>Database Connection Error</h2>
        <p><strong>Host:</strong> " . htmlspecialchars($db_host) . "</p>
        <p><strong>Port:</strong> " . htmlspecialchars($db_port) . "</p>
        <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        <hr style='border: none; border-top: 1px solid #feb2b2; margin: 1rem 0;'>
        <p style='font-size: 14px; color: #742a2a;'>
            Please ensure MYSQL_PUBLIC_URL is correctly set in Railway Variables.
        </p>
    </div>
    ");
}

// Schema Deployment
try {
    $check = $conn->query("SHOW TABLES LIKE 'users'");
    if ($check->num_rows == 0) {
        $conn->query("CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $conn->query("CREATE TABLE student_profiles (
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
        
        $admin_pass = password_hash('admin', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@nrsc.gov.in', '$admin_pass', 'admin')");
    }
} catch (Exception $e) {
    // Silent - tables might already exist
}

function sanitize($conn, $input) {
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($input))));
}
?>
