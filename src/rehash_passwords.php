<?php
require_once __DIR__ . '/config.php';
$pdo = db();

// 1️⃣ Fetch all users
$stmt = $pdo->query("SELECT id, username, password_hash FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2️⃣ Loop through users and hash plain-text passwords
foreach ($users as $u) {
    $pw = $u['password_hash'];
    
    // Skip if it's already hashed
    if (preg_match('/^\$2y\$/', $pw)) {
        echo "Skipping {$u['username']} (already hashed)\n";
        continue;
    }

    // Hash the plain password
    $newHash = password_hash($pw, PASSWORD_DEFAULT);
    $update = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
    $update->execute([$newHash, $u['id']]);
    echo "✅ Updated {$u['username']} to hashed password.\n";
}

echo "Done.";
