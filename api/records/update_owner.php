<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db_connection.php';
require_once '../system/audit_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || !in_array((int)($_SESSION['role_id'] ?? 0), [1, 4], true)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$ownerId = (int)($_POST['owner_id'] ?? 0);
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$contact = trim($_POST['contact_number'] ?? '');
$address = trim($_POST['address'] ?? '');
$email = trim($_POST['email'] ?? '');

if (!$ownerId || $firstName === '' || $lastName === '' || $contact === '' || $address === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide valid owner details.']);
    exit();
}

$owner = $conn->prepare('SELECT User_ID FROM owners WHERE Owner_ID = ? AND Status = \'active\'');
$owner->bind_param('i', $ownerId);
$owner->execute();
$row = $owner->get_result()->fetch_assoc();
if (!$row) { echo json_encode(['status' => 'error', 'message' => 'Owner not found.']); exit(); }
$userId = (int)$row['User_ID'];

$duplicate = $conn->prepare('SELECT User_ID FROM users WHERE Email = ? AND User_ID != ?');
$duplicate->bind_param('si', $email, $userId);
$duplicate->execute();
if ($duplicate->get_result()->num_rows) { echo json_encode(['status' => 'error', 'message' => 'That email address is already in use.']); exit(); }

$conn->begin_transaction();
try {
    $stmt = $conn->prepare('UPDATE owners SET First_name = ?, Last_name = ?, Contact_number = ?, Address = ? WHERE Owner_ID = ?');
    $stmt->bind_param('ssssi', $firstName, $lastName, $contact, $address, $ownerId);
    $stmt->execute();
    $stmt = $conn->prepare('UPDATE users SET Email = ? WHERE User_ID = ?');
    $stmt->bind_param('si', $email, $userId);
    $stmt->execute();
    $conn->commit();
    auditLog($conn, (int)$_SESSION['user_id'], 'Update Owner Details', 'owners', $ownerId);
    echo json_encode(['status' => 'success', 'message' => 'Owner details updated.']);
} catch (Throwable $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Unable to update owner details.']);
}
?>
