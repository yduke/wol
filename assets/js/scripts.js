function checkStatus() {
    $("#hostTable tr").each(function() {
      const row = $(this);
      const ip = row.data("ip");
      if (!ip) return; // 跳过没有IP的行

      $.get("check_status.php", {ip: ip}, function(resp) {
        const dot = row.find(".status-dot");
        const isOnline = resp === 'online';
        const wasOnline = dot.hasClass('online');

        // 如果状态变化了，则闪烁动画
        if (isOnline !== wasOnline) {
          dot.removeClass("online offline")
             .addClass(isOnline ? 'online' : 'offline')
             .addClass('status-change');

          // 1秒后移除动画类
          setTimeout(() => dot.removeClass('status-change'), 1000);
        } else {
          // 没变化则只是保持原状态
          dot.removeClass("online offline")
             .addClass(isOnline ? 'online' : 'offline');
        }
      });
    });
  }
    
    
    $(function(){
        checkStatus();
        setInterval(checkStatus, 5000); // 每5秒检测一次
    });

document.querySelectorAll('.send-wol').forEach(btn => {
  btn.addEventListener('click', async () => {
    const mac = btn.dataset.mac;
    const network = btn.dataset.network;
    const port = btn.dataset.port;
    btn.disabled = true;
    btn.textContent = '发送中...';
    const formData = new FormData();
    formData.append('mac', mac);
    formData.append('network', network);
    formData.append('port', port);

    try {
      const res = await fetch('wake.php', { method: 'POST', body: formData });
      const data = await res.json();
      const color = data.ok ? 'success' : 'danger';
      document.getElementById('result').innerHTML =
        `<div class="alert alert-${color} mt-3 alert-dismissible fade show">${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
    } catch (e) {
      document.getElementById('result').innerHTML =
        `<div class="alert alert-danger mt-3">请求失败：${e}</div>`;
    } finally {
      btn.disabled = false;
      btn.textContent = '唤醒';
    }
  });
});
