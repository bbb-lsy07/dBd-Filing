<?php
require_once 'common.php';
$db = init_database();
$settings = $db->querySingle("SELECT * FROM settings", true);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name="description" content="加入联bBb盟 ICP 备案，获取您的虚拟备案号。">
    <meta name="keywords" content="加入ICP备案, 虚拟备案, <?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>">
    <title>加入 - <?php echo htmlspecialchars($settings['site_title'] ?? ''); ?></title>
    <link rel="icon" href="https://www.dmoe.cc/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('form').addEventListener('submit', (e) => {
                const icpNumber = document.querySelector('input[name="icp_number"]').value.trim();
                if (!/^\d{8}$/.test(icpNumber)) {
                    e.preventDefault();
                    alert("备案号必须是8位纯数字。");
                }
            });
        });
    </script>
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container page-transition">
        <div class="header">
            <h1 class="holographic-text">加入 <?php echo htmlspecialchars($settings['site_title'] ?? ''); ?></h1>
        </div>
        <div class="search-box">
            <form action="process.php" method="POST" class="neon-form">
                <input type="text" name="icp_number" class="search-input" placeholder="请输入您想要的8位备案号" required>
                <input type="text" name="website_name" class="search-input" placeholder="请输入网站名称" required>
                <input type="url" name="website_url" class="search-input" placeholder="请输入网站地址" required>
                <textarea name="description" class="search-input" placeholder="请输入网站描述" required></textarea>
                <input type="email" name="contact_email" class="search-input" placeholder="请输入联系邮箱" required>
                <button type="submit" class="search-button glow-button">
                    <span>提交申请</span>
                    <div class="glow"></div>
                </button>
            </form>
            <p class="note">提交申请后，将在3个休息日审核。如超过7天未回复，请联系：<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?></p>
        </div>
    </div>
    <div class="footer">
        <a href="index.php">主页</a>
        <a href="about.php">关于</a>
        <a href="join.php">加入</a>
        <a href="change.php">变更</a>
        <a href="public.php">公示</a>
        <a href="travel.php">迁跃</a>
        <br>
        <a href="<?php echo htmlspecialchars($settings['site_url'] ?? ''); ?>/query.php?keyword=20240001" target="_blank">联bBb盟 icp备20240001号</a>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('loaded');
        });
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                document.body.classList.remove('loaded');
                setTimeout(() => {
                    window.location = e.target.href;
                }, 300);
            });
        });
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => {
                form.style.transform = 'scale(0.98)';
                setTimeout(() => form.style.transform = '', 200);
            });
        });
    </script>
</body>
</html>