<?php
// Strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// JSON Headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

session_start();

// 1. Check if logged in at all
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401); // Unauthorized
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit();
}

// 2. Dynamic Dashboard Security (The Magic Part)
// We look at the URL of the dashboard making the request
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

$required_role = null;

// Determine which role is allowed based on the folder name in the URL
if (strpos($referer, '/admin/') !== false) {
    $required_role = 1; // 1 = Administrator
} elseif (strpos($referer, '/vet/') !== false) {
    $required_role = 2; // 2 = Veterinarian
} elseif (strpos($referer, '/owner/') !== false) {
    $required_role = 3; // 3 = Pet Owner
} elseif (strpos($referer, '/staff/') !== false) {
    $required_role = 4; // 4 = Staff
}

// 3. Kick them out if their role doesn't match the dashboard they are trying to view
if ($required_role !== null && isset($_SESSION['role_id']) && $_SESSION['role_id'] != $required_role) {
    http_response_code(403); // Forbidden
    echo json_encode(["status" => "error", "message" => "Access denied. You do not have permission for this dashboard."]);
    exit();
}

// 4. Return User Data (For the Frontend to display)
$displayName = isset($_SESSION['username']) ? $_SESSION['username'] : "User";

echo json_encode([
    "status" => "success",
    "user" => [
        "id" => $_SESSION['user_id'],
        "name" => $displayName,
        "role" => $_SESSION['role_id']
    ]
]);
?>