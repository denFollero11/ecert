<?php
// DID Module stub - php-app/modules/did_module.php
// Purpose: register and manage decentralized identifiers (DIDs) for users.
// NOTE: This is a stub. Replace Fabric CA calls with your SDK / API calls.

require_once __DIR__ . '/../src/config.php';

function create_user_did($username) {
    $pdo = db();
    $did = 'did:fabric:' . hash('sha256', $username . microtime(true));
    $stmt = $pdo->prepare("UPDATE users SET did = ? WHERE username = ?");
    $stmt->execute([$did, $username]);
    return $did;
}

function get_user_did($username) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT did FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    return $stmt->fetchColumn();
}