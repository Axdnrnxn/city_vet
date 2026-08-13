<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
mysqli_report(MYSQLI_REPORT_OFF);
require_once '../../config/db_connection.php';
require_once '../system/audit_helper.php';

if ($conn instanceof mysqli) $conn->set_charset('utf8mb4');

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

function ensureHomepagePostsTable($conn) {
    $sql = "
        CREATE TABLE IF NOT EXISTS homepage_posts (
            Post_ID INT NOT NULL AUTO_INCREMENT,
            Section ENUM('highlight','announcement') NOT NULL DEFAULT 'announcement',
            Title VARCHAR(150) NOT NULL,
            Subtitle VARCHAR(255) DEFAULT NULL,
            Badge VARCHAR(60) DEFAULT NULL,
            Image_Path VARCHAR(255) DEFAULT NULL,
            Sort_Order INT NOT NULL DEFAULT 0,
            Status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            Created_By INT DEFAULT NULL,
            Created_At DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (Post_ID),
            KEY Section (Section),
            KEY Created_By (Created_By),
            CONSTRAINT homepage_posts_created_by_fk FOREIGN KEY (Created_By) REFERENCES users(User_ID) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";

    if (!$conn->query($sql)) {
        respond(["status" => "error", "message" => "Unable to initialize homepage posts table."], 500);
    }

    $columnsResult = $conn->query("SHOW COLUMNS FROM homepage_posts");
    $columns = [];
    while ($row = $columnsResult->fetch_assoc()) {
        $columns[] = $row['Field'];
    }

    $alterStatements = [];
    if (!in_array('Section', $columns, true)) {
        $alterStatements[] = "ADD COLUMN Section ENUM('highlight','announcement') NOT NULL DEFAULT 'announcement' AFTER Post_ID";
    }
    if (!in_array('Badge', $columns, true)) {
        $alterStatements[] = "ADD COLUMN Badge VARCHAR(60) DEFAULT NULL AFTER Subtitle";
    }
    if (!in_array('Image_Path', $columns, true)) {
        $alterStatements[] = "ADD COLUMN Image_Path VARCHAR(255) DEFAULT NULL AFTER Badge";
    }

    if ($alterStatements) {
        $alterSql = "ALTER TABLE homepage_posts " . implode(', ', $alterStatements) . ";";
        if (!$conn->query($alterSql)) {
            respond(["status" => "error", "message" => "Unable to upgrade homepage posts table for new section support."], 500);
        }
    }
}

$method = $_SERVER['REQUEST_METHOD'];
ensureHomepagePostsTable($conn);

if ($method === 'GET') {
    $publicOnly = isset($_GET['public']);
    $section = strtolower(trim($_GET['section'] ?? ''));
    $where = [];
    if ($publicOnly) {
        $where[] = "Status = 'active'";
    }
    if ($section !== '') {
        $where[] = "Section = '" . $conn->real_escape_string($section) . "'";
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    if (!$publicOnly) requireStaffOrAdmin();

    $result = $conn->query("SELECT Post_ID, Section, Title, Subtitle, Badge, Image_Path, Sort_Order, Status, Created_At FROM homepage_posts $whereSql ORDER BY Sort_Order DESC, Post_ID DESC");
    if (!$result) {
        respond(["status" => "error", "message" => "Unable to load homepage posts."], 500);
    }

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    respond(["status" => "success", "items" => $items]);
}

requireStaffOrAdmin();
$action = $_POST['action'] ?? '';

if ($method === 'POST' && $action === 'upload') {
    $section = strtolower(trim($_POST['section'] ?? 'announcement'));
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $badge = trim($_POST['badge'] ?? 'Announcement');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);

    if (!in_array($section, ['highlight', 'announcement'], true)) {
        respond(["status" => "error", "message" => "Invalid homepage section."], 422);
    }

    if ($title === '') {
        respond(["status" => "error", "message" => "Title is required."], 422);
    }

    $relativePath = null;
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $tmp = $_FILES['image']['tmp_name'];
        $mime = mime_content_type($tmp);

        if (!isset($allowed[$mime])) {
            respond(["status" => "error", "message" => "Only JPG, PNG, and WEBP images are allowed."], 422);
        }

        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            respond(["status" => "error", "message" => "Image must be 5MB or smaller."], 422);
        }

        $filename = uniqid('post_', true) . '.' . $allowed[$mime];
        $relativePath = 'uploads/posts/' . $filename;
        $targetDir = dirname(__DIR__, 2) . '/uploads/posts';
        $target = $targetDir . '/' . $filename;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (!move_uploaded_file($tmp, $target)) {
            respond(["status" => "error", "message" => "Unable to save uploaded image."], 500);
        }
    }

    $createdBy = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO homepage_posts (Section, Title, Subtitle, Badge, Image_Path, Sort_Order, Created_By) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        if ($relativePath && is_file(dirname(__DIR__, 2) . '/' . $relativePath)) {
            @unlink(dirname(__DIR__, 2) . '/' . $relativePath);
        }
        respond(["status" => "error", "message" => "Unable to save homepage post."], 500);
    }

    $stmt->bind_param("sssssii", $section, $title, $subtitle, $badge, $relativePath, $sortOrder, $createdBy);
    if (!$stmt->execute()) {
        if ($relativePath && is_file(dirname(__DIR__, 2) . '/' . $relativePath)) {
            @unlink(dirname(__DIR__, 2) . '/' . $relativePath);
        }
        respond(["status" => "error", "message" => "Unable to save homepage post."], 500);
    }

    auditLog($conn, $createdBy, 'Create Homepage Post', 'homepage_posts', (int)$conn->insert_id, [
        'metadata' => ['section' => $section, 'status' => 'active']
    ]);
    respond(["status" => "success", "message" => "Homepage post published."]);
}

if ($method === 'POST' && $action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $status = ($_POST['status'] ?? '') === 'active' ? 'active' : 'inactive';
    $stmt = $conn->prepare("UPDATE homepage_posts SET Status = ? WHERE Post_ID = ?");
    if (!$stmt) respond(["status" => "error", "message" => "Unable to update homepage post."], 500);
    $stmt->bind_param("si", $status, $id);
    if ($stmt->execute()) {
        auditLog($conn, (int)$_SESSION['user_id'], 'Update Homepage Post Status', 'homepage_posts', $id, [
            'metadata' => ['status' => $status]
        ]);
        respond(["status" => "success"]);
    }
    respond(["status" => "error", "message" => "Unable to update homepage post."]);
}

if ($method === 'POST' && $action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare("SELECT Image_Path FROM homepage_posts WHERE Post_ID = ?");
    if (!$stmt) respond(["status" => "error", "message" => "Unable to delete homepage post."], 500);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) respond(["status" => "error", "message" => "Post not found."], 404);

    $del = $conn->prepare("DELETE FROM homepage_posts WHERE Post_ID = ?");
    $del->bind_param("i", $id);
    if ($del->execute()) {
        $file = dirname(__DIR__, 2) . '/' . $row['Image_Path'];
        if ($row['Image_Path'] && is_file($file)) @unlink($file);
        auditLog($conn, (int)$_SESSION['user_id'], 'Delete Homepage Post', 'homepage_posts', $id);
        respond(["status" => "success"]);
    }

    respond(["status" => "error", "message" => "Unable to delete homepage post."], 500);
}

respond(["status" => "error", "message" => "Invalid request."], 400);
