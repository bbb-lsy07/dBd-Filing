<?php
require_once 'common.php';
$db = init_database();
$settings = $db->querySingle("SELECT * FROM settings", true);
$results = $db->query("SELECT * FROM filings WHERE status = 'approved' ORDER BY submission_date DESC");
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name="description" content="查看已通过审核的虚拟 ICP 备案网站列表。">
    <meta name="keywords" content="ICP 备案公示, 虚拟备案, <?php echo htmlspecialchars($settings['site_title']?? ''); ?>">
    <title>公示页面 - <?php echo htmlspecialchars($settings['site_title']?? ''); ?></title>
    <link rel="icon" href="https://www.dmoe.cc/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-image: url('<?php echo htmlspecialchars($settings['background_image']?? 'https://www.dmoe.cc/random.php'); ?>');
        }
    </style>
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container page-transition">
        <div class="header">
            <h1 class="holographic-text">公示页面</h1>
            <p>以下为已通过审核的备案网站。</p>
        </div>
        <div class="table-wrapper card-effect">
            <table>
                <thead>
                    <tr>
                        <th data-label="备案号">备案号</th>
                        <th data-label="网站名称">网站名称</th>
                        <th data-label="地址">地址</th>
                        <th data-label="描述">描述</th>
                        <th data-label="提交时间">提交时间</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)):?>
                        <tr>
                            <td data-label="备案号"><a href="query.php?keyword=<?php echo $row['filing_number']; ?>" class="filing-link">联bBb盟 icp备<?php echo $row['filing_number']; ?></a></td>
                            <td data-label="网站名称"><?php echo htmlspecialchars($row['website_name']); ?></td>
                            <td data-label="地址"><a href="<?php echo htmlspecialchars($row['website_url']); ?>" target="_blank"><?php echo htmlspecialchars($row['website_url']); ?></a></td>
                            <td data-label="描述"><?php echo htmlspecialchars($row['description']); ?></td>
                            <td data-label="提交时间"><?php echo htmlspecialchars($row['submission_date']); ?></td>
                        </tr>
                    <?php endwhile;?>
                </tbody>
            </table>
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
        <a href="<?php echo htmlspecialchars($settings['site_url']?? ''); ?>/query.php?keyword=20240001" target="_blank">联bBb盟 icp备20240001号</a>
    </div>
    <?php echo getFooterText();?>
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