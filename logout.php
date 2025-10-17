<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => 'method_not_allowed',
        'message' => 'Use POST to end the session.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
