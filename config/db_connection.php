<?php
// File: config/db_connection.php
$host = "localhost";
$username = "root";
$password = "";
$database = "city_vet_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}
?>