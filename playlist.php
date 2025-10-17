<?php
session_start();

if (empty($_SESSION['auth'])) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => 'unauthorized',
        'message' => 'Authentication required.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

const API_KEY = 'AIzaSyDop-z4X6tGbEhcxLBJns5vOUVY3-adAbI';
const PLAYLIST_ID = 'PLO7U__0AocSdjdnPDiwBU5FXEADIl_Rtx';
const MAX_RESULTS = 12;

$pageToken = isset($_GET['pageToken']) ? (string) $_GET['pageToken'] : '';
$pageToken = preg_replace('/[^a-zA-Z0-9_-]/', '', $pageToken ?? '');

$params = [
    'part' => 'snippet',
    'playlistId' => PLAYLIST_ID,
    'maxResults' => MAX_RESULTS,
    'key' => API_KEY,
];

if ($pageToken !== '') {
    $params['pageToken'] = $pageToken;
}

$url = 'https://www.googleapis.com/youtube/v3/playlistItems?' . http_build_query($params);

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10,
        'header' => [
            'Accept: application/json',
            'User-Agent: DSM-EspaceMemoire/1.0'
        ],
    ],
]);

$response = @file_get_contents($url, false, $context);
$status = 200;

if (isset($http_response_header[0]) && preg_match('/HTTP\/\S+\s+(\d{3})/', $http_response_header[0], $matches)) {
    $status = (int) $matches[1];
}

header('Content-Type: application/json; charset=utf-8');

if ($response === false) {
    http_response_code(502);
    echo json_encode([
        'error' => 'upstream_unreachable',
        'message' => 'Unable to contact YouTube API.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($status >= 400) {
    http_response_code($status);
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode($decoded, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'error' => 'upstream_error',
            'status' => $status,
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

echo $response;
