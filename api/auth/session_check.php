<?php
// Strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// JSON Headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

session_start();

// 1. Check if logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401); // Unauthorized
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit();
}

// 2. Check Role (Optional but recommended security)
// Ensure only Role ID 3 (Pet Owner) can access this specific dashboard endpoint
if (isset($_SESSION['role_id']) && $_SESSION['role_id'] != 3) {
    http_response_code(403); // Forbidden
    echo json_encode(["status" => "error", "message" => "Access denied for this role"]);
    exit();
}

// 3. Return User Data (For the Frontend to display)
// In a real app, you might fetch the latest name from DB here, but Session is faster for now
$displayName = isset($_SESSION['username']) ? $_SESSION['username'] : "Pet Owner";

echo json_encode([
    "status" => "success",
    "user" => [
        "id" => $_SESSION['user_id'],
        "name" => $displayName,
        "role" => $_SESSION['role_id']
    ]
]);
?>