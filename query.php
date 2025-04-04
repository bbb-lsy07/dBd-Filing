<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>查询备案信息 - 联bBb盟 ICP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container">
        <h1>查询备案信息</h1>
        <form action="query.php" method="GET">
            <div class="form-group">
                <label for="keyword">输入备案号或网站地址</label>
                <input type="text" id="keyword" name="keyword" required>
            </div>
            <button type="submit">查询</button>
        </form>
        
        <?php
        if (isset($_GET['keyword'])) {
            $keyword = htmlspecialchars($_GET['keyword']);
            $db = new SQLite3('database.sqlite');
            
            $stmt = $db->prepare("SELECT * FROM filings WHERE filing_number = :keyword OR website_url = :keyword");
            $stmt->bindValue(':keyword', $keyword, SQLITE3_TEXT);
            $result = $stmt->execute();
            
            if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $email = $row['contact_email'];
                $email_parts = explode('@', $email);
                $email_local = substr($email_parts[0], 0, 3) . str_repeat('*', strlen($email_parts[0]) - 3);
                $masked_email = $email_local . '@' . $email_parts[1];
                
                echo "<div class='result'>";
                echo "<h2>备案信息</h2>";
                echo "<p><strong>备案号：</strong> 联bBb盟 icp备{$row['filing_number']}</p>";
                echo "<p><strong>网站名称：</strong> {$row['website_name']}</p>";
                echo "<p><strong>网站地址：</strong> <a href='{$row['website_url']}' target='_blank'>{$row['website_url']}</a></p>";
                echo "<p><strong>描述：</strong> {$row['description']}</p>";
                echo "<p><strong>联系邮箱：</strong> {$masked_email}</p>";
                echo "<p><strong>提交时间：</strong> {$row['submission_date']}</p>";
                echo "<p><strong>状态：</strong> " . ($row['status'] == 'pending' ? '待审核' : ($row['status'] == 'approved' ? '已通过' : '已拒绝')) . "</p>";
                echo "</div>";
            } else {
                echo "<p class='error'>未找到相关备案信息。</p>";
            }
        }
        ?>
        <div class="links">
            <a href="index.php" class="back-link">返回首页</a>
        </div>
    </div>
</body>
</html>