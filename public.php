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

// 查询已通过审核的记录
$results = $db->query("SELECT * FROM filings WHERE status = 'approved' ORDER BY submission_date DESC");
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>公示页面 - 联bBb盟 ICP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container">
        <h1>公示页面</h1>
        <p>以下为已通过审核的备案网站。</p>
        
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>备案号</th>
                        <th>网站名称</th>
                        <th>地址</th>
                        <th>描述</th>
                        <th>提交时间</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($results) {
                        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                            echo "<tr>";
                            echo "<td><a href='query.php?keyword={$row['filing_number']}' class='filing-link'>联bBb盟 icp备{$row['filing_number']}</a></td>";
                            echo "<td>{$row['website_name']}</td>";
                            echo "<td><a href='{$row['website_url']}' target='_blank'>{$row['website_url']}</a></td>";
                            echo "<td>{$row['description']}</td>";
                            echo "<td>{$row['submission_date']}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>暂无通过审核的备案</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <div class="links">
            <a href="index.php" class="back-link">返回首页</a>
        </div>
    </div>
</body>
</html>