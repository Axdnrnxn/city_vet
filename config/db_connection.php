<?php
// File: config/db_connection.php
$host = "localhost";
$username = "root";
$password = "";
$database = "city_vet_db";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    // Return JSON error if connection fails
    die(json_encode(["status" => "error", "message" => "Connection Failed: " . $conn->connect_error]));
}
?>