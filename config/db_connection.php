<?php
// File: config/db_connection.php

// Check if we are running on XAMPP (localhost) or InfinityFree
$is_localhost = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1');

if ($is_localhost) {
    // -----------------------------------------
    // LOCAL XAMPP CREDENTIALS
    // -----------------------------------------
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "city_vet_db";
} else {
    // -----------------------------------------
    // LIVE INFINITYFREE CREDENTIALS
    // -----------------------------------------
    // You will get these details from your InfinityFree control panel
    // under the "MySQL Databases" section.
    
    $host = "sql107.infinityfree.com"; // e.g., sql102.infinityfree.com
    $username = "if0_42649367";        // e.g., if0_34567890
    $password = "Wwb9C4mFJfX";    // The password you use for hosting/FTP
    $database = "if0_42649367_city_vet_db"; // The database name you created
}

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}
?>