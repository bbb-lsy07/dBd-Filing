<?php
require_once 'common.php';
$db = init_database();
$settings = $db->querySingle("SELECT * FROM settings", true);
$site_title = $settings['site_title'] ?? '联bBb盟 ICP 备案系统';
$site_url = $settings['site_url'] ?? 'https://icp.bbb-lsy07.my';
$page_title = $site_title;
$meta_description = "查询虚拟 ICP 备案信息，了解网站状态和详情。";
$meta_keywords = "ICP 备案查询, 虚拟备案, $site_title";

if (isset($_GET['keyword'])) {
    $keyword = htmlspecialchars($_GET['keyword']);
    $stmt = $db->prepare("SELECT * FROM filings WHERE filing_number = :keyword OR website_url = :keyword");
    $stmt->bindValue(':keyword', $keyword, SQLITE3_TEXT);
    $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if ($row) {
        $page_title = htmlspecialchars($row['website_name']) . " - $site_title";
        $meta_description = "查询 " . htmlspecialchars($row['website_name']) . " 的虚拟 ICP 备案信息，备案号：联bBb盟 icp备{$row['filing_number']}。";
        $meta_keywords .= ", " . htmlspecialchars($row['website_name']) . ", " . htmlspecialchars($row['website_url']);
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name="description" content="<?php echo $meta_description; ?>">
    <meta name="keywords" content="<?php echo $meta_keywords; ?>">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" href="https://www.dmoe.cc/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container page-transition">
        <div class="header">
            <h1 class="holographic-text">查询备案信息</h1>
        </div>
        <div class="search-box">
            <form action="query.php" method="GET" class="query-form neon-form">
                <input type="text" name="keyword" class="search-input" placeholder="请输入备案号或网站地址" required>
                <button type="submit" class="search-button glow-button">
                    <span>查询</span>
                    <div class="glow"></div>
                </button>
            </form>
            <?php if (isset($_GET['keyword'])): ?>
                <?php if ($row): ?>
                    <div class="result card-effect">
                        <p><strong>备案号：</strong> 联bBb盟 icp备<?php echo htmlspecialchars($row['filing_number']); ?></p>
                        <p><strong>网站名称：</strong> <?php echo htmlspecialchars($row['website_name']); ?></p>
                        <p><strong>网站地址：</strong> <a href="<?php echo htmlspecialchars($row['website_url']); ?>" target="_blank"><?php echo htmlspecialchars($row['website_url']); ?></a></p>
                        <p><strong>描述：</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                        <p><strong>联系邮箱：</strong> <?php echo htmlspecialchars(substr($row['contact_email'], 0, 3) . str_repeat('*', max(0, strlen($row['contact_email']) - 6)) . substr($row['contact_email'], -3)); ?></p>
                        <p><strong>提交时间：</strong> <?php echo htmlspecialchars($row['submission_date']); ?></p>
                        <p><strong>状态：</strong> <?php echo $row['status'] == 'pending' ? '待审核' : ($row['status'] == 'approved' ? '已通过' : '已拒绝'); ?></p>
                    </div>
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