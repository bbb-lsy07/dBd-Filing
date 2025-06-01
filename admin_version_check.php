<?php
// backend self check stub
require_once 'common.php';
$current = DEFAULT_VERSION;
$latest = file_get_contents('https://raw.githubusercontent.com/example/dBd-Filing/master/latest.json');
$latest_json = json_decode($latest, true);
?>
<h3>版本自检</h3>
<p>当前版本: <?php echo htmlspecialchars($current); ?></p>
<p>最新版本: <?php echo htmlspecialchars($latest_json['version'] ?? '未知'); ?></p>
<?php if($latest_json && version_compare($latest_json['version'],$current,'>')): ?>
    <p style="color:red;">发现新版本！请尽快更新。</p>
<?php else: ?>
    <p>已是最新版本。</p>
<?php endif; ?>
