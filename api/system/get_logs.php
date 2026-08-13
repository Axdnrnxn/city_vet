<?php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php';
require_once 'audit_helper.php';

// Only Admin (Role 1) can view logs
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    auditLog($conn, $_SESSION['user_id'] ?? null, 'Denied Audit Log Access', 'audit_logs', 0, ['event_type' => 'security', 'outcome' => 'denied']);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

// Server-side pagination and filters keep this page usable as the audit table grows.
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(10, (int)($_GET['limit'] ?? 50)));
$offset = ($page - 1) * $limit;
$where = [];

foreach (['actor' => 'u.Username', 'action' => 'l.Action', 'entity' => 'l.Table_Affected', 'outcome' => 'l.Outcome'] as $input => $column) {
    $value = trim($_GET[$input] ?? '');
    if ($value !== '') $where[] = "$column LIKE '%" . $conn->real_escape_string($value) . "%'";
}

auditLog($conn, (int)$_SESSION['user_id'], 'View Audit Logs', 'audit_logs', 0, [
    'event_type' => 'security',
    'metadata' => ['page' => max(1, (int)($_GET['page'] ?? 1))]
]);
$recordId = (int)($_GET['record_id'] ?? 0);
if ($recordId > 0) $where[] = "l.Record_ID = $recordId";
foreach (['from' => '>=', 'to' => '<='] as $input => $operator) {
    $date = $_GET[$input] ?? '';
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $boundary = $input === 'to' ? "$date 23:59:59" : "$date 00:00:00";
        $where[] = "l.Timestamp $operator '" . $conn->real_escape_string($boundary) . "'";
    }
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countResult = $conn->query("SELECT COUNT(*) AS total FROM audit_logs l LEFT JOIN users u ON l.User_ID = u.User_ID $whereSql");
$total = $countResult ? (int)$countResult->fetch_assoc()['total'] : 0;

$sql = "SELECT l.Log_ID, l.Action, l.Event_Type, l.Table_Affected, l.Record_ID, l.Outcome, l.Request_Method, l.Endpoint, l.IP_Address, l.Metadata, l.Timestamp,
               COALESCE(u.Username, 'System/Unknown') AS Username
        FROM audit_logs l
        LEFT JOIN users u ON l.User_ID = u.User_ID
        $whereSql
        ORDER BY l.Timestamp DESC, l.Log_ID DESC
        LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);
$logs = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}

echo json_encode([
    'status' => 'success',
    'data' => $logs,
    'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'pages' => max(1, (int)ceil($total / $limit))]
]);
?>
