<?php
session_start();
header("Content-Type: application/json");

include_once '../../config/db_connection.php';

if (!$conn) {
    die(json_encode(["error" => "Database connection variable not found"]));
}

function writeAuditLog($conn, $userId, $action, $tableAffected, $recordId = 0) {
    if (!$userId) return;

    $stmt = $conn->prepare("INSERT INTO audit_logs (User_ID, Action, Table_Affected, Record_ID) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issi", $userId, $action, $tableAffected, $recordId);
        $stmt->execute();
        $stmt->close();
    }
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
        echo json_encode(["status" => "error", "message" => "Access denied. Administrators only."]);
        exit();
    }

    $query = "SELECT
                u.User_ID,
                u.Username,
                u.Email,
                u.Phone_number,
                u.Status,
                u.Role_ID
              FROM users u
              ORDER BY u.Created_At DESC";

    $result = $conn->query($query);
    $users = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }

    echo json_encode($users);
    exit();
}

if ($method !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));
$action = $data->action ?? '';
$actorId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if ($action === 'update_credentials') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
        echo json_encode(["status" => "error", "message" => "Access denied. Administrators only."]);
        exit();
    }

    $email = $data->email ?? '';
    $id = (int)($data->staff_id ?? 0);

    if (!$id || empty($email)) {
        echo json_encode(["status" => "error", "message" => "Missing user details."]);
        exit();
    }

    if (!empty($data->password)) {
        $pass_hash = password_hash($data->password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET Email = ?, Password_Hash = ? WHERE User_ID = ?");
        $stmt->bind_param("ssi", $email, $pass_hash, $id);
        $logAction = "Update User Email and Password";
    } else {
        $stmt = $conn->prepare("UPDATE users SET Email = ? WHERE User_ID = ?");
        $stmt->bind_param("si", $email, $id);
        $logAction = "Update User Email";
    }

    if ($stmt->execute()) {
        writeAuditLog($conn, $actorId, $logAction, "users", $id);
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    exit();
}

if ($action === 'toggle_status') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
        echo json_encode(["status" => "error", "message" => "Access denied. Administrators only."]);
        exit();
    }

    $id = (int)($data->staff_id ?? 0);
    $currentStatus = $data->current_status ?? '';
    $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';

    if (!$id) {
        echo json_encode(["status" => "error", "message" => "Missing user ID."]);
        exit();
    }

    $stmt = $conn->prepare("UPDATE users SET Status = ? WHERE User_ID = ?");
    $stmt->bind_param("si", $newStatus, $id);

    if ($stmt->execute()) {
        $logAction = ($newStatus === 'active') ? "Activate User Account" : "Deactivate User Account";
        writeAuditLog($conn, $actorId, $logAction, "users", $id);
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    exit();
}

if ($action === 'update_profile') {
    $userId = $actorId;

    if (!$userId) {
        echo json_encode(["status" => "error", "message" => "Unauthorized session."]);
        exit();
    }

    $currentPasswordInput = $data->current_password ?? '';
    $username = $data->username ?? '';
    $email = $data->email ?? '';
    $newPassword = $data->password ?? '';

    $verify_stmt = $conn->prepare("SELECT Password_Hash FROM users WHERE User_ID = ?");
    $verify_stmt->bind_param("i", $userId);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    $user_data = $result->fetch_assoc();

    if (!$user_data || !password_verify($currentPasswordInput, $user_data['Password_Hash'])) {
        echo json_encode(["status" => "error", "message" => "Verification failed. Incorrect current password."]);
        exit();
    }

    if (!empty($newPassword)) {
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET Username = ?, Email = ?, Password_Hash = ? WHERE User_ID = ?");
        $stmt->bind_param("sssi", $username, $email, $newHash, $userId);
        $logAction = "Update Profile and Password";
    } else {
        $stmt = $conn->prepare("UPDATE users SET Username = ?, Email = ? WHERE User_ID = ?");
        $stmt->bind_param("ssi", $username, $email, $userId);
        $logAction = "Update Profile";
    }

    $conn->begin_transaction();

    try {
        if (!$stmt->execute()) {
            throw new Exception("Update failed.");
        }

        $update_owner = $conn->prepare("UPDATE owners SET First_name = ? WHERE User_ID = ?");
        if ($update_owner) {
            $update_owner->bind_param("si", $username, $userId);
            if (!$update_owner->execute()) {
                throw new Exception("Owner profile sync failed.");
            }
            $update_owner->close();
        }

        writeAuditLog($conn, $userId, $logAction, "users", $userId);
        $conn->commit();

        $_SESSION['username'] = $username;
        echo json_encode(["status" => "success", "message" => "Profile updated successfully"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Update failed."]);
    }
    exit();
}

echo json_encode(["status" => "error", "message" => "Unknown action."]);
?>
