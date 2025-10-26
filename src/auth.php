<?php
require_once __DIR__.'/config.php';

function passwordHash($pw) {
    return password_hash($pw, PASSWORD_DEFAULT);
}

function verifyPassword($pw, $hash) {
    return password_verify($pw, $hash);
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}
function requireRole($role) {
    if (!isLoggedIn() || $_SESSION['user']['role'] !== $role) {
        header('Location: /login.php');
        exit;
    }
}
function currentUser() {
    return $_SESSION['user'] ?? null;
}