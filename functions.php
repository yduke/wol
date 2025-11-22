<?php
/**
 * 将 CIDR 网段（如 192.168.1.0/24）转换为广播地址（如 192.168.1.255）
 * @param string $cidr e.g. "192.168.1.0/24"
 * @return string|false 广播地址字符串或 false（输入不合法）
 */
function cidr_to_broadcast(string $cidr) {
    if (strpos($cidr, '/') === false) return false;
    list($ip, $mask) = explode('/', $cidr, 2);
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) return false;
    $mask = intval($mask);
    if ($mask < 0 || $mask > 32) return false;

    $ip_long = ip2long($ip);
    $netmask = ($mask === 0) ? 0 : (~((1 << (32 - $mask)) - 1) & 0xFFFFFFFF);
    $broadcast_long = ($ip_long & $netmask) | (~$netmask & 0xFFFFFFFF);
    return long2ip($broadcast_long);
}

/**
 * 将 MAC 地址字符串转换成二进制字节串（6 字节）
 * 接受 "aa:bb:cc:dd:ee:ff", "aa-bb-cc-dd-ee-ff", "aabbccddeeff"
 * @param string $mac
 * @return string|false 二进制字符串或 false（格式不合法）
 */
function mac_to_bytes(string $mac) {
    // 只保留十六进制字符
    $clean = preg_replace('/[^0-9a-fA-F]/', '', $mac);
    if (strlen($clean) !== 12) return false;

    $bytes = '';
    for ($i = 0; $i < 12; $i += 2) {
        $bytes .= chr(hexdec(substr($clean, $i, 2)));
    }
    return $bytes;
}

/**
 * 发送 Wake-on-LAN 魔法包
 * @param string $mac MAC 地址
 * @param string $target 广播地址或 CIDR（例如 "192.168.1.255" 或 "192.168.1.0/24"），默认 255.255.255.255
 * @param int $port UDP 端口（通常 7 或 9），默认 9
 * @param int $timeout 秒 数（socket 写入超时），默认 2
 * @return array ['ok' => bool, 'message' => string]
 */
function send_wol(string $mac, string $target = '255.255.255.255', int $port = 9, int $timeout = 2) {
    // 1. 解析目标：若是 CIDR，则转换为广播地址
    if (strpos($target, '/') !== false) {
        $broadcast = cidr_to_broadcast($target);
        if ($broadcast === false) {
            return ['ok' => false, 'message' => "无效的 CIDR: $target"];
        }
    } else {
        $broadcast = $target;
        if (filter_var($broadcast, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return ['ok' => false, 'message' => "无效的目标 IP: $target"];
        }
    }

    // 2. MAC -> bytes
    $mac_bytes = mac_to_bytes($mac);
    if ($mac_bytes === false) {
        return ['ok' => false, 'message' => "无效的 MAC 地址: $mac"];
    }

    // 3. 构造魔法包：6 x 0xFF + 16 x MAC
    $packet = str_repeat(chr(0xFF), 6) . str_repeat($mac_bytes, 16);

    // 4. 优先使用 sockets 扩展（可设置 SO_BROADCAST）
    if (function_exists('socket_create')) {
        $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock === false) {
            // fallback 下方
        } else {
            // 允许广播
            @socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
            // 设置写超时（可选）
            if (defined('SO_SNDTIMEO')) {
                $tv = ['sec' => $timeout, 'usec' => 0];
                @socket_set_option($sock, SOL_SOCKET, SO_SNDTIMEO, $tv);
            }
            $sent = @socket_sendto($sock, $packet, strlen($packet), 0, $broadcast, $port);
            @socket_close($sock);
            if ($sent === false || $sent === 0) {
                // fallthrough to fsockopen fallback
            } else {
                return ['ok' => true, 'message' => "魔法包已发送到 {$broadcast}:{$port}（使用 sockets）"];
            }
        }
    }

    // 5. 回退：使用 UDP stream（fsockopen / stream_socket_client）
    $uri = "udp://{$broadcast}:{$port}";
    $fp = @stream_socket_client($uri, $errno, $errstr, $timeout);
    if ($fp === false) {
        return ['ok' => false, 'message' => "无法建立 UDP 连接: $errstr ($errno)"];
    }

    // 若目标为广播，某些系统/网络可能要求设置 stream 不阻塞或其他，直接 fwrite 一次
    $bytes_written = @fwrite($fp, $packet);
    @fclose($fp);
    if ($bytes_written === false || $bytes_written === 0) {
        return ['ok' => false, 'message' => "向 {$broadcast}:{$port} 发送失败（使用 stream）"];
    }

    return ['ok' => true, 'message' => "魔法包已发送到 {$broadcast}:{$port}（使用 stream）"];
}
