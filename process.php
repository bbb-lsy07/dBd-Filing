<?php
session_start();
require_once 'common.php';
$db = init_database();
$settings = $db->querySingle("SELECT * FROM settings", true);
$site_url = $settings['site_url'] ?? 'https://icp.bbb-lsy07.my';
require_once 'send_mail.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 验证 CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "CSRF 验证失败，请重试！";
    } else {
        $website_name = htmlspecialchars($_POST['website_name']);
        $website_url = htmlspecialchars($_POST['website_url']);
        $description = htmlspecialchars($_POST['description']);
        $contact_email = htmlspecialchars($_POST['contact_email']);
        
        if (isset($_POST['icp_number']) && preg_match('/^\d{8}$/', $_POST['icp_number'])) {
            $filing_number = htmlspecialchars($_POST['icp_number']);
            $stmt = $db->prepare("SELECT COUNT(*) FROM filings WHERE filing_number = :filing_number");
            $stmt->bindValue(':filing_number', $filing_number, SQLITE3_TEXT);
            $check = $stmt->execute()->fetchArray(SQLITE3_NUM)[0];
            if ($check > 0) {
                $year = date('Y');
                $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                $filing_number = $year . $random;
            }
        } else {
            $year = date('Y');
            $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $filing_number = $year . $random;
        }
        
        $stmt = $db->prepare("INSERT INTO filings (filing_number, website_name, website_url, description, contact_email, submission_date, status) 
                              VALUES (:filing_number, :website_name, :website_url, :description, :contact_email, :submission_date, 'pending')");
        $stmt->bindValue(':filing_number', $filing_number, SQLITE3_TEXT);
        $stmt->bindValue(':website_name', $website_name, SQLITE3_TEXT);
        $stmt->bindValue(':website_url', $website_url, SQLITE3_TEXT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $stmt->bindValue(':contact_email', $contact_email, SQLITE3_TEXT);
        $stmt->bindValue(':submission_date', date('Y-m-d H:i:s'), SQLITE3_TEXT);
        try {
            $result = $stmt->execute();
            if (!$result) {
                throw new Exception("Database insert failed.");
            }
        } catch (Exception $e) {
            error_log("Filing submission failed: " . $e->getMessage());
            $error_message = "备案申请提交失败，请稍后重试！";
        }
        
        if (!isset($error_message)) {
            $display_number = "联bBb盟 icp备" . $filing_number;
            $code = "<a href='$site_url/query.php?keyword=$filing_number' target='_blank'>$display_number</a>";
            $subject = "备案申请已提交 - " . ($settings['site_title'] ?? '');
            $body = "<h2>备案申请确认</h2><p>您的网站 <strong>" . $website_name . "</strong> 的备案申请已提交。</p><p>备案号：<strong>$display_number</strong></p><p>状态：待审核</p><p>审核将在 2~4 个休息日内完成，请耐心等待。</p>";
            try {
                if (!sendMail($contact_email, $subject, $body)) {
                    throw new Exception("Mail sending failed.");
                }
            } catch (Exception $e) {
                error_log("Mail sending failed: " . $e->getMessage());
                $mail_error = "邮件发送失败，请联系管理员";
            }
        }
    }
} else {
    header("Location: join.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title>备案申请成功 - <?php echo htmlspecialchars($settings['site_title'] ?? ''); ?></title>
    <link rel="icon" href="https://www.dmoe.cc/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container">
        <div class="header">
            <h1 class="holographic-text">备案申请成功</h1>
            <?php if (isset($error_message)): ?>
                <p class="error"><?php echo $error_message; ?></p>
                <button class="search-button glow-button" onclick="history.back()">
                    <span>返回重试</span>
                    <div class="glow"></div>
                </button>
            <?php else: ?>
                <p>您的备案号是：<strong><?php echo $display_number; ?></strong></p>
                <p>状态：<span class="pending">待审核</span>（通过后将在公示页面显示）</p>
                <p>请将以下代码添加到您的网站页脚：</p>
                <pre class="card-effect"><?php echo htmlspecialchars($code); ?></pre>
                <button class="copy-btn glow-button" onclick="copyToClipboard()">
                    <span>一键复制</span>
                    <div class="glow"></div>
                </button>
                <p>审核将在 <strong>2~4个休息日</strong> 内完成，请耐心等待。</p>
                <p>如有疑问，请联系：<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?> 或加入QQ群：<a href="https://qm.qq.com/q/<?php echo htmlspecialchars($settings['qq_group'] ?? ''); ?>" target="_blank"><?php echo htmlspecialchars($settings['qq_group'] ?? ''); ?></a></p>
                <?php if (isset($mail_error)) echo "<p class='error'>$mail_error</p>"; ?>
                <button class="search-button glow-button" onclick="location.href='join.php'">
                    <span>返回申请页面</span>
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
        <a href="<?php echo htmlspecialchars($settings['site_url'] ?? ''); ?>/query.php?keyword=20240001" target="_blank">联bBb盟 icp备20240001号(This is a virtual filing system for entertainment and community interaction, not an official filing.)</a>
    </div>
    <script>
        function copyToClipboard() {
            navigator.clipboard.writeText(<?php echo json_encode($code); ?>).then(() => alert('已复制到剪贴板！'), () => alert('复制失败，请手动复制。'));
        }
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