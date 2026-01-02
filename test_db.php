<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Railway Diagnostic Tool</h1>";

if (!extension_loaded('mysqli')) {
    die("<h2 style='color:red'>CRITICAL ERROR: MySQLi Extension NOT loaded.</h2><p>Railway is not using your Dockerfile. <br>Go to Railway > Settings > Build > Builder and select 'Dockerfile'.</p>");
}

$host = getenv('MYSQLHOST');

echo "<h3>Available Environment Variables:</h3>";
echo "<pre>";
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'MYSQL') !== false || strpos($key, 'DB') !== false || strpos($key, 'RAILWAY') !== false) {
        // Obfuscate the value for safety in the screenshot
        $len = strlen($value);
        $masked = $len > 4 ? substr($value, 0, 4) . '...' : '****';
        echo "$key = $masked\n";
    }
}
echo "</pre>";

if (!$host) {
     // Don't die yet, let them see the list above
    echo "<h2 style='color:orange'>Warning: MYSQLHOST is empty. See list above for correct names.</h2>";
}

echo "<p>Attempting Connection...</p>";

try {
    $conn = new mysqli($host, $user, $pass, $name, $port);
    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }
    echo "<h2 style='color:green'>✅ Connection Successful!</h2>";
    echo "<p>Database '$name' is accessible.</p>";
    
    // Check tables
    $res = $conn->query("SHOW TABLES");
    echo "<h3>Existing Tables:</h3><ul>";
    while($row = $res->fetch_row()) {
        echo "<li>{$row[0]}</li>";
    }
    echo "</ul>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>❌ Connection Failed</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>
