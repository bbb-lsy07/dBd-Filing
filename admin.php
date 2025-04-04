<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$db = new SQLite3('database.sqlite');

// 创建管理员表（确保表存在）
$db->exec("CREATE TABLE IF NOT EXISTS admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    password TEXT
)");

// 检查并更新 filings 表结构
$result = $db->query("PRAGMA table_info(filings)");
$has_status = false;
while ($column = $result->fetchArray(SQLITE3_ASSOC)) {
    if ($column['name'] === 'status') {
        $has_status = true;
        break;
    }
}
if (!$has_status) {
    $db->exec("CREATE TABLE filings_new (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        filing_number TEXT UNIQUE,
        website_name TEXT,
        website_url TEXT,
        description TEXT,
        contact_email TEXT,
        submission_date TEXT,
        status TEXT DEFAULT 'pending'
    )");
    $db->exec("INSERT INTO filings_new (id, filing_number, website_name, website_url, description, contact_email, submission_date)
               SELECT id, filing_number, website_name, website_url, description, contact_email, submission_date FROM filings");
    $db->exec("UPDATE filings_new SET status = 'approved' WHERE status IS NULL");
    $db->exec("DROP TABLE filings");
    $db->exec("ALTER TABLE filings_new RENAME TO filings");
}

// 处理删除
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM filings WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
    header("Location: admin.php");
    exit;
}

// 处理审核
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    $stmt = $db->prepare("UPDATE filings SET status = 'approved' WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
    header("Location: admin.php");
    exit;
}
if (isset($_GET['reject'])) {
    $id = $_GET['reject'];
    $stmt = $db->prepare("UPDATE filings SET status = 'rejected' WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
    header("Location: admin.php");
    exit;
}

// 处理修改账户
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_username']) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
    $new_username = htmlspecialchars($_POST['new_username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password === $confirm_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $admin_id = $_SESSION['admin_id'];
        
        $stmt = $db->prepare("UPDATE admins SET username = :username, password = :password WHERE id = :id");
        if ($stmt) {
            $stmt->bindValue(':username', $new_username, SQLITE3_TEXT);
            $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
            $stmt->bindValue(':id', $admin_id, SQLITE3_INTEGER);
            $stmt->execute();
            $update_message = "账户信息已更新，请使用新用户名和密码重新登录！";
            session_destroy(); // 修改后强制退出登录
        } else {
            $update_error = "更新账户失败，请稍后重试！";
        }
    } else {
        $update_error = "两次输入的密码不一致！";
    }
}

// 获取所有备案记录
$results = $db->query("SELECT * FROM filings ORDER BY submission_date DESC");
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 - 联bBb盟 ICP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container">
        <h1>后台管理</h1>
        <p>欢迎，管理员！ 
            <a href="#" class="modify-link" onclick="document.getElementById('modifyModal').style.display='flex'">修改账户</a> 
            <a href="logout.php" class="logout-link">退出登录</a>
        </p>
        
        <h2>备案记录</h2>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>备案号</th>
                        <th>网站名称</th>
                        <th>地址</th>
                        <th>描述</th>
                        <th>邮箱</th>
                        <th>提交时间</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($results) {
                        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                            echo "<tr>";
                            echo "<td>{$row['id']}</td>";
                            echo "<td>联bBb盟 icp备{$row['filing_number']}</td>";
                            echo "<td>{$row['website_name']}</td>";
                            echo "<td><a href='{$row['website_url']}' target='_blank'>{$row['website_url']}</a></td>";
                            echo "<td>{$row['description']}</td>";
                            echo "<td>{$row['contact_email']}</td>";
                            echo "<td>{$row['submission_date']}</td>";
                            echo "<td>" . ($row['status'] == 'pending' ? '待审核' : ($row['status'] == 'approved' ? '已通过' : '已拒绝')) . "</td>";
                            echo "<td>";
                            if ($row['status'] == 'pending') {
                                echo "<a href='admin.php?approve={$row['id']}' class='approve-link'>通过</a> | ";
                                echo "<a href='admin.php?reject={$row['id']}' class='reject-link'>拒绝</a> | ";
                            }
                            echo "<a href='admin.php?delete={$row['id']}' class='delete-link' onclick='return confirm(\"确定删除吗？\");'>删除</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9'>暂无备案记录</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <div class="links">
            <a href="index.php" class="back-link">返回首页</a>
        </div>
    </div>

    <!-- 修改账户模态窗口 -->
    <div id="modifyModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('modifyModal').style.display='none'">×</span>
            <h2>修改账户信息</h2>
            <?php if (isset($update_message)) echo "<p class='success'>$update_message</p>"; ?>
            <?php if (isset($update_error)) echo "<p class='error'>$update_error</p>"; ?>
            <form action="admin.php" method="POST">
                <div class="form-group">
                    <label for="new_username">新用户名</label>
                    <input type="text" id="new_username" name="new_username" required>
                </div>
                <div class="form-group">
                    <label for="new_password">新密码</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">确认新密码</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit">更新账户</button>
            </form>
        </div>
    </div>

    <script>
        // 点击模态窗口外部关闭
        window.onclick = function(event) {
            var modal = document.getElementById('modifyModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>