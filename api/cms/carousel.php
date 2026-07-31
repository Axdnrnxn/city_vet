<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
mysqli_report(MYSQLI_REPORT_OFF);
require_once '../../config/db_connection.php';

if ($conn instanceof mysqli) $conn->set_charset("utf8mb4");

function respond($payload, $code = 200) {
    http_response_code($code);
    echo json_encode($payload);
    exit();
}

function requireStaffOrAdmin() {
    if (!isset($_SESSION['user_id'], $_SESSION['role_id']) || !in_array((int)$_SESSION['role_id'], [1, 4], true)) {
        respond(["status" => "error", "message" => "Access denied."], 403);
    }
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $publicOnly = isset($_GET['public']);
    $where = $publicOnly ? "WHERE Status = 'active'" : "";
    if (!$publicOnly) requireStaffOrAdmin();

    $result = $conn->query("SELECT Carousel_ID, Title, Subtitle, Image_Path, Sort_Order, Status, Created_At FROM carousel_images $where ORDER BY Sort_Order ASC, Carousel_ID DESC");
    if (!$result) {
        if ($conn->errno === 1146 && $publicOnly) {
            respond(["status" => "success", "items" => []]);
        }
        respond(["status" => "error", "message" => "Carousel table is missing. Please run the CMS carousel migration."], 500);
    }
    $items = [];
    while ($row = $result->fetch_assoc()) $items[] = $row;
    respond(["status" => "success", "items" => $items]);
}

requireStaffOrAdmin();
$action = $_POST['action'] ?? '';

if ($method === 'POST' && $action === 'upload') {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);

    if ($title === '' || empty($_FILES['image']['name'])) {
        respond(["status" => "error", "message" => "Title and image are required."], 422);
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tmp = $_FILES['image']['tmp_name'];
    $mime = mime_content_type($tmp);
    if (!isset($allowed[$mime])) {
        respond(["status" => "error", "message" => "Only JPG, PNG, and WEBP images are allowed."], 422);
    }

    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        respond(["status" => "error", "message" => "Image must be 5MB or smaller."], 422);
    }

    $filename = uniqid('carousel_', true) . '.' . $allowed[$mime];
    $relativePath = 'uploads/carousel/' . $filename;
    $targetDir = dirname(__DIR__, 2) . '/uploads/carousel';
    $target = $targetDir . '/' . $filename;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (!move_uploaded_file($tmp, $target)) {
        respond(["status" => "error", "message" => "Unable to save uploaded image."], 500);
    }

    $createdBy = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO carousel_images (Title, Subtitle, Image_Path, Sort_Order, Created_By) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        @unlink($target);
        respond(["status" => "error", "message" => "Carousel table is missing. Please run the CMS carousel migration."], 500);
    }
    $stmt->bind_param("sssii", $title, $subtitle, $relativePath, $sortOrder, $createdBy);

    if (!$stmt->execute()) {
        @unlink($target);
        respond(["status" => "error", "message" => "Unable to save carousel item."], 500);
    }

    respond(["status" => "success", "message" => "Carousel image uploaded."]);
}

if ($method === 'POST' && $action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $status = ($_POST['status'] ?? '') === 'active' ? 'active' : 'inactive';
    $stmt = $conn->prepare("UPDATE carousel_images SET Status = ? WHERE Carousel_ID = ?");
    if (!$stmt) respond(["status" => "error", "message" => "Carousel table is missing. Please run the CMS carousel migration."], 500);
    $stmt->bind_param("si", $status, $id);
    respond($stmt->execute() ? ["status" => "success"] : ["status" => "error", "message" => "Unable to update item."]);
}

if ($method === 'POST' && $action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare("SELECT Image_Path FROM carousel_images WHERE Carousel_ID = ?");
    if (!$stmt) respond(["status" => "error", "message" => "Carousel table is missing. Please run the CMS carousel migration."], 500);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) respond(["status" => "error", "message" => "Item not found."], 404);

    $del = $conn->prepare("DELETE FROM carousel_images WHERE Carousel_ID = ?");
    $del->bind_param("i", $id);
    if ($del->execute()) {
        $file = dirname(__DIR__, 2) . '/' . $row['Image_Path'];
        if (is_file($file)) @unlink($file);
        respond(["status" => "success"]);
    }
    respond(["status" => "error", "message" => "Unable to delete item."], 500);
}

respond(["status" => "error", "message" => "Invalid request."], 400);
