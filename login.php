<?php
session_start();
require_once 'common.php';
$db = init_database();
$settings = $db->querySingle("SELECT * FROM settings", true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['reset_password'])) {
    try {
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = :username");
        $stmt->bindValue(':username', $_POST['username'], SQLITE3_TEXT);
        $result = $stmt->execute();
        if ($result === false) {
            throw new Exception($db->lastErrorMsg());
        }
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if ($row && password_verify($_POST['password'], $row['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['user_role'] = $row['role'] ?? 'admin';
            if ($row['force_reset'] == 1) $_SESSION['force_reset'] = true;
            else header("Location: admin.php");
            exit;
        } else {
            $error = "用户名或密码错误！";
        }
    } catch (Exception $e) {
        error_log("登录失败: " . $e->getMessage());
        $error = "登录失败，请稍后再试！";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    if ($_POST['new_password'] === $_POST['confirm_password']) {
        try {
            $stmt = $db->prepare("UPDATE admins SET password = :password, force_reset = 0 WHERE id = :id");
            $stmt->bindValue(':password', password_hash($_POST['new_password'], PASSWORD_DEFAULT), SQLITE3_TEXT);
            $stmt->bindValue(':id', $_SESSION['admin_id'], SQLITE3_INTEGER);
            $result = $stmt->execute();
            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }
            unset($_SESSION['force_reset']);
            header("Location: admin.php");
            exit;
        } catch (Exception $e) {
            error_log("重置密码失败: " . $e->getMessage());
            $reset_error = "重置密码失败，请稍后再试！";
        }
    } else {
        $reset_error = "两次输入的密码不一致！";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title>后台登录 - <?php echo htmlspecialchars($settings['site_title'] ?? ''); ?></title>
    <link rel="icon" href="https://www.dmoe.cc/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container page-transition">
        <div class="header">
            <?php if (isset($_SESSION['force_reset']) && $_SESSION['force_reset']): ?>
                <h1 class="holographic-text">首次登录 - 重置密码</h1>
                <p class="note">请设置新密码以继续使用系统。</p>
                <?php if (isset($reset_error)) echo "<p class='error'>$reset_error</p>"; ?>
                <form action="login.php" method="POST" class="neon-form">
                    <input type="hidden" name="reset_password" value="1">
                    <input type="password" name="new_password" class="search-input" placeholder="新密码" required>
                    <input type="password" name="confirm_password" class="search-input" placeholder="确认新密码" required>
                    <button type="submit" class="search-button glow-button">
                        <span>重置密码</span>
                        <div class="glow"></div>
                    </button>
                </form>
            <?php else: ?>
                <h1 class="holographic-text">后台登录</h1>
                <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
                <form action="login.php" method="POST" class="neon-form">
                    <input type="text" name="username" class="search-input" placeholder="用户名" required>
                    <input type="password" name="password" class="search-input" placeholder="密码" required>
                    <button type="submit" class="search-button glow-button">
                        <span>登录</span>
                        <div class="glow"></div>
                    </button>
                </form>
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