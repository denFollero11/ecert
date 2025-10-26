<?php
// Audit & Compliance stub - php-app/modules/audit_compliance.php
require_once __DIR__ . '/../src/config.php';

function record_audit_event($action, $details) {
    $pdo = db();
    $stmt = $pdo->prepare("INSERT INTO audit_logs (action, details) VALUES (?, ?)");
    $stmt->execute([$action, json_encode($details)]);
}

function get_audit_logs($limit = 200) {
    $pdo = db();
    $stmt = $pdo->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT " . (int)$limit);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}