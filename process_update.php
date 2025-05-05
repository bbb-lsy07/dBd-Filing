<?php
session_start();
require_once 'common.php';

if (isset($_GET['action']) && $_GET['action'] == 'update' && isset($_GET['version'])) {
    $version = $_GET['version'];
    $json_url = 'https://admin-hosting-v.bbb-lsy07.my/json/1.json';
    $json_data = file_get_contents($json_url);
    if ($json_data) {
        $update_info = json_decode($json_data, true);
        if ($update_info && isset($update_info['versions'][0])) {
            $latest_version = $update_info['versions'][0]['version'];
            if ($latest_version == $version) {
                $zip_url = $update_info['versions'][0]['file_path'];
                $zip_file = 'update.zip';
                file_put_contents($zip_file, file_get_contents($zip_url));
                $zip = new ZipArchive;
                if ($zip->open($zip_file) === TRUE) {
                    $zip->extractTo('.');
                    $zip->close();
                    unlink($zip_file);
                    $db = init_database();
                    $stmt = $db->prepare("UPDATE settings SET version = :version WHERE id = 1");
                    $stmt->bindValue(':version', $version, SQLITE3_TEXT);
                    $stmt->execute();
                    header("Location: admin.php?update_success=1");
                } else {
                    header("Location: admin.php?update_error=1");
                }
            } else {
                header("Location: admin.php?update_error=2");
            }
        } else {
            header("Location: admin.php?update_error=3");
        }
    } else {
        header("Location: admin.php?update_error=4");
    }
    exit;
}

// 以下为原有的 GitHub 更新逻辑
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

function log_message($message) {
    file_put_contents('update_log.txt', date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
}

log_message('开始执行更新脚本');

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    log_message('未登录');
    echo "data: " . json_encode(['progress' => 0, 'message' => '未登录！', 'error' => true]) . "\n\n";
    exit;
}

if (!isset($_GET['csrf_token'])) {
    log_message('CSRF token未提供');
    echo "data: " . json_encode(['progress' => 0, 'message' => 'CSRF token未提供！', 'error' => true]) . "\n\n";
    exit;
}

if ($_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    log_message('CSRF token验证失败，收到: ' . $_GET['csrf_token'] . ', 期望: ' . $_SESSION['csrf_token']);
    echo "data: " . json_encode(['progress' => 0, 'message' => 'CSRF token验证失败！', 'error' => true]) . "\n\n";
    exit;
}

$db = init_database();
log_message('数据库连接成功');

function send_progress($progress, $message, $error = false) {
    $data = json_encode(['progress' => $progress, 'message' => $message, 'error' => $error]);
    echo "data: $data\n\n";
    log_message("发送进度: $progress% - $message" . ($error ? ' (错误)' : ''));
    ob_flush();
    flush();
}

try {
    send_progress(10, '检查网络连接...');
    $files = ['admin.php', 'common.php', 'travel.php', 'login.php', 'style.css'];
    $total_files = count($files);
    $progress_per_file = 80 / $total_files;

    foreach ($files as $index => $file) {
        send_progress(10 + ($index * $progress_per_file), "下载文件: $file...");
        $url = "https://raw.githubusercontent.com/bbb-lsy07/dBd-Filing/main/$file";
        log_message("尝试下载: $url");
        $content = @file_get_contents($url);
        if ($content === false) {
            log_message("下载失败: $file");
            throw new Exception("无法下载文件: $file");
        }
        log_message("下载成功: $file");
        if (@file_put_contents($file, $content) === false) {
            log_message("写入失败: $file");
            throw new Exception("无法写入文件: $file");
        }
        log_message("写入成功: $file");
        send_progress(10 + (($index + 1) * $progress_per_file), "文件 $file 更新成功");
    }

    send_progress(90, '更新数据库...');
    $db->exec("CREATE TABLE IF NOT EXISTS updates (id INTEGER PRIMARY KEY AUTOINCREMENT, update_time TEXT)");
    $db->exec("INSERT INTO updates (update_time) VALUES (datetime('now'))");
    log_message('数据库更新完成');

    send_progress(100, '更新完成！');
    log_message('更新完成');
} catch (Exception $e) {
    log_message('错误: ' . $e->getMessage());
    send_progress(0, '错误: ' . $e->getMessage(), true);
    exit;
}

echo ": keepalive\n\n";
ob_flush();
flush();
?>