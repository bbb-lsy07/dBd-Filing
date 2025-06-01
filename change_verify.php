<?php
session_start();
require_once 'common.php';
require_once 'config.php';
require_once 'send_mail.php';

$db = init_database();

$error_msg = '';
$stage = '';

// 处理初始 POST: 从 change.php 跳转过来的 id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !isset($_POST['stage'])) {
    $id = intval($_POST['id']);
    $stmt = $db->prepare("SELECT * FROM filings WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    if (!$row) {
        $error_msg = '未找到对应的备案记录。';
    } else {
        $_SESSION['verify_id'] = $id;
        $stage = 'email';
    }
}
// 处理邮箱提交
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stage']) && $_POST['stage'] === 'email') {
    $id = intval($_SESSION['verify_id'] ?? 0);
    $input_email = trim($_POST['email'] ?? '');
    if ($id <= 0) {
        $error_msg = '会话失效，请重新操作。';
        $stage = '';
    } else {
        $stmt = $db->prepare("SELECT contact_email FROM filings WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if (!$row) {
            $error_msg = '备案记录不存在，请返回重试。';
            $stage = '';
        } elseif (strcasecmp($row['contact_email'], $input_email) !== 0) {
            $error_msg = '您输入的邮箱与备案时填写的邮箱不匹配。';
            $stage = 'email';
        } else {
            $verify_code = sprintf('%06d', random_int(0, 999999));
            $_SESSION["verify_code_{$id}"] = $verify_code;
            $subject = '【dBd-Filing】备案信息变更验证码';
            $body = "您正在进行备案信息变更操作，验证码为：{$verify_code}。若非本人操作，请忽略此邮件。";
            $to = $row['contact_email'];
            $mail_sent = sendMail($to, $subject, $body);
            if (!$mail_sent) {
                $error_msg = '验证码发送失败，请稍后再试。';
                $stage = 'email';
            } else {
                $stage = 'code';
            }
        }
    }
}
// 处理验证码提交
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stage']) && $_POST['stage'] === 'code') {
    $id = intval($_SESSION['verify_id'] ?? 0);
    $input_code = trim($_POST['code'] ?? '');
    if ($id <= 0) {
        $error_msg = '会话失效，请重新操作。';
        $stage = '';
    } else {
        $saved_code = $_SESSION["verify_code_{$id}"] ?? '';
        if ($input_code !== $saved_code) {
            $error_msg = '验证码错误，请重新输入。';
            $stage = 'code';
        } else {
            $stage = 'edit';
        }
    }
}
// 其他情况重定向到 change.php
else {
    if ($stage === '') {
        header('Location: change.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>备案信息变更验证</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="favicon.ico">
    <style>
        .center-box {
            max-width: 450px;
            margin: 60px auto;
            padding: 30px 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }
        .center-box h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #e6e6e6;
        }
        .neon-input {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            color: #fff;
            font-size: 1rem;
            outline: none;
        }
        .neon-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #0ff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            color: #000;
            cursor: pointer;
            transition: background 0.3s;
        }
        .submit-btn:hover {
            background: #0dd;
        }
        .error-msg {
            color: #ff4d4f;
            text-align: center;
            margin-bottom: 15px;
        }
        label {
            color: #e6e6e6;
            font-size: 0.95rem;
            display: block;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
    <div class="github-corner"></div>
    <div class="container">
        <div class="center-box">
            <?php if ($stage === 'email'): ?>
                <h2>输入备案邮箱验证</h2>
                <?php if ($error_msg): ?>
                    <p class="error-msg"><?= htmlspecialchars($error_msg) ?></p>
                <?php endif; ?>
                <form action="change_verify.php" method="POST">
                    <input type="hidden" name="stage" value="email">
                    <label for="email">请填写备案时使用的邮箱：</label>
                    <input type="email" id="email" name="email" class="neon-input" placeholder="example@mail.com" required>
                    <button type="submit" class="submit-btn">发送验证码</button>
                </form>
            <?php elseif ($stage === 'code'): ?>
                <h2>输入邮箱验证码</h2>
                <?php if ($error_msg): ?>
                    <p class="error-msg"><?= htmlspecialchars($error_msg) ?></p>
                <?php endif; ?>
                <form action="change_verify.php" method="POST">
                    <input type="hidden" name="stage" value="code">
                    <label for="code">验证码：</label>
                    <input type="text" id="code" name="code" class="neon-input" placeholder="请输入 6 位验证码" maxlength="6" required>
                    <button type="submit" class="submit-btn">验证并继续</button>
                </form>
            <?php elseif ($stage === 'edit'): ?>
                <?php
                    $id = intval($_SESSION['verify_id']);
                    $stmt = $db->prepare("SELECT * FROM filings WHERE id = :id");
                    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
                    $result = $stmt->execute();
                    $record = $result->fetchArray(SQLITE3_ASSOC);
                    if (!$record) {
                        echo '<p class="error-msg">未找到对应备案记录，无法进入修改。</p>';
                    } else {
                ?>
                <h2>修改备案详情</h2>
                <form action="process_change.php" method="POST">
                    <input type="hidden" name="id" value="<?= $record['id'] ?>">
                    <label for="website_name">网站名称：</label>
                    <input type="text" id="website_name" name="website_name" class="neon-input"
                           value="<?= htmlspecialchars($record['website_name']) ?>" required>
                    <label for="website_url">网站地址（URL）：</label>
                    <input type="url" id="website_url" name="website_url" class="neon-input"
                           value="<?= htmlspecialchars($record['website_url']) ?>" required>
                    <label for="description">网站描述：</label>
                    <textarea id="description" name="description" class="neon-input" rows="3" required><?= htmlspecialchars($record['description']) ?></textarea>
                    <label for="contact_email">联系人邮箱：</label>
                    <input type="email" id="contact_email" name="contact_email" class="neon-input"
                           value="<?= htmlspecialchars($record['contact_email']) ?>" required>
                    <button type="submit" class="submit-btn">提交修改</button>
                </form>
                <?php } ?>
            <?php else: ?>
                <h2>操作异常，请返回</h2>
                <?php if ($error_msg): ?>
                    <p class="error-msg"><?= htmlspecialchars($error_msg) ?></p>
                <?php endif; ?>
                <p><a href="change.php"><button class="submit-btn" style="width:auto; margin: 0 auto;">返回变更页面</button></a></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="footer">
        <?= getFooterText() ?>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.body.classList.add('loaded');
    });
    </script>
</body>
</html>
