<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Token timeout in seconds (30 minutes)
define('CSRF_TOKEN_TIMEOUT', 1800);

// Generate CSRF token, store with timestamp, and return it
function generateCsrfToken() {
    // Only generate a new token if none exists or it's invalid
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_TIMEOUT)) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token (check existence, match, and timeout)
function validateCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || empty($token)) {
        // Debug: Log missing token
        // error_log("CSRF validation failed: Missing session token or submitted token");
        return false;
    }
    // Check if token has expired
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_TIMEOUT) {
        // Debug: Log expired token
        // error_log("CSRF validation failed: Token expired");
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        return false;
    }
    // Verify token match
    $valid = hash_equals($_SESSION['csrf_token'], $token);
    // Debug: Log token mismatch
    // if (!$valid) error_log("CSRF validation failed: Token mismatch. Session: {$_SESSION['csrf_token']}, Submitted: $token");
    return $valid;
}

// Regenerate CSRF token after successful action
function regenerateCsrfToken() {
    unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
    return generateCsrfToken();
}
?>