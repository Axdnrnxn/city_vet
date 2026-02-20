<?php
// File: api/auth/session_status.php
session_start();
header("Content-Type: application/json");

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "logged_in",
        "user_id" => $_SESSION['user_id'],
        "role_id" => $_SESSION['role_id'],
        "username" => $_SESSION['username']
    ]);
} else {
    echo json_encode(["status" => "logged_out"]);
}
?>