<?php
session_start();
require_once 'common.php';
$db = init_database();

// Only handle POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: change.php');
    exit;
}

$id = intval($_POST['id'] ?? 0);
$website_name = trim($_POST['website_name'] ?? '');
$website_url = trim($_POST['website_url'] ?? '');
$description = trim($_POST['description'] ?? '');
$contact_email = trim($_POST['contact_email'] ?? '');

$error = '';
$success = '';

if ($id <= 0 || !$website_name || !$website_url || !$description || !$contact_email) {
    $error = '请填写完整信息。';
} else {
    $stmt = $db->prepare("UPDATE filings SET website_name = :name, website_url = :url, description = :desc, contact_email = :email WHERE id = :id");
    $stmt->bindValue(':name', $website_name, SQLITE3_TEXT);
    $stmt->bindValue(':url', $website_url, SQLITE3_TEXT);
    $stmt->bindValue(':desc', $description, SQLITE3_TEXT);
    $stmt->bindValue(':email', $contact_email, SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $res = $stmt->execute();
    if ($res) {
        $success = '备案信息已成功更新。';
    } else {
        $error = '更新失败，请重试。';
    }
}

// Fetch settings for footer
$settings = $db->querySingle("SELECT * FROM settings", true);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>提交结果</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="github-corner"></div>
    <div class="container">
        <div class="center-box">
            <?php if (!empty($error)): ?>
                <h2>操作失败</h2>
                <p class="error-msg"><?= htmlspecialchars($error) ?></p>
                <p><a href="change.php"><button class="submit-btn">返回</button></a></p>
            <?php else: ?>
                <h2>操作成功</h2>
                <p><?= htmlspecialchars($success) ?></p>
                <p><a href="index.php"><button class="submit-btn">返回首页</button></a></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="footer">
        <?= htmlspecialchars($settings['site_title'] ?? 'dBd-Filing') ?>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.body.classList.add('loaded');
    });
    </script>
</body>
</html>
