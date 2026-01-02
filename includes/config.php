<?php
// includes/config.php
// NRSC ENTERPRISE CORE - Version 8.0 (Persistent Connection)

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database Configuration
$db_host = getenv('MYSQLHOST') ?: 'localhost';
$db_user = getenv('MYSQLUSER') ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('MYSQLDATABASE') ?: 'nrsc_portal_db';
$db_port = getenv('MYSQLPORT') ?: 3306;

// If MYSQL_PUBLIC_URL exists, parse it
if (getenv('MYSQL_PUBLIC_URL')) {
    $url = parse_url(getenv('MYSQL_PUBLIC_URL'));
    $db_host = $url['host'] ?? $db_host;
    $db_user = $url['user'] ?? $db_user;
    $db_pass = $url['pass'] ?? $db_pass;
    $db_name = ltrim($url['path'] ?? '', '/') ?: $db_name;
    $db_port = $url['port'] ?? 3306;
}

// Use Persistent Connection (p: prefix)
$conn = @new mysqli('p:' . $db_host, $db_user, $db_pass, $db_name, (int)$db_port);

if ($conn->connect_error) {
    // Fallback: Try without persistent
    $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name, (int)$db_port);
}

if ($conn->connect_error) {
    echo "<div style='font-family:sans-serif;padding:2rem;max-width:600px;margin:50px auto;border:1px solid red;background:#fff0f0;'>";
    echo "<h2>Database Error</h2>";
    echo "<p><b>Host:</b> " . htmlspecialchars($db_host) . "</p>";
    echo "<p><b>Port:</b> " . htmlspecialchars($db_port) . "</p>";
    echo "<p><b>Error:</b> " . htmlspecialchars($conn->connect_error) . "</p>";
    echo "<hr><p>Check your MYSQL environment variables in Railway.</p>";
    echo "</div>";
    exit;
}

// Set longer timeout after connection
$conn->query("SET wait_timeout=28800");
$conn->query("SET interactive_timeout=28800");

// Schema Deployment
$check = @$conn->query("SHOW TABLES LIKE 'users'");
if ($check && $check->num_rows == 0) {
    $conn->multi_query("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE student_profiles (
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
        );
    ");
    while ($conn->next_result()) {;}
    
    $admin_pass = password_hash('admin', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@nrsc.gov.in', '$admin_pass', 'admin')");
}

function sanitize($conn, $input) {
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($input))));
}
?>
