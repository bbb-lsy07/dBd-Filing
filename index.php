<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>联bBb盟 ICP 备案系统</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container">
        <h1>联bBb盟 ICP 备案系统</h1>
        <p class="note">这是一个虚拟备案系统，仅供娱乐和社区互动使用，非官方备案。</p>
        
        <form action="process.php" method="POST">
            <div class="form-group">
                <label for="website_name">网站名称</label>
                <input type="text" id="website_name" name="website_name" required>
            </div>
            
            <div class="form-group">
                <label for="website_url">网站地址</label>
                <input type="url" id="website_url" name="website_url" required>
            </div>
            
            <div class="form-group">
                <label for="description">网站描述</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="contact_email">联系邮箱</label>
                <input type="email" id="contact_email" name="contact_email" required>
            </div>
            
            <button type="submit">提交备案</button>
        </form>
        
        <div class="links">
            <a href="query.php" class="query-link">查询备案信息</a>
            <a href="public.php" class="query-link">查看公示</a>
        </div>
    </div>
</body>
</html>