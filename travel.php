<?php
session_start();
require_once 'common.php';
$db = init_database();

$settings = $db->querySingle("SELECT * FROM settings", true);

// Automatic travel logic
$approved_results = $db->query("SELECT * FROM filings WHERE status = 'approved'");
$approved_websites = [];
while ($row = $approved_results->fetchArray(SQLITE3_ASSOC)) {
    $approved_websites[] = $row;
}

$travel_number = '';
$website = null;
$target_name = '';
if (!empty($approved_websites)) {
    $last_index = isset($_SESSION['last_travel_index']) ? $_SESSION['last_travel_index'] : -1;
    $website_index = ($last_index + 1) % count($approved_websites);
    $_SESSION['last_travel_index'] = $website_index;
    $website = $approved_websites[$website_index];

    // Generate travel number
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM travel_logs WHERE website_url = :website_url");
    $stmt->bindValue(':website_url', $website['website_url'], SQLITE3_TEXT);
    $travel_count = $stmt->execute()->fetchArray(SQLITE3_ASSOC)['count'] + 1;
    $travel_number = "EXPLORER-" . str_pad($travel_count, 6, '0', STR_PAD_LEFT);

    // Random target name
    $target_names = ["生活乐开花", "星际家园", "数字宇宙", "未来之城", "梦幻星河"];
    $target_name = $target_names[array_rand($target_names)];

    // Insert travel log
    $stmt = $db->prepare("INSERT INTO travel_logs (travel_number, website_name, website_url, travel_time) VALUES (:travel_number, :website_name, :website_url, :travel_time)");
    $stmt->bindValue(':travel_number', $travel_number, SQLITE3_TEXT);
    $stmt->bindValue(':website_name', $website['website_name'], SQLITE3_TEXT);
    $stmt->bindValue(':website_url', $website['website_url'], SQLITE3_TEXT);
    $stmt->bindValue(':travel_time', date('Y-m-d H:i:s'), SQLITE3_TEXT);
    $stmt->execute();
}

// Fetch recent travel logs
$recent_logs = $db->query("SELECT * FROM travel_logs ORDER BY travel_time DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name="description" content="穿越虚拟 ICP 备案网站，体验星链穿梭冒险。">
    <meta name="keywords" content="ICP 穿越, 虚拟备案, 星链穿梭, <?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>">
    <title>星链穿梭 - <?php echo htmlspecialchars($settings['site_title'] ?? ''); ?></title>
    <link rel="icon" href="https://www.dmoe.cc/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #000428, #004e92);
            background-image: url('<?php echo htmlspecialchars($settings['background_image'] ?? 'https://www.dmoe.cc/random.php'); ?>'), linear-gradient(135deg, #000428, #004e92);
            background-blend-mode: overlay;
            overflow-x: hidden;
            animation: spacePulse 15s infinite alternate;
            position: relative;
        }
        #space-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        @keyframes spacePulse {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }
        .travel-container {
            min-height: 60vh;
            max-height: 80vh;
            overflow-y: auto;
            padding-bottom: 20px;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0, 191, 255, 0.5);
        }
        .travel-info {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 15px;
            border: 1px solid rgba(0, 191, 255, 0.3);
            box-shadow: 0 0 20px rgba(0, 191, 255, 0.3);
            transition: all 0.3s ease;
        }
        .travel-info:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 30px rgba(0, 191, 255, 0.7);
        }
        .table-wrapper {
            max-height: 300px;
            overflow-y: auto;
            margin: 0 auto;
            max-width: 600px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 10px;
        }
        .particle {
            position: fixed;
            width: 2px;
            height: 2px;
            background: #FFFFFF;
            border-radius: 50%;
            animation: float 5s infinite, twinkle 2s infinite alternate;
            left: <?php echo rand(0, 100); ?>%;
            top: <?php echo rand(0, 100); ?>%;
            animation-delay: <?php echo rand(0, 5000); ?>ms;
        }
        @keyframes twinkle {
            0% { opacity: 0.3; }
            100% { opacity: 1; }
        }
        table {
            border-collapse: collapse;
            width: 100%;
            background: rgba(0, 0, 0, 0.3);
        }
        table, th, td {
            border: 1px solid rgba(0, 191, 255, 0.5);
        }
        th {
            background: rgba(0, 191, 255, 0.2);
        }
        tr:hover {
            background: rgba(0, 191, 255, 0.1);
        }
        #countdown {
            color: #00BFFF;
            font-weight: bold;
            animation: pulse 1s infinite alternate;
        }
        @keyframes pulse {
            0% { text-shadow: 0 0 5px #00BFFF; }
            100% { text-shadow: 0 0 15px #00BFFF, 0 0 20px #00BFFF; }
        }
    </style>
</head>
<body class="travel-body">
    <div class="github-corner">
        <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank" class="github-link">开源地址</a>
    </div>
    <div class="container travel-container page-transition">
        <div class="header">
            <h1 class="holographic-text">星链穿梭</h1>
            <p>穿越虚拟 ICP 备案网站，体验星链穿梭冒险！</p>
        </div>
        <?php if (empty($approved_websites)): ?>
            <div class="travel-info card-effect">
                <p>当前无星际站点可穿越，请提交备案以加入星链网络！</p>
            </div>
        <?php else: ?>
            <div class="travel-info card-effect">
                <h2 class="holographic-text"><?php echo htmlspecialchars($website['website_name']); ?></h2>
                <p><strong>探险者编号：</strong> <?php echo htmlspecialchars($travel_number); ?></p>
                <p><strong>传送目标：</strong> <?php echo htmlspecialchars($target_name); ?></p>
                <p><strong>目标地址：</strong> <a href="<?php echo htmlspecialchars($website['website_url']); ?>" target="_blank"><?php echo htmlspecialchars($website['website_url']); ?></a></p>
                <p><strong>引用文字：</strong> "穿越星河，只为遇见未知的你。"</p>
                <p>即将在 <span id="countdown">10</span> 秒后到达！</p>
                
                <div class="rating-system">
                    <p>给这次探索评分:</p>
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star" data-rating="<?php echo $i; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <p class="rating-result"></p>
                </div>
                
                <div class="share-buttons">
                    <button class="share-btn" data-platform="twitter">分享到Twitter</button>
                    <button class="share-btn" data-platform="facebook">分享到Facebook</button>
                    <button class="copy-link">复制链接</button>
                </div>
            </div>
        <?php endif; ?>
        <div class="travel-info card-effect">
            <h2>最近迁跃记录</h2>
            <?php if ($recent_logs->numColumns() == 0): ?>
                <p>暂无迁跃记录</p>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>迁跃编号</th>
                                <th>网站名称</th>
                                <th>网站地址</th>
                                <th>迁跃时间</th>
                            </tr>
                            <tbody>
                                <?php while ($row = $recent_logs->fetchArray(SQLITE3_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['travel_number']); ?></td>
                                        <td><?php echo htmlspecialchars($row['website_name']); ?></td>
                                        <td><a href="<?php echo htmlspecialchars($row['website_url']); ?>" target="_blank"><?php echo htmlspecialchars($row['website_url']); ?></a></td>
                                        <td><?php echo htmlspecialchars($row['travel_time']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </thead>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="footer">
        <a href="index.php">主页</a>
        <a href="about.php">关于</a>
        <a href="join.php">加入</a>
        <a href="change.php">变更</a>
        <a href="public.php">公示</a>
        <a href="travel.php">迁跃</a>
        <br>
        <a href="<?php echo htmlspecialchars($settings['site_url'] ?? ''); ?>/query.php?keyword=20240001" target="_blank">联bBb盟 icp备20240001号</a>
    </div>
    <div class="stars"></div>
    <div class="particles">
        <?php for ($i = 0; $i < 30; $i++): ?>
            <div class="particle"></div>
        <?php endfor; ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('loaded');
            // Adjust container and table height
            const container = document.querySelector('.travel-container');
            const header = document.querySelector('.header');
            const footer = document.querySelector('.footer');
            const tableWrapper = document.querySelector('.table-wrapper');
            function adjustContainerHeight() {
                const headerHeight = header.offsetHeight;
                const footerHeight = footer.offsetHeight;
                const windowHeight = window.innerHeight;
                const availableHeight = windowHeight - headerHeight - footerHeight - 40;
                container.style.minHeight = `${Math.min(availableHeight, windowHeight * 0.8)}px`;
                if (tableWrapper) {
                    tableWrapper.style.maxHeight = `${availableHeight - headerHeight - 100}px`;
                }
            }
            adjustContainerHeight();
            window.addEventListener('resize', adjustContainerHeight);
        });

        // Countdown for automatic travel
        <?php if (!empty($approved_websites)): ?>
            let countdown = 10;
            setInterval(() => {
                if (countdown <= 0) window.location.href = "<?php echo htmlspecialchars($website['website_url']); ?>";
                else document.getElementById("countdown").innerText = countdown--;
            }, 1000);
        <?php endif; ?>

        // Page transition for links
        document.querySelectorAll('a').forEach(link => {
            if (!link.classList.contains('github-link') && !link.classList.contains('filing-link')) {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    document.body.classList.remove('loaded');
                    setTimeout(() => {
                        window.location = e.target.href;
                    }, 300);
                });
            }
        });
    </script>
<canvas id="space-canvas"></canvas>
<script>
    // 3D星空背景
    const canvas = document.getElementById('space-canvas');
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    // 创建星星
    const stars = [];
    for (let i = 0; i < 200; i++) {
        stars.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            radius: Math.random() * 1.5,
            speed: Math.random() * 0.2,
            opacity: Math.random()
        });
    }

    // 动画循环
    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // 更新和绘制星星
        stars.forEach(star => {
            star.y += star.speed;
            if (star.y > canvas.height) {
                star.y = 0;
                star.x = Math.random() * canvas.width;
            }
            
            ctx.beginPath();
            ctx.arc(star.x, star.y, star.radius, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(255, 255, 255, ${star.opacity})`;
            ctx.fill();
        });
        
        requestAnimationFrame(animate);
    }
    
    animate();
    
    // 响应窗口大小变化
    window.addEventListener('resize', () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    });
</script>
<?php getFooterText();?>
</body>
</html>