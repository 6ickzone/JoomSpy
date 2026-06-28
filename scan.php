<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET');

if (!isset($_GET['url']) || !isset($_GET['path'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameter']);
    exit;
}

$target = filter_var($_GET['url'], FILTER_VALIDATE_URL);
$path = $_GET['path'];

if (!$target) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid base URL']);
    exit;
}

$full_url = rtrim($target, '/') . $path;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $full_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_TIMEOUT, 6);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode(['status' => 'error', 'message' => 'Connection timeout/error']);
} else {
    echo json_encode(['status' => 'success', 'http_code' => $http_code]);
}

curl_close($ch);
