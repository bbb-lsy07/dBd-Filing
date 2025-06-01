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
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="description" content="<?php echo $meta_description; ?>">
    <meta name="keywords" content="<?php echo $meta_keywords; ?>">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" href="https://www.dmoe.cc/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-image: url('<?php echo htmlspecialchars($settings['background_image'] ?? 'https://www.dmoe.cc/random.php'); ?>');
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .container {
            padding-bottom: 60px; /* 为页脚留出空间 */
        }
        .search-box {
            max-width: 800px; /* 统一宽度 */
            margin: 20px auto;
        }
        .filing-details {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid #00ffcc;
            border-radius: 10px;
            padding: 20px;
            margin: 20px auto;
            max-width: 800px; /* 保持宽度 */
            box-shadow: 0 0 20px rgba(0, 255, 204, 0.5);
            backdrop-filter: blur(5px);
        }
        .filing-details h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            text-align: center;
            color: #fff;
            text-shadow: 0 0 10px #00ffcc, 0 0 20px #ff00ff;
            line-height: 1.2;
        }
        .filing-details dl {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* 一行两个字段 */
            gap: 5px; /* 减小间距 */
            margin: 0;
            font-size: 1rem;
        }
        .filing-details dl > div {
            display: flex;
            align-items: flex-start;
            padding: 5px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1); /* 添加分隔线 */
            min-width: 300px; /* 确保每组宽度均匀 */
        }
        .filing-details dt {
            font-weight: bold;
            color: #00ffcc;
            text-align: left;
            padding: 5px 5px 5px 0;
            flex: none; /* 动态宽度 */
            min-width: 80px; /* 最小宽度 */
        }
        .filing-details dd {
            margin: 0;
            padding: 5px 0;
            color: #fff;
            word-break: break-all;
            text-align: left;
            flex: 1;
        }
        .filing-details .description {
            max-height: 150px; /* 减小高度 */
            overflow-y: auto;
            padding: 10px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            font-size: 1rem;
            line-height: 1.5;
            text-align: left;
        }
        .filing-details a {
            color: #00ffcc;
            text-decoration: none;
            transition: color 0.3s;
        }
        .filing-details a:hover {
            color: #ff00ff;
        }
        .back-link {
            display: block;
            margin: 20px auto;
            text-align: center;
            color: #00ffcc;
            font-size: 1rem;
            text-decoration: none;
        }
        .back-link:hover {
            color: #ff00ff;
        }
        .error, .status-message {
            text-align: center;
            font-size: 1rem;
            margin: 20px 0;
            color: #ff4444;
        }
        .footer {
            position: relative;
            bottom: 0;
            width: 100%;
            padding: 10px 0;
            text-align: center;
            color: #fff;
        }
        @media (max-width: 600px) {
            .search-box {
                max-width: 100%; /* 移动端宽度自适应 */
                padding: 0 10px; /* 避免内容贴边 */
            }
            .filing-details {
                max-width: 100%; /* 移动端宽度自适应 */
                padding: 0 10px; /* 避免内容贴边 */
            }
            .filing-details dl {
                grid-template-columns: 1fr; /* 移动端单列 */
                gap: 10px; /* 移动端更大间距 */
            }
            .filing-details dl > div {
                flex-direction: column; /* 垂直排列 */
                border-bottom: none; /* 移动端移除分隔线 */
                min-width: 0; /* 适应移动端 */
            }
            .filing-details dt {
                min-width: 0;
            }
            .filing-details dd {
                flex: none;
            }
        }
    </style>
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container page-transition">
        <div class="header">
            <h1 class="holographic-text">查询备案信息</h1>
            <p><?php echo htmlspecialchars($settings['welcome_message'] ?? '这是一个虚拟备案系统，仅供娱乐和社区互动使用，非官方备案。'); ?></p>
        </div>
        <div class="search-box">
            <form action="query.php" method="GET" class="query-form neon-form">
                <input type="text" name="keyword" class="search-input" placeholder="请输入备案号或网站地址" value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>" required>
                <button type="submit" class="search-button glow-button">
                    <span>查询</span>
                    <div class="glow"></div>
                </button>
            </form>
            <?php if (isset($_GET['keyword'])): ?>
                <?php if ($row): ?>
                    <?php if ($row['status'] === 'approved'): ?>
                        <div class="filing-details card-effect">
                            <h2 class="holographic-text">备案详情</h2>
                            <dl>
                                <div>
                                    <dt>备案号</dt>
                                    <dd>联bBb盟 icp备<?php echo htmlspecialchars($row['filing_number']); ?></dd>
                                </div>
                                <div>
                                    <dt>网站名称</dt>
                                    <dd><?php echo htmlspecialchars($row['website_name']); ?></dd>
                                </div>
                                <div>
                                    <dt>网站地址</dt>
                                    <dd><a href="<?php echo htmlspecialchars($row['website_url']); ?>" target="_blank"><?php echo htmlspecialchars($row['website_url']); ?></a></dd>
                                </div>
                                <div>
                                    <dt>简介</dt>
                                    <dd class="description"><?php echo htmlspecialchars($row['description']); ?></dd>
                                </div>
                                <div>
                                    <dt>联系邮箱</dt>
                                    <dd><?php echo htmlspecialchars(substr($row['contact_email'], 0, 3) . str_repeat('*', max(0, strlen($row['contact_email']) - 6)) . substr($row['contact_email'], -3)); ?></dd>
                                </div>
                                <div>
                                    <dt>提交时间</dt>
                                    <dd><?php echo htmlspecialchars($row['submission_date']); ?></dd>
                                </div>
                                <div>
                                    <dt>备案证书</dt>
                                    <dd><a href="generate_certificate.php?filing_id=<?php echo $row['id']; ?>" target="_blank">下载备案证书</a></dd>
                                </div>
                                <div>
                                    <dt>状态</dt>
                                    <dd>已通过</dd>
                                </div>
                            </dl>
                        </div>
                    <?php else: ?>
                        <p class="status-message">
                            <?php echo $row['status'] === 'pending' ? '该备案正在审核中' : '该备案未通过审核'; ?>
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="error">未找到相关备案信息。</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <a href="index.php" class="back-link">返回主页</a>
    </div>
    <div class="footer">
        <?php echo getFooterText(); ?>
        <a href="<?php echo htmlspecialchars($site_url); ?>/query.php?keyword=20240001" target="_blank">联bBb盟 icp备20240001号</a>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.body.classList.add('loaded');
                const container = document.querySelector('.container');
                const header = document.querySelector('.header');
                function adjustContainerHeight() {
                    const headerHeight = header.offsetHeight;
                    const windowHeight = window.innerHeight;
                    const footerHeight = document.querySelector('.footer').offsetHeight;
                    const availableHeight = windowHeight - footerHeight - 40;
                    container.style.minHeight = `${availableHeight}px`;
                }
                adjustContainerHeight();
                window.addEventListener('resize', adjustContainerHeight);
            }, 50);
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
        });
    </script>
</body>
</html>