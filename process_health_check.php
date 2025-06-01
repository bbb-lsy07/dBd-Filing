<?php
session_start();
require_once 'common.php';
require_once 'config.php';
require_once 'send_mail.php';

$db = init_database();

// 检查是否是管理员登录
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['admin_id']) || (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'admin')) {
    ob_clean(); // 清除所有缓冲区内容
    echo json_encode(['status' => 'error', 'message' => '权限不足，请重新登录！']);
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

if (isset($_GET['filing_id'])) {
    $filing_id = $_GET['filing_id'];

    $filing_to_check = $db->querySingle("SELECT website_url, website_name, contact_email FROM filings WHERE id = {$filing_id} AND status = 'approved'", true);

    if ($filing_to_check) {
        $website_url = $filing_to_check['website_url'];
        $website_name = $filing_to_check['website_name'];
        $contact_email = $filing_to_check['contact_email'];

        $is_healthy = 1; // 假设网站健康
        $status_code = 0;
        $error_message = '';

        $ch = curl_init($website_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        // curl_setopt($ch, CURLOPT_NOBODY, true); // Removed to get full content for better health check


        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $http_code >= 400) {
            $is_healthy = 0;
            $status_code = $http_code;
            $error_message = $curl_error ?: "HTTP Status Code: {$http_code}";
            error_log("手动健康检查失败: {$website_name} ({$website_url}) - {$error_message}");

            $subject = "你的备案网站健康检查异常 - " . ($settings['site_title'] ?? '');
            $body = "<h2>网站健康检查异常</h2><p>你的网站 <strong>" . htmlspecialchars($website_name) . "</strong> (" . htmlspecialchars($website_url) . ") 在手动健康检查中发现异常。</p><p>状态码: {$status_code}</p><p>错误信息: " . htmlspecialchars($error_message) . "</p><p>请尽快检查你的网站。</p>";
            sendMail($contact_email, $subject, $body);
        }

        $stmt = $db->prepare("UPDATE filings SET is_healthy = :is_healthy, last_check_status = :last_check_status, last_check_time = :last_check_time WHERE id = :id");
        $stmt->bindValue(':is_healthy', $is_healthy, SQLITE3_INTEGER);
        $stmt->bindValue(':last_check_status', $status_code, SQLITE3_INTEGER);
        $stmt->bindValue(':last_check_time', date('Y-m-d H:i:s'), SQLITE3_TEXT);
        $stmt->bindValue(':id', $filing_id, SQLITE3_INTEGER);
        $stmt->execute();

        ob_clean(); // 清除所有缓冲区内容
        echo json_encode(['status' => 'success', 'message' => '网站健康检查完成。', 'is_healthy' => $is_healthy, 'last_check_time' => date('Y-m-d H:i:s')]);
    } else {
        ob_clean(); // 清除所有缓冲区内容
    echo json_encode(['status' => 'error', 'message' => '未找到该备案或备案未通过审核。']);
    }
} else {
    ob_clean(); // 清除所有缓冲区内容
echo json_encode(['status' => 'error', 'message' => '缺少备案ID。']);
}
?>