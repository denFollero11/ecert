<?php
/**
 * Common utility functions for the E-Certificate system.
 * Safe fallback for older autoload references.
 */

// Redirect helper
function redirect($url) {
    header("Location: " . $url);
    exit();
}


// Check if logged-in user is admin
function isAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}



// Generate random token (for CSRF, reset links, etc.)
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}
