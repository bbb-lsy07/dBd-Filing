<?php
session_start();
require_once 'common.php';
$db = init_database();
$settings = $db->querySingle("SELECT * FROM settings", true);
require_once 'send_mail.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 验证 CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = 'CSRF 验证失败，请重试！';
    } else {
        $id = htmlspecialchars($_POST['id']);
        $website_name = htmlspecialchars($_POST['website_name']);
        $website_url = htmlspecialchars($_POST['website_url']);
        $description = htmlspecialchars($_POST['description']);
        $contact_email = htmlspecialchars($_POST['contact_email']);

        $stmt = $db->prepare("UPDATE filings SET website_name = :website_name, website_url = :website_url, description = :description, contact_email = :contact_email, status = 'pending' WHERE id = :id");
        $stmt->bindValue(':website_name', $website_name, SQLITE3_TEXT);
        $stmt->bindValue(':website_url', $website_url, SQLITE3_TEXT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $stmt->bindValue(':contact_email', $contact_email, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();

        if ($result) {
            $stmt = $db->prepare("SELECT filing_number FROM filings WHERE id = :id");
            $stmt->bindValue(':id', (int)$id, SQLITE3_INTEGER);
            $filing = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
            $success_message = "变更申请已提交，将重新审核！";
            $subject = "变更申请已提交 - " . ($settings['site_title'] ?? '');
            $body = "<h2>变更申请确认</h2><p>您的网站 <strong>" . $website_name . "</strong> 的变更申请已提交。</p><p>备案号：联bBb盟 icp备" . htmlspecialchars($filing['filing_number']) . "</p><p>状态：待审核</p><p>审核将在 2~4 个休息日内完成，请耐心等待。</p>";
            if (!sendMail($contact_email, $subject, $body)) {
                $mail_error = "邮件发送失败，请联系管理员";
            }
        } else {
            $error_message = "变更申请提交失败，请稍后重试！";
        }
    }
} else {
    header("Location: change.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title>变更申请结果 - <?php echo htmlspecialchars($settings['site_title'] ?? ''); ?></title>
    <link rel="icon" href="https://www.dmoe.cc/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container page-transition">
        <div class="header">
            <h1 class="holographic-text">变更申请结果</h1>
            <?php if (isset($success_message)): ?>
                <p class="success"><?php echo $success_message; ?></p>
                <p>审核将在 <strong>2~4个休息日</strong> 内完成，请耐心等待。</p>
                <p>如有疑问，请联系：<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?> 或加入QQ群：<a href="https://qm.qq.com/q/<?php echo htmlspecialchars($settings['qq_group'] ?? ''); ?>" target="_blank"><?php echo htmlspecialchars($settings['qq_group'] ?? ''); ?></a></p>
                <?php if (isset($mail_error)) echo "<p class='error'>$mail_error</p>"; ?>
                <button class="search-button glow-button" onclick="location.href='change.php'">
                    <span>返回变更页面</span>
                    <div class="glow"></div>
                </button>
            <?php elseif (isset($error_message)): ?>
                <p class="error"><?php echo $error_message; ?></p>
                <button class="search-button glow-button" onclick="history.back()">
                    <span>返回重试</span>
                    <div class="glow"></div>
                </button>
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
    </script>
    <?php echo getFooterText(); ?>
</body>
</html>