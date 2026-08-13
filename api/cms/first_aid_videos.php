<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');
mysqli_report(MYSQLI_REPORT_OFF);
require_once '../../config/db_connection.php';

if ($conn instanceof mysqli) {
    $conn->set_charset('utf8mb4');
}

function respond($payload, $code = 200) {
    http_response_code($code);
    echo json_encode($payload);
    exit();
}

function requireStaffOrAdmin() {
    if (!isset($_SESSION['user_id'], $_SESSION['role_id']) || !in_array((int)$_SESSION['role_id'], [1, 4], true)) {
        respond(['status' => 'error', 'message' => 'Access denied.'], 403);
    }
}

function normalizeYouTubeEmbedUrl($rawUrl) {
    $url = trim((string)$rawUrl);
    if ($url === '') {
        return '';
    }

    $parsed = parse_url($url);
    $host = strtolower($parsed['host'] ?? '');
    $path = strtolower($parsed['path'] ?? '');

    if (strpos($host, 'youtube.com') !== false || strpos($host, 'www.youtube.com') !== false || strpos($host, 'youtu.be') !== false) {
        $videoId = '';

        if (strpos($host, 'youtu.be') !== false) {
            $videoId = trim($path, '/');
        } else {
            parse_str($parsed['query'] ?? '', $query);
            $videoId = $query['v'] ?? '';
        }

        if ($videoId !== '') {
            return 'https://www.youtube.com/embed/' . rawurlencode($videoId);
        }
    }

    if (strpos($url, 'youtube.com/embed/') !== false || strpos($url, 'www.youtube.com/embed/') !== false) {
        return $url;
    }

    return '';
}

function ensureFirstAidVideosTable($conn) {
    $sql = "
        CREATE TABLE IF NOT EXISTS first_aid_videos (
            Video_ID INT NOT NULL AUTO_INCREMENT,
            Title VARCHAR(150) NOT NULL,
            Description VARCHAR(255) DEFAULT NULL,
            Embed_Url VARCHAR(500) NOT NULL,
            Tags VARCHAR(255) DEFAULT NULL,
            Sort_Order INT NOT NULL DEFAULT 0,
            Status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            Created_By INT DEFAULT NULL,
            Created_At DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (Video_ID),
            KEY Status (Status),
            KEY Created_By (Created_By),
            CONSTRAINT first_aid_videos_created_by_fk FOREIGN KEY (Created_By) REFERENCES users(User_ID) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";

    if (!$conn->query($sql)) {
        respond(['status' => 'error', 'message' => 'Unable to initialize first aid videos table.'], 500);
    }
}

$method = $_SERVER['REQUEST_METHOD'];
// include audit helper if available
if (file_exists(dirname(__DIR__) . '/system/audit_helper.php')) require_once dirname(__DIR__) . '/system/audit_helper.php';
ensureFirstAidVideosTable($conn);

if ($method === 'GET') {
    $publicOnly = isset($_GET['public']);
    $where = $publicOnly ? "WHERE Status = 'active'" : '';
    if (!$publicOnly) {
        requireStaffOrAdmin();
    }

    $result = $conn->query("SELECT Video_ID, Title, Description, Embed_Url, Tags, Sort_Order, Status, Created_At FROM first_aid_videos $where ORDER BY Sort_Order DESC, Video_ID DESC");
    if (!$result) {
        respond(['status' => 'error', 'message' => 'Unable to load first aid videos.'], 500);
    }

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    respond(['status' => 'success', 'items' => $items]);
}

requireStaffOrAdmin();
$action = $_POST['action'] ?? '';

if ($method === 'POST' && $action === 'upload') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $youtubeUrl = trim($_POST['youtube_url'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);

    if ($title === '') {
        respond(['status' => 'error', 'message' => 'Title is required.'], 422);
    }

    $embedUrl = normalizeYouTubeEmbedUrl($youtubeUrl);
    if ($embedUrl === '') {
        respond(['status' => 'error', 'message' => 'Please paste a valid YouTube watch or embed URL.'], 422);
    }

    $createdBy = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare('INSERT INTO first_aid_videos (Title, Description, Embed_Url, Tags, Sort_Order, Created_By) VALUES (?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        respond(['status' => 'error', 'message' => 'Unable to save first aid video.'], 500);
    }

    $stmt->bind_param('ssssii', $title, $description, $embedUrl, $tags, $sortOrder, $createdBy);
    if (!$stmt->execute()) {
        respond(['status' => 'error', 'message' => 'Unable to save first aid video.'], 500);
    }

    $insertId = $conn->insert_id;
    if (function_exists('auditLog')) auditLog($conn, $createdBy, 'Create First Aid Video', 'first_aid_videos', $insertId);

    respond(['status' => 'success', 'message' => 'First aid video saved.']);
}

if ($method === 'POST' && $action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $status = ($_POST['status'] ?? '') === 'active' ? 'active' : 'inactive';
    $stmt = $conn->prepare('UPDATE first_aid_videos SET Status = ? WHERE Video_ID = ?');
    if (!$stmt) {
        respond(['status' => 'error', 'message' => 'Unable to update first aid video.'], 500);
    }

    $stmt->bind_param('si', $status, $id);
    $ok = $stmt->execute();
    if ($ok && function_exists('auditLog')) auditLog($conn, (int)$_SESSION['user_id'], "Toggle First Aid Video Status to {$status}", 'first_aid_videos', $id);
    respond($ok ? ['status' => 'success'] : ['status' => 'error', 'message' => 'Unable to update first aid video.']);
}

if ($method === 'POST' && $action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare('DELETE FROM first_aid_videos WHERE Video_ID = ?');
    if (!$stmt) {
        respond(['status' => 'error', 'message' => 'Unable to delete first aid video.'], 500);
    }

    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    if ($ok && function_exists('auditLog')) auditLog($conn, (int)$_SESSION['user_id'], 'Delete First Aid Video', 'first_aid_videos', $id);
    respond($ok ? ['status' => 'success'] : ['status' => 'error', 'message' => 'Unable to delete first aid video.']);
}

respond(['status' => 'error', 'message' => 'Invalid request.'], 400);
