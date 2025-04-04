<?php
$db = new SQLite3('database.sqlite');

// 检查并更新表结构
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

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $website_name = htmlspecialchars($_POST['website_name']);
    $website_url = htmlspecialchars($_POST['website_url']);
    $description = htmlspecialchars($_POST['description']);
    $contact_email = htmlspecialchars($_POST['contact_email']);
    
    // 生成备案号（年份 + 随机4位数）
    $year = date('Y');
    $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $filing_number = $year . $random;
    
    // 插入数据，初始状态为待审
    $stmt = $db->prepare("INSERT INTO filings (filing_number, website_name, website_url, description, contact_email, submission_date, status) 
                          VALUES (:filing_number, :website_name, :website_url, :description, :contact_email, :submission_date, 'pending')");
    $stmt->bindValue(':filing_number', $filing_number, SQLITE3_TEXT);
    $stmt->bindValue(':website_name', $website_name, SQLITE3_TEXT);
    $stmt->bindValue(':website_url', $website_url, SQLITE3_TEXT);
    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
    $stmt->bindValue(':contact_email', $contact_email, SQLITE3_TEXT);
    $stmt->bindValue(':submission_date', date('Y-m-d H:i:s'), SQLITE3_TEXT);
    $stmt->execute();
    
    // 生成展示代码
    $display_number = "联bBb盟 icp备" . $filing_number;
    $code = "<a href='https://icp.bbb-lsy07.my/query.php?keyword=$filing_number'>$display_number</a>";
    
    // 显示结果
    echo "<!DOCTYPE html><html lang='zh-CN'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><link rel='stylesheet' href='style.css'></head><body>";
    echo "<div class='github-corner'><a href='https://github.com/bbb-lsy07/dBd-Filing' target='_blank' class='github-link'>开源地址</a></div>";
    echo "<div class='container'><h1>备案申请成功</h1>";
    echo "<p>您的备案号是：<strong>$filing_number</strong></p>";
    echo "<p>状态：待审核（通过后将在公示页面显示）</p>";
    echo "<p>请将以下代码添加到您的网站页脚：</p>";
    echo "<pre>" . htmlspecialchars($code) . "</pre>";
    echo "<textarea id='filingCode' style='display:none;'>$code</textarea>";
    echo "<button onclick='copyCode()'>一键复制</button>";
    echo "<script>
            function copyCode() {
                var code = document.getElementById('filingCode').value;
                navigator.clipboard.writeText(code).then(() => alert('已复制到剪贴板！'));
            }
          </script>";
    echo "<div class='links'><a href='index.php' class='back-link'>返回首页</a></div></div></body></html>";
    exit;
}
?>