<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => '未授权访问']);
    exit;
}
require_once __DIR__ . '/functions.php';
$mac = $_POST['mac'] ?? '';
$network = $_POST['network'] ?? '255.255.255.255';
$port = intval($_POST['port'] ?? 9);

if (!$mac) {
    echo json_encode(['ok' => false, 'message' => '缺少 MAC 地址']);
    exit;
}
$result = send_wol($mac, $network, $port);
echo json_encode($result);
