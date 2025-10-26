<?php
// User Wallet stub - php-app/modules/user_wallet.php
require_once __DIR__ . '/../src/config.php';

function list_user_certificates($userId) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE student_email = (SELECT email FROM users WHERE id = ?)");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function share_certificate_link($certId, $expires_in_seconds = 3600) {
    // create a short-lived share token
    $token = bin2hex(random_bytes(16));
    $pdo = db();
    $stmt = $pdo->prepare("INSERT INTO shared_links (cert_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))");
    $stmt->execute([$certId, $token, $expires_in_seconds]);
    return $token;
}