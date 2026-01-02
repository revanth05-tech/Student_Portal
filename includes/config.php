<?php
// includes/config.php
// NRSC ENTERPRISE CORE - Version 9.0 (PDO Connection)

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database Configuration
$db_host = getenv('MYSQLHOST') ?: 'localhost';
$db_user = getenv('MYSQLUSER') ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('MYSQLDATABASE') ?: 'nrsc_portal_db';
$db_port = getenv('MYSQLPORT') ?: 3306;

// Connect using PDO
try {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 30
    ]);
    
    // Create mysqli wrapper for backward compatibility
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, (int)$db_port);
    
} catch (PDOException $e) {
    // If PDO fails, show detailed error
    echo "<div style='font-family:sans-serif;max-width:600px;margin:50px auto;padding:20px;border:2px solid #c00;background:#fee;'>";
    echo "<h2 style='color:#c00;margin-top:0;'>Connection Failed</h2>";
    echo "<p><strong>Host:</strong> " . htmlspecialchars($db_host) . "</p>";
    echo "<p><strong>Port:</strong> " . htmlspecialchars($db_port) . "</p>";
    echo "<p><strong>Database:</strong> " . htmlspecialchars($db_name) . "</p>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    exit;
} catch (mysqli_sql_exception $e) {
    // mysqli fallback error
    echo "<div style='font-family:sans-serif;max-width:600px;margin:50px auto;padding:20px;border:2px solid #c00;background:#fee;'>";
    echo "<h2 style='color:#c00;'>mysqli Connection Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    exit;
}

// Schema Deployment
try {
    $check = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($check->rowCount() == 0) {
        $pdo->exec("CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE student_profiles (
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
        $pdo->exec("INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@nrsc.gov.in', '$admin_pass', 'admin')");
    }
} catch (Exception $e) {
    // Silent on schema errors
}

function sanitize($conn, $input) {
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($input))));
}
?>
