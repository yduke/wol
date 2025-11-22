<?php
header('Content-Type: text/plain');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if (!isset($_GET['ip'])) {
    http_response_code(400);
    exit('Missing IP');
}

$ip = $_GET['ip'];
$ports = [80, 22, 445, 3389, 9]; // 常见端口，可自行调整
$timeout = 3;
$online = false;

foreach ($ports as $port) {
    $conn = @fsockopen($ip, $port, $errno, $errstr, $timeout);
    if ($conn) {
        fclose($conn);
        $online = true;
        break;
    }
}

echo $online ? 'online' : 'offline';

