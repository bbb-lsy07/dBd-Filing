<?php
session_start();
require_once 'common.php';
$db = init_database();
$settings = $db->querySingle("SELECT * FROM settings", true);
$settings = $settings ?: [
    'site_title' => '联bBb盟 ICP 备案系统',
    'site_url' => 'https://icp.bbb-lsy07.my',
    'welcome_message' => '这是一个虚拟备案系统，仅供娱乐和社区互动使用，非官方备案。',
    'contact_email' => 'admin@bbb-lsy07.my',
    'qq_group' => '123456789',
    'background_image' => 'https://www.dmoe.cc/random.php'
];

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name="description" content="<?php echo htmlspecialchars($settings['welcome_message']); ?>">
    <meta name="keywords" content="ICP备案, 虚拟备案, <?php echo htmlspecialchars($settings['site_title']); ?>">
    <title><?php echo htmlspecialchars($settings['site_title']); ?></title>
    <link rel="icon" href="https://www.dmoe.cc/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container">
        <div class="header">
            <h1 class="holographic-text"><?php echo htmlspecialchars($settings['site_title']); ?></h1>
            <p class="note"><?php echo htmlspecialchars($settings['welcome_message']); ?></p>
        </div>
        <div class="search-box">
            <form action="process_filing.php" method="POST" class="neon-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="text" name="website_name" class="search-input" placeholder="请输入网站名称" required>
                <input type="url" name="website_url" class="search-input" placeholder="请输入网站地址（如：https://example.com）" required>
                <textarea name="description" class="search-input" placeholder="请输入网站描述" required></textarea>
                <input type="email" name="contact_email" class="search-input" placeholder="请输入联系邮箱" required>
                <button type="submit" class="search-button glow-button">
                    <span>提交备案</span>
                    <div class="glow"></div>
                </button>
            </form>
        </div>
    </div>
    <div class="footer">
        <a href="index.php">主页</a>
        <a href="about.php">关于</a>
        <a href="join.php">加入</a>
        <a href="change.php">变更</a>
        <a href="public.php">公示</a>
        <a href="travel.php">迁跃</a>
        <?php echo getFooterText(); ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('loaded');
        });
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => {
                form.style.transform = 'scale(0.98)';
                setTimeout(() => form.style.transform = '', 200);
            });
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