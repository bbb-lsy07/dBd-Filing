<?php
require_once 'common.php';
require_once 'config.php';
require_once 'send_mail.php';

$db = init_database();

// 获取所有已批准的备案网站
$filings = $db->query('SELECT id, website_url, website_name, contact_email FROM filings WHERE status = "approved"');

while ($row = $filings->fetchArray(SQLITE3_ASSOC)) {
    $filing_id = $row['id'];
    $website_url = $row['website_url'];
    $website_name = $row['website_name'];
    $contact_email = $row['contact_email'];

    $is_healthy = 1; // 假设网站健康
    $status_code = 0;
    $error_message = '';

    // 检查网站健康状态
    $ch = curl_init($website_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 忽略SSL证书验证，实际生产环境应谨慎
    curl_setopt($ch, CURLOPT_HEADER, true); // 获取响应头
    curl_setopt($ch, CURLOPT_NOBODY, true); // 不下载响应体

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $http_code >= 400 || $http_code === 0) {
        $is_healthy = 0; // 网站不健康
        $status_code = $http_code;
        $error_message = $curl_error ?: "HTTP Status Code: {$http_code}";
        error_log("网站健康检查失败: {$website_name} ({$website_url}) - {$error_message}");

        // 发送邮件通知备案人
        $subject = "你的备案网站健康检查异常 - " . ($settings['site_title'] ?? '');
        $body = "<h2>网站健康检查异常</h2><p>你的网站 <strong>" . htmlspecialchars($website_name) . "</strong> (" . htmlspecialchars($website_url) . ") 在最近的健康检查中发现异常。</p><p>状态码: {$status_code}</p><p>错误信息: " . htmlspecialchars($error_message) . "</p><p>请尽快检查你的网站。</p>";
        if (!sendMail($contact_email, $subject, $body)) {
            error_log("发送网站健康异常邮件失败: {$contact_email}");
        }
    }

}

// 在循环外部准备一次语句
$stmt = $db->prepare("UPDATE filings SET is_healthy = :is_healthy, last_check_status = :last_check_status, last_check_time = :last_check_time WHERE id = :id");

while ($row = $filings->fetchArray(SQLITE3_ASSOC)) {
    $filing_id = $row['id'];
    $website_url = $row['website_url'];
    $website_name = $row['website_name'];
    $contact_email = $row['contact_email'];

    $is_healthy = 1; // 假设网站健康
    $status_code = 0;
    $error_message = '';

    // 检查网站健康状态
    $ch = curl_init($website_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 忽略SSL证书验证，实际生产环境应谨慎
    curl_setopt($ch, CURLOPT_HEADER, true); // 获取响应头
    curl_setopt($ch, CURLOPT_NOBODY, true); // 不下载响应体

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $http_code >= 400 || $http_code === 0) {
        $is_healthy = 0; // 网站不健康
        $status_code = $http_code;
        $error_message = $curl_error ?: "HTTP Status Code: {$http_code}";
        error_log("网站健康检查失败: {$website_name} ({$website_url}) - {$error_message}");

        // 发送邮件通知备案人
        $subject = "你的备案网站健康检查异常 - " . ($settings['site_title'] ?? '');
        $body = "<h2>网站健康检查异常</h2><p>你的网站 <strong>" . htmlspecialchars($website_name) . "</strong> (" . htmlspecialchars($website_url) . ") 在最近的健康检查中发现异常。</p><p>状态码: {$status_code}</p><p>错误信息: " . htmlspecialchars($error_message) . "</p><p>请尽快检查你的网站。</p>";
        if (!sendMail($contact_email, $subject, $body)) {
            error_log("发送网站健康异常邮件失败: {$contact_email}");
        }
    }

    // 绑定参数并执行
    $stmt->bindValue(':is_healthy', $is_healthy, SQLITE3_INTEGER);
    $stmt->bindValue(':last_check_status', $status_code, SQLITE3_INTEGER);
    $stmt->bindValue(':last_check_time', date('Y-m-d H:i:s'), SQLITE3_TEXT);
    $stmt->bindValue(':id', $filing_id, SQLITE3_INTEGER);
    $stmt->execute();
}

echo "网站健康检查完成。";
?>