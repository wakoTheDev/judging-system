<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); 
define('DB_NAME', 'judging_system');

/**
 * Get database connection
 * @return mysqli Database connection object
 */
function getDatabaseConnection() {
    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }
    
    // Set charset to prevent encoding issues
    $connection->set_charset("utf8");
    
    return $connection;
}

/**
 * Close database connection
 * @param mysqli $connection Database connection to close
 */
function closeDatabaseConnection($connection) {
    if ($connection) {
        $connection->close();
    }
}

/**
 * Execute prepared statement with error handling
 * @param mysqli $conn Database connection
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters for prepared statement
 * @param string $types Parameter types string
 * @return array|bool Query result or false on error
 */
function executeQuery($conn, $sql, $params = [], $types = '') {
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result;
}
?>