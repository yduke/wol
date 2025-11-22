<?php
session_start();
require_once __DIR__ . '/config.php';

if (empty($_SESSION['logged_in']) || time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$jsonFile = __DIR__ . '/hosts.json';
$hosts = [];
if (file_exists($jsonFile)) {
    $hosts = json_decode(file_get_contents($jsonFile), true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <title>Wake On LAN 控制台</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="./assets/image/power.png">
  <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="./assets/icons/iconfont.css" id="iconfont" rel="stylesheet">
  <link href="./assets/css/main.css" id="main-style" rel="stylesheet">
</head>
<body class="bg-light">
<header class="p-2 text-bg-dark">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h4">
        <i class="icon-font ico-power"></i>
        <span>Wake On LAN 控制台</span>
      </h1>
      <a href="logout.php" class="btn btn-light btn-sm"><i class="icon-font ico-logout"></i> 登出</a>

    </div>
  </div>
</header>

<div class="container py-4">

<div id="result" class="mt-4"></div>

  <div id="table-container">
    <?php if (empty($hosts)): ?>
      <div class="alert alert-warning text-center">未找到主机配置，请编辑 <code>hosts.json</code></div>
    <?php else: ?>
      <table id="hostTable" class="table table-striped table-hover align-middle bg-white shadow-sm rounded">
        <thead>
          <tr>
            <th>#</th>
            <th>名称</th>
            <th>MAC 地址</th>
            <th>网段 / 广播</th>
            <th>端口</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($hosts as $i => $h): ?>
            <tr data-ip="<?= htmlspecialchars($h['ip']) ?>">
              <td><?= $i+1 ?></td>
              <td><span class="status-dot offline"></span><?= htmlspecialchars($h['name'] ?? '-') ?></td>
              <td><?= htmlspecialchars($h['mac'] ?? '-') ?></td>
              <td><?= htmlspecialchars($h['network'] ?? '-') ?></td>
              <td><?= htmlspecialchars($h['port'] ?? 9) ?></td>
              <td>
                <button class="btn btn-success btn-sm send-wol"
                  data-mac="<?= htmlspecialchars($h['mac']) ?>"
                  data-network="<?= htmlspecialchars($h['network']) ?>"
                  data-port="<?= htmlspecialchars($h['port']) ?>">
                  唤醒
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  
</div>


<div class="container">
  <footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
    <div class="col-md-4 d-flex align-items-center">
      <a href="#" class="mb-3 me-2 mb-md-0 text-body-secondary text-decoration-none lh-1" aria-label="WOL" style="font-size: 1.5rem;">
        <i class="icon-font ico-power"></i>
      </a> <span class="mb-3 mb-md-0 text-body-secondary">© 2025 Duke Yin</span>
    </div> <ul class="nav col-md-4 justify-content-end list-unstyled d-flex">
      <li class="ms-3">
        <a class="text-body-secondary text-decoration-none" href="https://dukeyin.com/" target="_blank" aria-label="dukeyin" style="font-size: 1.5rem;">
          <i class="icon-font ico-dukeyin"></i>
        </a>
      </li>
      <li class="ms-3">
        <a class="text-body-secondary text-decoration-none" href="https://github.com/yduke" target="_blank" aria-label="github" style="font-size: 1.5rem;">
          <i class="icon-font ico-github"></i>
        </a>
      </li>

    </ul>
  </footer>
</div>
<script src="./assets/js/bootstrap.bundle.min.js"></script>
<script src="./assets/jquery-3.7.1.min.js"></script>
<script src="./assets/js/scripts.js"></script>
</body>
</html>
