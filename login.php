<?php
session_start();
require_once __DIR__ . '/config.php';

// 若已登录直接跳转
if (!empty($_SESSION['logged_in']) && time() - $_SESSION['login_time'] < SESSION_TIMEOUT) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        header('Location: index.php');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN" data-bs-theme="auto" class="bg-body-tertiary">
<head>
  <meta charset="UTF-8">
  <title>登录 - Wake On LAN 控制台</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="./assets/css/bootstrap.min.css?v=5.3.8" rel="stylesheet">
  <link href="./assets/icons/iconfont.css?v=<?= APP_VERSION ?>" id="iconfont" rel="stylesheet">
  <link href="./assets/css/main.css?v=<?= APP_VERSION ?>" id="main-style" rel="stylesheet">
    <script src="./assets/js/darkmode.js?v=<?= APP_VERSION ?>"></script>
</head>
<body class="bg-body-tertiary">
<div class="container d-flex justify-content-center align-items-center" style="height:80vh;">
  <div class="card shadow-lg p-4" style="max-width:400px; width:100%;">
    <h3 class="text-center mb-4"><i class="icon-font ico-power"></i> 登录</h3>
    <p class="text-center text-body-secondary">登录 Wake on LAN 控制台</p>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="form-floating mb-3">
        <input type="text" id="username" name="username" class="form-control" placeholder="name@example.com" required>
        <label for="username" class="form-label">用户名</label>
      </div>
      <div class="form-floating mb-3">
        <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
        <label for="password" class="form-label">密码</label>
      </div>
      <button type="submit" class="btn btn-primary w-100">登录</button>
    </form>
  </div>
</div>
</body>
</html>
