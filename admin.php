<?php
session_start();
require_once 'common.php';
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
    'background_image' => 'https://www.dmoe.cc/random.php'
];
require_once 'send_mail.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM filings WHERE id = :id");
    $stmt->bindValue(':id', (int)$_GET['delete'], SQLITE3_INTEGER);
    $stmt->execute();
    header("Location: admin.php");
    exit;
}

if (isset($_GET['approve'])) {
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
    }
    header("Location: admin.php");
    exit;
}

if (isset($_GET['reject'])) {
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
    }
    header("Location: admin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_username'])) {
    if ($_POST['new_password'] === $_POST['confirm_password']) {
        $stmt = $db->prepare("UPDATE admins SET username = :username, password = :password, force_reset = 0 WHERE id = :id");
        $stmt->bindValue(':username', htmlspecialchars($_POST['new_username']), SQLITE3_TEXT);
        $stmt->bindValue(':password', password_hash($_POST['new_password'], PASSWORD_DEFAULT), SQLITE3_TEXT);
        $stmt->bindValue(':id', $_SESSION['admin_id'], SQLITE3_INTEGER);
        $stmt->execute();
        $update_message = "账户信息已更新，请重新登录！";
        session_destroy();
        echo '<script>alert("账户信息已更新，请重新登录！"); window.location.href = "login.php";</script>';
        exit;
    } else {
        $update_error = "两次输入的密码不一致！";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['site_title'])) {
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
}

$results = $db->query("SELECT * FROM filings ORDER BY submission_date DESC");
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
            max-width: 800px; /* 与.neon-form的max-width一致 */
            max-height: 80vh; /* 最大高度限制 */
            overflow-y: auto; /* 内容超出时可滚动 */
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
            grid-template-columns: 1fr 1fr; /* 左右布局 */
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
            grid-column: span 2; /* 按钮跨两列 */
            margin-top: 10px;
        }
        @media (max-width: 600px) {
            .modal-content {
                max-width: 90%; /* 移动端宽度自适应 */
                margin: 0 10px;
            }
            .neon-form {
                grid-template-columns: 1fr; /* 移动端垂直布局 */
            }
            .neon-form button {
                grid-column: span 1; /* 按钮适应单列 */
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
                <a href="#" class="modify-link" onclick="document.getElementById('githubModal').style.display='flex'">更新系统</a>
                <a href="logout.php" class="logout-link">退出登录</a>
            </p>
            <?php if (isset($settings_update_message)) echo "<p class='success'>$settings_update_message</p>"; ?>
            <?php if (isset($mail_error)) echo "<p class='error'>$mail_error</p>"; ?>
            <?php if (isset($update_message)) echo "<p class='success'>$update_message</p>"; ?>
            <?php if (isset($update_error)) echo "<p class='error'>$update_error</p>"; ?>
        </div>
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
                            <td data-label="操作">
                                <?php if ($row['status'] == 'pending'): ?>
                                    <a href="admin.php?approve=<?php echo $row['id']; ?>" class="approve-link">通过</a> |
                                    <a href="admin.php?reject=<?php echo $row['id']; ?>" class="reject-link">拒绝</a> |
                                <?php endif; ?>
                                <a href="admin.php?delete=<?php echo $row['id']; ?>" class="delete-link" onclick="return confirm('确定删除吗？');">删除</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
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
            <h2>系统更新</h2>
            <form id="update-form" class="neon-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <button type="submit" class="search-button glow-button" id="update-button">
                    <span>从GitHub更新</span>
                    <div class="glow"></div>
                </button>
            </form>
            <progress id="update-progress" max="100" value="0" style="width: 100%; height: 20px; margin-top: 10px;"></progress>
            <div id="update-status" class="result"></div>
        </div>
    </div>
    <script>
        window.onclick = function(event) {
            ['modifyModal', 'settingsModal', 'githubModal'].forEach(id => {
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