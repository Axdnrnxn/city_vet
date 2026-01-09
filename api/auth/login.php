<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON and CORS
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

// Start Session (Required for login)
session_start();

// Include DB Connection
$configPath = '../config/db_connection.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Configuration missing."]);
    exit();
}
require_once $configPath;

// Get POST Data
$data = json_decode(file_get_contents("php://input"), true);
if (is_null($data)) {
    $data = $_POST;
}

// 1. Validate Inputs
$email = isset($data['email']) ? trim($conn->real_escape_string($data['email'])) : '';
$password = isset($data['password']) ? trim($data['password']) : '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Please provide both email and password."]);
    exit();
}

// 2. Query User
// We only select necessary fields. 
// Note: We select User_ID, Role_ID, Password_Hash, and Username
$sql = "SELECT User_ID, Role_ID, Username, Password_Hash FROM Users WHERE Email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// 3. Verify User Exists
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // 4. Verify Password (Hash Comparison)
    if (password_verify($password, $user['Password_Hash'])) {
        
        // 5. Success! Set Session Variables
        $_SESSION['user_id'] = $user['User_ID'];
        $_SESSION['role_id'] = $user['Role_ID'];
        $_SESSION['username'] = $user['Username'];
        $_SESSION['logged_in'] = true;

        http_response_code(200);
        echo json_encode([
            "status" => "success", 
            "message" => "Login successful",
            "role" => $user['Role_ID'] // Useful for frontend redirection if you have multiple dashboards
        ]);
        
    } else {
        // Password mismatch
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
    }

} else {
    // Email not found
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
}

$stmt->close();
$conn->close();
?>