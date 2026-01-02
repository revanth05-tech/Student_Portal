<?php
// includes/config.php
// NRSC ENTERPRISE CORE - Auto-Healing Architecture

// 1. Session & Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // TEMPORARY: For Debugging Railway
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 3600);
session_start();

// 2. Database Configuration
$db_host = getenv('MYSQLHOST') ?: 'localhost';
$db_user = getenv('MYSQLUSER') ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('MYSQLDATABASE') ?: 'nrsc_portal_db';
$db_port = getenv('MYSQLPORT') ?: 3306;

// 3. AUTO-HEALING CONNECTION ENGINE
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Throw exceptions instead of warnings

try {
    // Attempt standard connection
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    
} catch (mysqli_sql_exception $e) {
    // If connection failed...
    try {
        // Fallback: Try connecting without DB (Localhost setup mode)
        $conn = new mysqli($db_host, $db_user, $db_pass, "", $db_port);
        
        // Only try to create DB if on Localhost (Cloud users usually can't)
        if ($db_host == 'localhost' || $db_host == '127.0.0.1') {
            $conn->query("CREATE DATABASE IF NOT EXISTS $db_name");
            $conn->select_db($db_name);
        } else {
            // On Cloud: If we can't connect to specific DB, it's a fatal config error
            die("<h1>Cloud Database Error</h1><p>Could not connect to database '$db_name'. Check your Railway Variables.</p><pre>".$e->getMessage()."</pre>");
        }
    } catch (Exception $ex) {
         die("<h1>Fatal Connection Error</h1><p>Please check database credentials.</p><pre>".$ex->getMessage()."</pre>");
    }
}

// Ensure schema exists
try {
    deploy_schema($conn);
} catch (Exception $e) {
    die("<h1>Schema Deployment Failed</h1><pre>".$e->getMessage()."</pre>");
}

// 4. Schema Deployment System
function deploy_schema($conn) {
    // A. Users Table (RBAC)
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        INDEX(username),
        INDEX(role)
    )");

    // B. Student Profiles (Extended Attributes)
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
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX(full_name),
        INDEX(college_id)
    )");

    // C. Seed Default Admin
    $admin_pass = password_hash('admin', PASSWORD_DEFAULT);
    $check = $conn->query("SELECT id FROM users WHERE role='admin' LIMIT 1");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@nrsc.gov.in', '$admin_pass', 'admin')");
    }
}

// 5. Global Helpers
define('APP_NAME', 'NRSC Enterprise Portal');
define('APP_VER', '4.5.0');

function sanitize($conn, $input) {
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($input))));
}
?>
