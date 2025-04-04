<?php
session_start();

$db = new SQLite3('database.sqlite');

// 创建管理员表（如果不存在）
$db->exec("CREATE TABLE IF NOT EXISTS admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    password TEXT
)");

// 检查是否有管理员账户，如果没有则创建默认账户
$result = $db->query("SELECT COUNT(*) as count FROM admins");
$row = $result->fetchArray(SQLITE3_ASSOC);
if ($row['count'] == 0) {
    $default_username = "admin";
    $default_password = password_hash("123456", PASSWORD_DEFAULT); // 使用密码哈希加密
    $stmt = $db->prepare("INSERT INTO admins (username, password) VALUES (:username, :password)");
    $stmt->bindValue(':username', $default_username, SQLITE3_TEXT);
    $stmt->bindValue(':password', $default_password, SQLITE3_TEXT);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $db->prepare("SELECT * FROM admins WHERE username = :username");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['admin_id'] = $row['id']; // 设置 admin_id
            header("Location: admin.php");
            exit;
        } else {
            $error = "用户名或密码错误！";
        }
    } else {
        $error = "用户名或密码错误！";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台登录 - 联bBb盟 ICP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container">
        <h1>后台登录</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">登录</button>
        </form>
        
        <div class="links">
            <a href="index.php" class="back-link">返回首页</a>
        </div>
    </div>
</body>
</html>