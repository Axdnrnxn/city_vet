<?php
/**
 * Writes a security/audit event. Metadata must not contain passwords, password
 * hashes, tokens, or full clinical notes. This function intentionally accepts a
 * null actor for system and pre-login events (for example, failed login).
 */
function auditLog($conn, $userId, $action, $tableAffected, $recordId = 0, array $options = []) {
    if (!($conn instanceof mysqli)) return false;

    $metadata = $options['metadata'] ?? null;
    if (is_array($metadata)) {
        unset($metadata['password'], $metadata['Password_Hash'], $metadata['token'], $metadata['authorization']);
        $metadata = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $eventType = $options['event_type'] ?? 'data_change';
    $outcome = $options['outcome'] ?? 'success';
    $method = $options['method'] ?? ($_SERVER['REQUEST_METHOD'] ?? null);
    $endpoint = $options['endpoint'] ?? ($_SERVER['SCRIPT_NAME'] ?? null);
    $ipAddress = $options['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? null);
    $sessionId = $options['session_id'] ?? (session_status() === PHP_SESSION_ACTIVE ? session_id() : null);
    $actorId = $userId ? (int)$userId : null;
    $entityId = $recordId ? (int)$recordId : null;

    $stmt = $conn->prepare(
        "INSERT INTO audit_logs
         (User_ID, Action, Event_Type, Table_Affected, Record_ID, Outcome, Request_Method, Endpoint, IP_Address, Session_ID, Metadata)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmt) return false;

    $stmt->bind_param(
        'isssissssss',
        $actorId, $action, $eventType, $tableAffected, $entityId, $outcome,
        $method, $endpoint, $ipAddress, $sessionId, $metadata
    );
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
