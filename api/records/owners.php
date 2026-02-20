<?php
// File: api/records/owners.php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

// Security: Ensure Admin or Staff
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 4)) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Access Denied"]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// =========================================================
// 1. GET: Fetch All Owners (with Pet Counts)
// =========================================================
if ($method === 'GET') {
    // We join 'owners' with 'users' (to get Email) and 'pets' (to count them)
    $sql = "SELECT 
                o.Owner_ID, 
                o.First_name, 
                o.Last_name, 
                o.Contact_Number, 
                o.Address, 
                u.Email,
                o.Status,
                COUNT(p.Pet_ID) as Pet_Count
            FROM owners o
            JOIN users u ON o.User_ID = u.User_ID
            LEFT JOIN pets p ON o.Owner_ID = p.Owner_ID AND p.Status = 'active'
            WHERE o.Status = 'active'
            GROUP BY o.Owner_ID
            ORDER BY o.Last_name ASC";

    $result = $conn->query($sql);
    $owners = [];

    while ($row = $result->fetch_assoc()) {
        $owners[] = $row;
    }

    echo json_encode($owners);
    exit();
}

// =========================================================
// 2. POST: Handle Search (Optional, if doing server-side search)
// =========================================================
// Note: We can handle simple search on the frontend with JS for now.
?>