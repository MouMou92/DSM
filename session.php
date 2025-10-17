<?php
session_start();

$authorized = !empty($_SESSION['auth']);

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'authorized' => $authorized,
], JSON_UNESCAPED_UNICODE);

if (!$authorized) {
    http_response_code(401);
}
