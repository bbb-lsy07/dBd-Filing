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
    <meta name="description" content="变更您的联bBb盟 ICP 备案信息。">
    <meta name="keywords" content="变更ICP备案, 虚拟备案, <?php echo htmlspecialchars($settings['site_title']); ?>">
    <title>变更 - <?php echo htmlspecialchars($settings['site_title']); ?></title>
    <link rel="icon" href="https://www.dmoe.cc/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container">
        <div class="header">
            <h1 class="holographic-text">变更备案信息</h1>
        </div>
        <div class="search-box">
            <form action="change.php" method="GET" class="query-form neon-form">
                <input type="text" name="keyword" class="search-input" placeholder="请输入备案号或网站地址" required>
                <button type="submit" class="search-button glow-button">
                    <span>查询</span>
                    <div class="glow"></div>
                </button>
            </form>
            <?php if (isset($_GET['keyword'])): ?>
                <?php
                $stmt = $db->prepare("SELECT * FROM filings WHERE filing_number = :keyword OR website_url = :keyword");
                $stmt->bindValue(':keyword', htmlspecialchars($_GET['keyword']), SQLITE3_TEXT);
                $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
                if ($row): ?>
                    <form action="process_change.php" method="POST" class="neon-form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <input type="text" name="website_name" class="search-input" value="<?php echo htmlspecialchars($row['website_name']); ?>" required>
                        <input type="url" name="website_url" class="search-input" value="<?php echo htmlspecialchars($row['website_url']); ?>" required>
                        <textarea name="description" class="search-input" required><?php echo htmlspecialchars($row['description']); ?></textarea>
                        <input type="email" name="contact_email" class="search-input" value="<?php echo htmlspecialchars($row['contact_email']); ?>" required>
                        <button type="submit" class="search-button glow-button">
                            <span>提交变更</span>
                            <div class="glow"></div>
                        </button>
                    </form>
                <?php else: ?>
                    <p class="error">未找到相关备案信息。</p>
                <?php endif; ?>
            <?php endif; ?>
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