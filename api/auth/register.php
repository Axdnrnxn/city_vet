<?php
// strict error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON (standard for API communication)
header("Content-Type: application/json; charset=UTF-8");

// Handle CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

// Include database connection
$configPath = '../config/db_connection.php'; 
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server Configuration Error"]);
    exit();
}
require_once $configPath;

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);
if (is_null($data)) {
    $data = $_POST;
}

// 1. Sanitize and Validate Inputs
$username = isset($data['username']) ? trim($conn->real_escape_string($data['username'])) : '';
$email = isset($data['email']) ? trim($conn->real_escape_string($data['email'])) : '';
$password = isset($data['password']) ? trim($data['password']) : '';
$firstName = isset($data['first_name']) ? trim($conn->real_escape_string($data['first_name'])) : '';
$lastName = isset($data['last_name']) ? trim($conn->real_escape_string($data['last_name'])) : '';
$contact = isset($data['contact_number']) ? trim($conn->real_escape_string($data['contact_number'])) : '';

// Basic validation check
if (empty($username) || empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data. Please fill all required fields."]);
    exit();
}

// 2. Check for Duplicates (The "Better" Logic)
// Use a prepared statement to prevent injection and check exact matches
$checkSql = "SELECT User_ID, Username, Email FROM Users WHERE Username = ? OR Email = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("ss", $username, $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    $existing = $checkResult->fetch_assoc();
    $errorMsg = "Account already exists.";
    
    // Check exactly which field caused the conflict
    if ($existing['Username'] === $username) {
        $errorMsg = "This Username is already taken.";
    } elseif ($existing['Email'] === $email) {
        $errorMsg = "This Email is already registered.";
    }

    http_response_code(409); // Conflict status
    echo json_encode(["status" => "error", "message" => $errorMsg]);
    exit();
}
$checkStmt->close();

// 3. Begin Transaction
$conn->begin_transaction();

try {
    // 3a. Insert User (With BCRYPT Hashing)
    $roleId = 3; // UPDATED: Role ID 3 = Pet Owner
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $userSql = "INSERT INTO Users (Role_ID, Username, Password_Hash, Email, Phone_number) VALUES (?, ?, ?, ?, ?)";
    $stmtUser = $conn->prepare($userSql);
    $stmtUser->bind_param("issss", $roleId, $username, $passwordHash, $email, $contact);
    
    if (!$stmtUser->execute()) {
        throw new Exception("Error registering user account: " . $stmtUser->error);
    }
    
    // Get the ID of the user we just created
    $newUserId = $conn->insert_id;
    $stmtUser->close();

    // 3b. Insert Owner Profile
    $ownerSql = "INSERT INTO Owners (User_ID, First_name, Last_name, Contact_Number, Registration_Date) VALUES (?, ?, ?, ?, NOW())";
    $stmtOwner = $conn->prepare($ownerSql);
    $stmtOwner->bind_param("isss", $newUserId, $firstName, $lastName, $contact);
    
    if (!$stmtOwner->execute()) {
        throw new Exception("Error creating owner profile: " . $stmtOwner->error);
    }
    $stmtOwner->close();

    // 4. Commit Transaction
    $conn->commit();

    http_response_code(201); // Created
    echo json_encode(["status" => "success", "message" => "Account created successfully!"]);

} catch (Exception $e) {
    // Rollback if anything failed
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>