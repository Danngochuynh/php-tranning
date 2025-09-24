<?php

if (session_status() === PHP_SESSION_NONE) {
    // Thiết lập cookie params trước khi session_start
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'); // true nếu HTTPS
    // PHP 7.3+ supports samesite via session_set_cookie_params options array
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,           // phiên cookie
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'        // Lax or Strict
        ]);
    } else {
        // fallback: set samesite via header (less ideal)
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', $secure ? 1 : 0);
        ini_set('session.cookie_lifetime', 0);
        // For older PHP you may need to set cookie manually
    }
    session_start();
}

// generate a token if not exists
function csrf_get_token(): string {
    if (empty($_SESSION['_csrf_token'])) {
        // 32 bytes random -> 64 hex chars
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

// insert hidden input for forms
function csrf_input_field(): string {
    $t = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="_csrf_token" value="' . $t . '">';
}

// verify token on POST (throws / returns false)
function csrf_validate_request(): bool {
    // Only validate for state-changing methods
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (in_array($method, ['POST','PUT','PATCH','DELETE'])) {
        // Try header first (for AJAX): X-CSRF-Token
        $headerToken = null;
        foreach (getallheaders() as $k => $v) {
            if (strtolower($k) === 'x-csrf-token') {
                $headerToken = $v;
                break;
            }
        }
        $provided = $headerToken !== null ? $headerToken : ($_POST['_csrf_token'] ?? $_REQUEST['_csrf_token'] ?? null);
        $sessionToken = $_SESSION['_csrf_token'] ?? null;
        // timing-safe compare
        if (is_string($provided) && is_string($sessionToken) && hash_equals($sessionToken, $provided)) {
            return true;
        }
        // Optionally: check Origin or Referer as extra check
        return false;
    }
    return true; // GET requests pass
}
