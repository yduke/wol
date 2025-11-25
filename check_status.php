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
$timeout = 1; // 尽量短，避免卡顿

// 1. TCP 端口快速探测（首选：和现有方案兼容）
$ports = [80, 443, 22, 445, 3389, 53, 139];
foreach ($ports as $port) {
    $conn = @fsockopen($ip, $port, $errno, $errstr, $timeout);
    if ($conn) {
        fclose($conn);
        exit('online');
    }
}

// 2. UDP 探测（UDP 更容易穿防火墙，但不保证回包）
function udp_probe($ip, $port = 7) {
    $sock = @fsockopen("udp://".$ip, $port, $errno, $errstr, 0.5);
    if (!$sock) return false;

    @fwrite($sock, "ping");
    stream_set_timeout($sock, 1);

    $data = @fread($sock, 1);
    fclose($sock);

    // UDP 返回数据的情况不常见，但如果收到则说明一定在线
    return !empty($data);
}
if (udp_probe($ip)) {
    exit('online');
}

// 3. HTTP 探测（如果目标有 web server）
$ctx = stream_context_create([
    'http' => [
        'timeout' => 1,
        'method'  => 'HEAD'
    ]
]);
$fp = @fopen("http://$ip/", 'r', false, $ctx);
if ($fp) {
    fclose($fp);
    exit('online');
}

// 4. 可选：ICMP Ping（仅在 shell_exec 可用时）
if (function_exists('shell_exec')) {
    $ping = @shell_exec("ping -c 1 -W 1 $ip 2>&1");
    if (strpos($ping, '1 received') !== false ||
        strpos($ping, 'bytes from') !== false) {
        exit('online');
    }
}

// 5. 可选：ARP（仅在 shell_exec 可用时）
if (function_exists('shell_exec')) {
    @shell_exec("ping -c 1 -W 1 $ip >/dev/null 2>&1");
    $arp = @shell_exec("arp -n $ip 2>/dev/null");

    if ($arp && strpos($arp, 'incomplete') === false && strpos($arp, $ip) !== false) {
        exit('online');
    }
}

exit('offline');
