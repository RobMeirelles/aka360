<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'jajablabla123');
define('DB_NAME', 'akademia360new');

// Create database connection using mysqli
function getDBConnection() {
    try {
        // Connect to MySQL server
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        if ($mysqli->connect_error) {
            throw new Exception("Error de conexión: " . $mysqli->connect_error);
        }
        
        // Set charset
        $mysqli->set_charset("utf8");
        
        // Create database if it doesn't exist
        $mysqli->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Select the database
        $mysqli->select_db(DB_NAME);
        
        return $mysqli;
    } catch(Exception $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

?>