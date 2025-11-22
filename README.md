# Wake On Lan PHP
## 描述 Description

将这个PHP项目放置在你的局域网PHP环境内，浏览器打开index.php，可以对局域网内设定的主机进行WOL开机。
Put this repo in your LAN PHP environment, visit index.php on broswer, you can Wake configered hosts on web page.

## 安装 install

步骤
1. 将所有文件放置在局域网PHP WEB服务器内。Put all file in this repo in your LAN PHP web server.
2. 修改`config-sample.php`，修改用户名和密码，并将文件重命名为`config.php`。 Edit `config-sample.php`, fill your username and password. Rename it to `config.php`.
3. 修改`hosts.json`，填入你想控制的局域网内主机MAC和网段IP等信息。
4. 访问项目所在的域名，例如：`http://192.168.2.6/wol/`，可选使用反向代理提供外部访问。

Step by step
1. Put all file in this repo in your LAN PHP web server.
2. Edit `config-sample.php`, fill your username and password. Rename it to `config.php`.
3. Edit `hosts.json` with IP MAC ex. of the hosts you want to control.
4. Visit the web from your broswer, eg `http://192.168.2.6/wol/`, optionally use reversd porxy to prviode external access.
