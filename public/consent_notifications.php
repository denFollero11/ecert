<?php
// Consent & Notifications stub - php-app/modules/consent_notifications.php
require_once __DIR__ . '/../src/config.php';

function request_consent($userEmail, $certId) {
    $pdo = db();
    $token = bin2hex(random_bytes(16));
    $stmt = $pdo->prepare("INSERT INTO consent_requests (email, cert_id, token, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$userEmail, $certId, $token]);
    // send email here (stub)
    return $token;
}

function check_consent($token) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM consent_requests WHERE token = ? LIMIT 1");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}