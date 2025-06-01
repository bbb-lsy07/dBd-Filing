<?php
session_start();
require_once 'common.php';
require_once 'config.php';

// 环境检测
$errors = [];

// 检查PHP版本
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    $errors[] = 'PHP版本过低，请升级到PHP 7.4或更高版本。当前版本：' . PHP_VERSION;
}

// 检查SQLite3扩展
if (!class_exists('SQLite3')) {
    $errors[] = '未安装SQLite3扩展，请在php.ini中启用或安装。';
}

// 检查cURL扩展
if (!function_exists('curl_init')) {
    $errors[] = '未安装cURL扩展，请在php.ini中启用或安装。';
}

// 检查GD库扩展
if (!function_exists('gd_info')) {
    $errors[] = '未安装GD库扩展，请在php.ini中启用或安装。';
}

// 检查文件写入权限
$test_file = 'test_write_permission.txt';
if (@file_put_contents($test_file, 'test') === false) {
    $errors[] = '当前目录或子目录没有写入权限，请检查目录权限。';
} else {
    @unlink($test_file);
}

// 如果存在错误，显示错误信息并停止执行
if (!empty($errors)) {
    echo '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>环境检测失败</title><style>body{font-family: Arial, sans-serif; margin: 20px; background-color: #f8f8f8; color: #333;} .container{background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);} h1{color: #d9534f;} ul{list-style-type: none; padding: 0;} li{margin-bottom: 10px; background-color: #f2dede; border: 1px solid #ebccd1; color: #a94442; padding: 10px; border-radius: 4px;}</style></head><body><div class="container"><h1>环境检测失败</h1><p>系统运行所需环境不满足，请根据以下提示进行配置：</p><ul>';
    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul><p>配置完成后，请刷新本页面。</p></div></body></html>';
    exit();
}

$db = init_database();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['admin_id']) || (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'admin')) {
    header("HTTP/1.1 403 Forbidden");
    echo '<script>alert("权限不足，请重新登录！"); window.location.href = "login.php";</script>';
    exit();
}

$settings = $db->querySingle("SELECT * FROM settings", true);
$settings = $settings ?: [
    'site_title' => '联bBb盟 ICP 备案系统',
    'site_url' => 'https://icp.bbb-lsy07.my',
    'welcome_message' => '这是一个虚拟备案系统，仅供娱乐和社区互动使用，非官方备案。',
    'contact_email' => 'admin@bbb-lsy07.my',
    'qq_group' => '123456789',
    'background_image' => 'https://www.dmoe.cc/random.php',
    'version' => '1.0.0'
];
require_once 'send_mail.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// 获取统计数据
$total_filings = $db->querySingle("SELECT COUNT(*) FROM filings");
$pending_filings = $db->querySingle("SELECT COUNT(*) FROM filings WHERE status = 'pending'");
$approved_filings = $db->querySingle("SELECT COUNT(*) FROM filings WHERE status = 'approved'");
$rejected_filings = $db->querySingle("SELECT COUNT(*) FROM filings WHERE status = 'rejected'");

// 获取每日新增备案数量 (过去7天)
$daily_new_filings = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $count = $db->querySingle("SELECT COUNT(*) FROM filings WHERE DATE(submission_date) = '{$date}'");
    $daily_new_filings[$date] = $count ?: 0;
}

// 获取每月新增备案数量 (过去12个月)
$monthly_new_filings = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $count = $db->querySingle("SELECT COUNT(*) FROM filings WHERE STRFTIME('%Y-%m', submission_date) = '{$month}'");
    $monthly_new_filings[$month] = $count ?: 0;
}

if (isset($_GET['delete'])) {
    try {
        $stmt = $db->prepare("DELETE FROM filings WHERE id = :id");
        $stmt->bindValue(':id', (int)$_GET['delete'], SQLITE3_INTEGER);
        $stmt->execute();
        header("Location: admin.php");
        exit;
    } catch (Exception $e) {
        error_log("删除备案失败: " . $e->getMessage());
        echo '<script>alert("删除备案失败，请稍后再试！"); window.location.href = "admin.php";</script>';
        exit;
    }
}

if (isset($_GET['approve'])) {
    try {
        $db->exec('BEGIN;');
        $stmt = $db->prepare("UPDATE filings SET status = 'approved' WHERE id = :id");
        $stmt->bindValue(':id', (int)$_GET['approve'], SQLITE3_INTEGER);
        $stmt->execute();
        $stmt = $db->prepare("SELECT * FROM filings WHERE id = :id");
        $stmt->bindValue(':id', (int)$_GET['approve'], SQLITE3_INTEGER);
        $filing = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        $subject = "你的备案已通过审核 - " . ($settings['site_title'] ?? '');
        $body = "<h2>备案审核通过</h2><p>你的网站 <strong>" . htmlspecialchars($filing['website_name']) . "</strong> 的备案申请已通过审核。</p><p>备案号：联bBb盟 icp备" . htmlspecialchars($filing['filing_number']) . "</p><p>请将以下代码添加到你的网站页脚：</p><pre><a href='" . htmlspecialchars($settings['site_url']) . "/query.php?keyword=" . htmlspecialchars($filing['filing_number']) . "' target='_blank'>联bBb盟 icp备" . htmlspecialchars($filing['filing_number']) . "</a></pre>";
        if (!sendMail($filing['contact_email'], $subject, $body)) {
            $mail_error = "邮件发送失败，请手动通知用户";
            error_log("批准备案后邮件发送失败: " . $filing['contact_email']);
        }
        $db->exec('COMMIT;');
        header("Location: admin.php");
        exit;
    } catch (Exception $e) {
        $db->exec('ROLLBACK;');
        error_log("批准备案失败: " . $e->getMessage());
        echo '<script>alert("批准备案失败，请稍后再试！"); window.location.href = "admin.php";</script>';
        exit;
    }
}

if (isset($_GET['reject'])) {
    try {
        $db->exec('BEGIN;');
        $stmt = $db->prepare("UPDATE filings SET status = 'rejected' WHERE id = :id");
        $stmt->bindValue(':id', (int)$_GET['reject'], SQLITE3_INTEGER);
        $stmt->execute();
        $stmt = $db->prepare("SELECT * FROM filings WHERE id = :id");
        $stmt->bindValue(':id', (int)$_GET['reject'], SQLITE3_INTEGER);
        $filing = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        $subject = "你的备案未通过审核 - " . ($settings['site_title'] ?? '');
        $body = "<h2>备案审核未通过</h2><p>你的网站 <strong>" . htmlspecialchars($filing['website_name']) . "</strong> 的备案申请未通过审核。</p><p>请检查信息后重新提交。</p>";
        if (!sendMail($filing['contact_email'], $subject, $body)) {
            $mail_error = "邮件发送失败，请手动通知用户";
            error_log("拒绝备案后邮件发送失败: " . $filing['contact_email']);
        }
        $db->exec('COMMIT;');
        header("Location: admin.php");
        exit;
    } catch (Exception $e) {
        $db->exec('ROLLBACK;');
        error_log("拒绝备案失败: " . $e->getMessage());
        echo '<script>alert("拒绝备案失败，请稍后再试！"); window.location.href = "admin.php";</script>';
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_username'])) {
    if ($_POST['new_password'] === $_POST['confirm_password']) {
        try {
            $stmt = $db->prepare("UPDATE admins SET username = :username, password = :password, force_reset = 0 WHERE id = :id");
            $stmt->bindValue(':username', htmlspecialchars($_POST['new_username']), SQLITE3_TEXT);
            $stmt->bindValue(':password', password_hash($_POST['new_password'], PASSWORD_DEFAULT), SQLITE3_TEXT);
            $stmt->bindValue(':id', $_SESSION['admin_id'], SQLITE3_INTEGER);
            $stmt->execute();
            $update_message = "账户信息已更新，请重新登录！";
            session_destroy();
            echo '<script>alert("账户信息已更新，请重新登录！"); window.location.href = "login.php";</script>';
            exit;
        } catch (Exception $e) {
            error_log("更新管理员账户失败: " . $e->getMessage());
            $update_error = "更新账户信息失败，请稍后再试！";
        }
    } else {
        $update_error = "两次输入的密码不一致！";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['site_title'])) {
    try {
        $stmt = $db->prepare("UPDATE settings SET site_title = :site_title, site_url = :site_url, welcome_message = :welcome_message, contact_email = :contact_email, qq_group = :qq_group, smtp_host = :smtp_host, smtp_port = :smtp_port, smtp_username = :smtp_username, smtp_password = :smtp_password, smtp_secure = :smtp_secure, background_image = :background_image WHERE id = 1");
        $stmt->bindValue(':site_title', htmlspecialchars($_POST['site_title']), SQLITE3_TEXT);
        $stmt->bindValue(':site_url', htmlspecialchars($_POST['site_url']), SQLITE3_TEXT);
        $stmt->bindValue(':welcome_message', htmlspecialchars($_POST['welcome_message']), SQLITE3_TEXT);
        $stmt->bindValue(':contact_email', htmlspecialchars($_POST['contact_email']), SQLITE3_TEXT);
        $stmt->bindValue(':qq_group', htmlspecialchars($_POST['qq_group']), SQLITE3_TEXT);
        $stmt->bindValue(':smtp_host', htmlspecialchars($_POST['smtp_host']), SQLITE3_TEXT);
        $stmt->bindValue(':smtp_port', (int)$_POST['smtp_port'], SQLITE3_INTEGER);
        $stmt->bindValue(':smtp_username', htmlspecialchars($_POST['smtp_username']), SQLITE3_TEXT);
        $stmt->bindValue(':smtp_password', htmlspecialchars($_POST['smtp_password']), SQLITE3_TEXT);
        $stmt->bindValue(':smtp_secure', htmlspecialchars($_POST['smtp_secure']), SQLITE3_TEXT);
        $stmt->bindValue(':background_image', htmlspecialchars($_POST['background_image']), SQLITE3_TEXT);
        $stmt->execute();
        $settings_update_message = "站点设置已更新！";
        $settings = $db->querySingle("SELECT * FROM settings", true);
    } catch (Exception $e) {
        error_log("更新站点设置失败: " . $e->getMessage());
        $settings_update_message = "更新站点设置失败，请稍后再试！";
    }
}

// 检查更新逻辑（仅在 action=check_update 或 AJAX 调用时执行）
$versions = [];
$update_error = null;
if (isset($_GET['action']) && $_GET['action'] == 'check_update') {
    ob_start(); // 开始输出缓冲，防止意外输出
    $json_url = UPDATE_JSON_URL;
    $ch = curl_init($json_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $json_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // 记录日志
    $log_message = date('Y-m-d H:i:s') . " - 尝试访问 $json_url. HTTP 状态码: $http_code";
    if ($curl_error) {
        $log_message .= " | 错误: $curl_error";
    }
    if ($json_data && $http_code == 200) {
        $log_message .= " | 响应前100字符: " . substr($json_data, 0, 100);
    }
    file_put_contents('update_errors.log', $log_message . PHP_EOL, FILE_APPEND);

    if ($json_data && $http_code == 200) {
        $update_info = json_decode($json_data, true);
        if (is_array($update_info) && isset($update_info['versions']) && is_array($update_info['versions']) && !empty($update_info['versions'])) {
            $versions = $update_info['versions'];
            $current_version = APP_VERSION;
            $latest_version = $versions[0]['version'];
            $current_version_clean = preg_replace('/^v/', '', $current_version);
            $latest_version_clean = preg_replace('/^v/', '', $latest_version);
            $update_available = version_compare($latest_version_clean, $current_version_clean, '>');
            file_put_contents('update_errors.log', date('Y-m-d H:i:s') . " - 当前版本: $current_version, 最新版本: $latest_version, 是否可更新: " . ($update_available ? '是' : '否') . PHP_EOL, FILE_APPEND);

            // 如果是 AJAX 请求，返回 JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                ob_clean(); // 清理缓冲区，确保无意外输出
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'versions' => $versions,
                    'current_version' => $current_version,
                    'latest_version' => $latest_version,
                    'update_available' => $update_available
                ]);
                exit;
            }
        } else {
            $update_error = "无法解析更新信息: JSON 格式无效或 versions 数组为空";
            file_put_contents('update_errors.log', date('Y-m-d H:i:s') . " - JSON 解析错误: " . json_last_error_msg() . PHP_EOL, FILE_APPEND);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $update_error]);
                exit;
            }
        }
    } else {
        $update_error = "无法获取更新信息: HTTP 状态码 $http_code";
        if ($curl_error) {
            $update_error .= " (cURL 错误: $curl_error)";
        }
        if ($http_code == 403) {
            $update_error .= "（可能是权限问题，请检查服务器配置或提供 API 密钥）";
        }
        file_put_contents('update_errors.log', date('Y-m-d H:i:s') . " - 获取失败: $update_error" . PHP_EOL, FILE_APPEND);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $update_error]);
            exit;
        }
    }
    ob_end_clean(); // 清理缓冲区
}

if (isset($_GET['update_success'])) {
    $update_success_message = "更新成功！";
} elseif (isset($_GET['update_error'])) {
    $update_error_message = "更新失败，错误代码：{$_GET['update_error']}";
}

$results = $db->query('SELECT *, COALESCE(is_healthy, 1) as is_healthy, last_check_time FROM filings ORDER BY submission_date DESC');
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name="description" content="管理员面板 - <?php echo htmlspecialchars($settings['site_title']); ?>">
    <meta name="keywords" content="管理员面板, ICP备案, <?php echo htmlspecialchars($settings['site_title']); ?>">
    <title>后台管理 - <?php echo htmlspecialchars($settings['site_title']); ?></title>
    <link rel="icon" href="https://www.dmoe.cc/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <style>
        .modal-content {
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .modal-content h2 {
            color: #fff;
            text-align: center;
            text-shadow: 0 0 10px #0ff;
            margin-bottom: 20px;
        }
        .neon-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .basic-settings, .smtp-settings {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .basic-settings legend, .smtp-settings legend {
            color: #0ff;
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 0 0 5px #0ff;
        }
        .neon-form button {
            grid-column: span 2;
            margin-top: 10px;
        }
        .version-list {
            list-style: none;
            padding: 0;
            margin: 10px 0;
        }
        .version-item {
            border-bottom: 1px solid #0ff;
            padding: 10px 0;
        }
        .version-item:last-child {
            border-bottom: none;
        }
        .version-item p {
            margin: 5px 0;
        }
        .current-version {
            color: #ffd700;
            font-weight: bold;
        }
        .update-link {
            color: #2ecc71;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .update-link:hover {
            text-decoration: underline;
        }
        .check-update-link {
            color: #ffd700;
            text-decoration: none;
            margin-left: 10px;
        }
        .check-update-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .modal-content {
                max-width: 90%;
                margin: 0 10px;
            }
            .neon-form {
                grid-template-columns: 1fr;
            }
            .neon-form button {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container">
        <div class="header">
            <h1 class="holographic-text">后台管理</h1>
            <p>欢迎，管理员！ 
                <a href="#" class="modify-link" onclick="document.getElementById('modifyModal').style.display='flex'">修改账户</a>
                <a href="#" class="modify-link" onclick="document.getElementById('settingsModal').style.display='flex'">站点设置</a>
                <a href="admin.php?action=statistics" class="modify-link">数据统计</a>
                <a href="#" class="modify-link" onclick="document.getElementById('githubModal').style.display='flex'">初始版本</a>
                <a href="logout.php" class="logout-link">退出登录</a>
                <a href="#" class="check-update-link" onclick="showCheckUpdateModal()">检查更新</a>
            </p>
            <?php if (isset($settings_update_message)) echo "<p class='success'>$settings_update_message</p>"; ?>
            <?php if (isset($mail_error)) echo "<p class='error'>$mail_error</p>"; ?>
            <?php if (isset($update_message)) echo "<p class='success'>$update_message</p>"; ?>
            <?php if (isset($update_error)) echo "<p class='error'>$update_error</p>"; ?>
            <?php if (isset($update_success_message)) echo "<p class='success'>$update_success_message</p>"; ?>
            <?php if (isset($update_error_message)) echo "<p class='error'>$update_error_message</p>"; ?>
        </div>
        <?php if (isset($_GET['action']) && $_GET['action'] == 'statistics'): ?>
            <?php include 'admin_statistics.php'; ?>
        <?php else: ?>
            <div class="table-wrapper card-effect">
                <table>
                    <thead>
                        <tr>
                            <th data-label="ID">ID</th>
                            <th data-label="备案号">备案号</th>
                            <th data-label="网站名称">网站名称</th>
                            <th data-label="地址">地址</th>
                            <th data-label="描述">描述</th>
                            <th data-label="邮箱">邮箱</th>
                            <th data-label="提交时间">提交时间</th>
                            <th data-label="状态">状态</th>
                            <th data-label="健康状态">健康状态</th>
                            <th data-label="上次检查时间">上次检查时间</th>
                            <th data-label="操作">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
                            <tr>
                                <td data-label="ID"><?php echo $row['id']; ?></td>
                                <td data-label="备案号">联bBb盟 icp备<?php echo $row['filing_number']; ?></td>
                                <td data-label="网站名称"><?php echo htmlspecialchars($row['website_name']); ?></td>
                                <td data-label="地址"><a href="<?php echo htmlspecialchars($row['website_url']); ?>" target="_blank"><?php echo htmlspecialchars($row['website_url']); ?></a></td>
                                <td data-label="描述"><?php echo htmlspecialchars($row['description']); ?></td>
                                <td data-label="邮箱"><?php echo htmlspecialchars($row['contact_email']); ?></td>
                                <td data-label="提交时间"><?php echo htmlspecialchars($row['submission_date']); ?></td>
                                <td data-label="状态"><?php echo $row['status'] == 'pending' ? '待审核' : ($row['status'] == 'approved' ? '已通过' : '已拒绝'); ?></td>
                                <td data-label="健康状态">
                                    <?php
                                        if ($row['is_healthy'] == 1) {
                                            echo '<span style="color: green;">正常</span>';
                                        } else {
                                            echo '<span style="color: red;">异常</span>';
                                        }
                                    ?>
                                </td>
                                <td data-label="上次检查时间">
                                    <?php echo $row['last_check_time'] ? htmlspecialchars($row['last_check_time']) : 'N/A'; ?>
                                </td>
                                <td data-label="操作">
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <a href="admin.php?approve=<?php echo $row['id']; ?>" class="approve-link">通过</a> |
                                         <a href="admin.php?reject=<?php echo $row['id']; ?>" class="reject-link">拒绝</a> |
                                    <?php elseif ($row['status'] == 'approved'): ?>
                                        <a href="generate_certificate.php?filing_id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-success btn-sm">查看证书</a> |
                                        <a href="admin.php?reject=<?php echo $row['id']; ?>" class="reject-link">拒绝</a> |
                                        <a href="#" class="btn btn-info btn-sm check-health-btn" data-id="<?php echo $row['id']; ?>">健康检查</a> |
                                    <?php endif; ?>
                                     <a href="admin.php?delete=<?php echo $row['id']; ?>" class="delete-link" onclick="return confirm('确定删除吗？');">删除</a>


                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <div class="footer">
        <?php echo getFooterText(); ?>
        <a href="<?php echo htmlspecialchars($settings['site_url']); ?>/query.php?keyword=20240001" target="_blank">联bBb盟 icp备20240001号</a>
    </div>
    <div id="modifyModal" class="modal" style="display: none;">
        <div class="modal-content card-effect">
            <span class="close" onclick="document.getElementById('modifyModal').style.display='none'">×</span>
            <h2>修改账户信息</h2>
            <?php if (isset($update_message)) echo "<p class='success'>$update_message</p>"; ?>
            <?php if (isset($update_error)) echo "<p class='error'>$update_error</p>"; ?>
            <form action="admin.php" method="POST" class="neon-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="text" name="new_username" class="search-input" placeholder="请输入新用户名" required>
                <input type="password" name="new_password" class="search-input" placeholder="请输入新密码" required>
                <input type="password" name="confirm_password" class="search-input" placeholder="请再次输入新密码" required>
                <button type="submit" class="search-button glow-button">
                    <span>更新账户</span>
                    <div class="glow"></div>
                </button>
            </form>
        </div>
    </div>
    <div id="settingsModal" class="modal" style="display: none;">
        <div class="modal-content card-effect">
            <span class="close" onclick="document.getElementById('settingsModal').style.display='none'">×</span>
            <h2>站点设置</h2>
            <form action="admin.php" method="POST" class="neon-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <fieldset class="basic-settings">
                    <legend>基础设置</legend>
                    <input type="text" name="site_title" class="search-input" value="<?php echo htmlspecialchars($settings['site_title']); ?>" placeholder="请输入站点标题（如：联bBb盟 ICP 备案系统）" required>
                    <input type="url" name="site_url" class="search-input" value="<?php echo htmlspecialchars($settings['site_url']); ?>" placeholder="请输入站点URL（如：https://icp.bbb-lsy07.my）" required>
                    <textarea name="welcome_message" class="search-input" placeholder="请输入欢迎信息（如：这是一个虚拟备案系统）" required><?php echo htmlspecialchars($settings['welcome_message']); ?></textarea>
                    <input type="email" name="contact_email" class="search-input" value="<?php echo htmlspecialchars($settings['contact_email']); ?>" placeholder="请输入联系邮箱（如：admin@bbb-lsy07.my）" required>
                    <input type="text" name="qq_group" class="search-input" value="<?php echo htmlspecialchars($settings['qq_group']); ?>" placeholder="请输入QQ群号（如：123456789）" required>
                    <input type="url" name="background_image" class="search-input" value="<?php echo htmlspecialchars($settings['background_image']); ?>" placeholder="请输入背景图URL（如：https://www.dmoe.cc/random.php）" required>
                </fieldset>
                <fieldset class="smtp-settings">
                    <legend>SMTP 设置</legend>
                    <input type="text" name="smtp_host" class="search-input" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>" placeholder="SMTP 服务器（如：smtp.gmail.com）">
                    <input type="number" name="smtp_port" class="search-input" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? ''); ?>" placeholder="SMTP 端口（如：587）">
                    <input type="text" name="smtp_username" class="search-input" value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>" placeholder="SMTP 用户名（如：your.email@gmail.com）">
                    <input type="password" name="smtp_password" class="search-input" value="<?php echo htmlspecialchars($settings['smtp_password'] ?? ''); ?>" placeholder="SMTP 密码">
                    <select name="smtp_secure" class="search-input">
                        <option value="tls" <?php if (($settings['smtp_secure'] ?? 'tls') == 'tls') echo 'selected'; ?>>TLS</option>
                        <option value="ssl" <?php if (($settings['smtp_secure'] ?? 'tls') == 'ssl') echo 'selected'; ?>>SSL</option>
                    </select>
                </fieldset>
                <button type="submit" class="search-button glow-button">
                    <span>保存设置</span>
                    <div class="glow"></div>
                </button>
            </form>
        </div>
    </div>
    <div id="githubModal" class="modal" style="display: none;">
        <div class="modal-content card-effect">
            <span class="close" onclick="document.getElementById('githubModal').style.display='none'">×</span>
            <h2>初始版本</h2>
            <form id="update-form" class="neon-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <button type="submit" class="search-button glow-button" id="update-button">
                    <span>从GitHub获取</span>
                    <div class="glow"></div>
                </button>
            </form>
            <progress id="update-progress" max="100" value="0" style="width: 100%; height: 20px; margin-top: 10px;"></progress>
            <div id="update-status" class="result"></div>
        </div>
    </div>
    <div id="checkUpdateModal" class="modal" style="display: none;">
        <div class="modal-content card-effect">
            <span class="close" onclick="document.getElementById('checkUpdateModal').style.display='none'">×</span>
            <h2>检查更新</h2>
            <div id="version-list" class="version-list">
                <p>正在加载版本信息...</p>
            </div>
        </div>
    </div>
    <script>
        function showCheckUpdateModal() {
            const modal = document.getElementById('checkUpdateModal');
            const versionList = document.getElementById('version-list');
            modal.style.display = 'flex';
            versionList.innerHTML = '<p>正在加载版本信息...</p>';

            fetch('admin.php?action=check_update', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP 错误，状态码: ${response.status}`);
                }
                return response.text(); // 先获取原始文本
            })
            .then(text => {
                // 记录响应内容到控制台，便于调试
                console.log('AJAX 响应内容:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        let html = `<p>当前版本：${data.current_version} ${data.current_version === data.latest_version ? '<span class="current-version">(最新版本)</span>' : ''}</p>`;
                        html += '<ul class="version-list">';
                        data.versions.forEach(version => {
                            html += `
                                <li class="version-item">
                                    <p><strong>版本：${version.version}</strong> ${version.version === data.current_version ? '<span class="current-version">(当前)</span>' : ''}</p>
                                    <p>更新日志：${version.changelog || '无描述'}</p>
                                    <p>PHP版本要求：${version.php_version || '未知'}</p>
                                    <p>更新类型：${version.update_type || '未知'}</p>
                                    <p>创建时间：${version.created_at || '未知'}</p>
                                    ${version.version !== data.current_version ? `<a href="process_update.php?action=update&version=${encodeURIComponent(version.version)}" class="update-link search-button glow-button"><span>安装此版本</span><div class="glow"></div></a>` : ''}
                                </li>
                            `;
                        });
                        html += '</ul>';
                        versionList.innerHTML = html;
                    } else {
                        versionList.innerHTML = `<p class="error">加载版本信息失败：${data.error}</p>`;
                    }
                } catch (error) {
                    // 记录解析错误的响应内容
                    console.error('JSON 解析错误:', error, '响应内容:', text);
                    versionList.innerHTML = `<p class="error">加载版本信息失败：${error.message} (响应内容: ${text.substring(0, 100)}...)</p>`;
                }
            })
            .catch(error => {
                console.error('AJAX 请求失败:', error);
                versionList.innerHTML = `<p class="error">加载版本信息失败：${error.message}</p>`;
            });
        }

        window.onclick = function(event) {
            ['modifyModal', 'settingsModal', 'githubModal', 'checkUpdateModal'].forEach(id => {
                let modal = document.getElementById(id);
                if (event.target == modal) modal.style.display = "none";
            });
        };

        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.body.classList.add('loaded');
                const container = document.querySelector('.container');
                const header = document.querySelector('.header');
                const tableWrapper = document.querySelector('.table-wrapper');
                function adjustContainerHeight() {
                    const headerHeight = header.offsetHeight;
                    const windowHeight = window.innerHeight;
                    const footerHeight = document.querySelector('.footer').offsetHeight;
                    const availableHeight = windowHeight - footerHeight - 40;
                    container.style.minHeight = `${availableHeight}px`;
                    tableWrapper.style.maxHeight = `${availableHeight - headerHeight - 60}px`;
                }
                adjustContainerHeight();
                window.addEventListener('resize', adjustContainerHeight);

                // Manual Health Check via AJAX
                document.querySelectorAll('.check-health-btn').forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const filingId = this.dataset.id;
                        const healthStatusCell = this.closest('tr').querySelector('td[data-label="健康状态"]');
                        const lastCheckTimeCell = this.closest('tr').querySelector('td[data-label="上次检查时间"]');

                        healthStatusCell.innerHTML = '<span style="color: gray;">检查中...</span>';
                        lastCheckTimeCell.textContent = '更新中...';

                        fetch('process_health_check.php?filing_id=' + filingId, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                if (data.is_healthy == 1) {
                                    healthStatusCell.innerHTML = '<span style="color: green;">正常</span>';
                                } else {
                                    healthStatusCell.innerHTML = '<span style="color: red;">异常</span>';
                                }
                                lastCheckTimeCell.textContent = data.last_check_time;
                            } else {
                                healthStatusCell.innerHTML = '<span style="color: orange;">检查失败</span>';
                                lastCheckTimeCell.textContent = 'N/A';
                                console.error('健康检查失败:', data.message);
                            }
                        })
                        .catch(error => {
                            healthStatusCell.innerHTML = '<span style="color: red;">网络错误</span>';
                            lastCheckTimeCell.textContent = 'N/A';
                            console.error('健康检查请求错误:', error);
                        });
                    });
                });

                const form = document.getElementById('update-form');
                const button = document.getElementById('update-button');
                const progress = document.getElementById('update-progress');
                const status = document.getElementById('update-status');
                let source = null;

                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    if (source) {
                        source.close();
                        source = null;
                    }
                    button.disabled = true;
                    button.querySelector('span').textContent = '更新中...';
                    progress.value = 0;
                    status.textContent = '开始更新...';
                    status.className = 'result';

                    const formData = new FormData(form);
                    const csrfToken = formData.get('csrf_token');
                    console.log('发送CSRF Token:', csrfToken);

                    source = new EventSource(`process_update.php?csrf_token=${encodeURIComponent(csrfToken)}`);

                    source.onmessage = (event) => {
                        console.log('收到SSE消息:', event.data);
                        try {
                            const data = JSON.parse(event.data);
                            progress.value = data.progress;
                            status.textContent = data.message;
                            status.className = data.error ? 'error' : 'result';
                            if (data.progress === 100 && !data.error) {
                                status.className = 'success';
                                status.textContent = data.message || '更新成功！';
                                button.disabled = false;
                                button.querySelector('span').textContent = '从GitHub更新';
                                setTimeout(() => {
                                    document.getElementById('githubModal').style.display = 'none';
                                    window.location.reload();
                                }, 1000);
                                source.close();
                            } else if (data.error) {
                                status.className = 'error';
                                status.textContent = '更新失败：' + data.message;
                                button.disabled = false;
                                button.querySelector('span').textContent = '从GitHub更新';
                                source.close();
                            }
                        } catch (err) {
                            console.error('解析SSE数据失败:', err);
                            status.className = 'error';
                            status.textContent = '解析更新数据失败：' + err.message;
                            button.disabled = false;
                            button.querySelector('span').textContent = '从GitHub更新';
                            source.close();
                        }
                    };

                    source.onerror = () => {
                        console.error('SSE连接错误');
                        status.className = 'error';
                        status.textContent = '更新连接中断，请检查网络后重试';
                        button.disabled = false;
                        button.querySelector('span').textContent = '从GitHub更新';
                        source.close();
                    };

                    source.onopen = () => {
                        console.log('SSE连接已建立');
                    };
                });
            }, 50);
        });
    </script>
</body>
</html>