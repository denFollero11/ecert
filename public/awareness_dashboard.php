<?php
// Awareness Dashboard stub - php-app/modules/awareness_dashboard.php
require_once __DIR__ . '/../src/config.php';

function get_adoption_metrics() {
    $pdo = db();
    $totalIssued = $pdo->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
    $totalVerified = $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE action = 'verify'")->fetchColumn();
    return ['issued' => (int)$totalIssued, 'verified' => (int)$totalVerified];
}