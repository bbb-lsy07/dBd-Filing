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
    <meta name="description" content="关于联bBb盟 ICP 备案系统，了解我们的虚拟备案服务。">
    <meta name="keywords" content="关于ICP备案, 虚拟备案, <?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>">
    <title>关于 - <?php echo htmlspecialchars($settings['site_title'] ?? ''); ?></title>
    <link rel="icon" href="https://www.dmoe.cc/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container page-transition">
        <div class="header">
            <h1 class="holographic-text">关于 <?php echo htmlspecialchars($settings['site_title'] ?? ''); ?></h1>
        </div>
        <div class="content card-effect">
            <h2>什么是联bBb盟 ICP 备案？</h2>
            <p>联bBb盟 ICP 备案是一个虚拟的网站备案系统，旨在为爱好者提供一个可爱的社区互动平台。</p>
            <h2>为什么加入我们？</h2>
            <p>加入联bBb盟，让您的网站拥有一个独特的备案号，既好看又有趣，是站长的个性展示。</p>
            <h2>我们有话说</h2>
            <p>欢迎所有喜欢虚拟备案的朋友加入！快来给您的网站添加一个联bBb盟 ICP 号吧~</p>
            <div class="button-container">
                <button class="join-btn glow-button" onclick="location.href='join.php'">
                    <span>加入联bBb盟</span>
                    <div class="glow"></div>
                </button>
            </div>
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
    <?php echo getFooterText(); ?>
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
    </script>
</body>
</html>