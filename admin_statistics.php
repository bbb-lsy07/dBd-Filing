<div class="statistics-container card-effect">
    <h2>备案数据统计 <a href="admin.php" class="back-button">返回备案列表</a></h2>
    <div class="stats-summary">
        <div class="stat-item">
            <h3>总备案数</h3>
            <p><?php echo $total_filings; ?></p>
        </div>
        <div class="stat-item pending">
            <h3>待审批</h3>
            <p><?php echo $pending_filings; ?></p>
        </div>
        <div class="stat-item approved">
            <h3>已通过</h3>
            <p><?php echo $approved_filings; ?></p>
        </div>
        <div class="stat-item rejected">
            <h3>已拒绝</h3>
            <p><?php echo $rejected_filings; ?></p>
        </div>
    </div>

    <h3>每日新增备案 (过去7天)</h3>
    <div class="daily-stats chart-container">
        <?php foreach ($daily_new_filings as $date => $count): ?>
            <div class="chart-bar" style="height: <?php echo ($count / (max($daily_new_filings) ?: 1)) * 100; ?>%;" title="<?php echo $date; ?>: <?php echo $count; ?>">
                <span><?php echo $count; ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="chart-labels">
        <?php foreach ($daily_new_filings as $date => $count): ?>
            <span><?php echo date('m-d', strtotime($date)); ?></span>
        <?php endforeach; ?>
    </div>

    <h3>每月新增备案 (过去12个月)</h3>
    <div class="monthly-stats chart-container">
        <?php foreach ($monthly_new_filings as $month => $count): ?>
            <div class="chart-bar" style="height: <?php echo ($count / (max($monthly_new_filings) ?: 1)) * 100; ?>%;" title="<?php echo $month; ?>: <?php echo $count; ?>">
                <span><?php echo $count; ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="chart-labels">
        <?php foreach ($monthly_new_filings as $month => $count): ?>
            <span><?php echo date('Y-m', strtotime($month)); ?></span>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .statistics-container {
        background-color: #0a0a0a;
        border: 1px solid #0ff;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
        color: #0ff;
        box-shadow: 0 0 15px rgba(0, 255, 255, 0.6);
    }
    .statistics-container h2, .statistics-container h3 {
        color: #0ff;
        text-align: center;
        margin-bottom: 20px;
        text-shadow: 0 0 8px rgba(0, 255, 255, 0.8);
    }
    .stats-summary {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
        margin-bottom: 30px;
    }
    .stat-item {
        background-color: #1a1a1a;
        border: 1px solid #0ff;
        border-radius: 5px;
        padding: 15px;
        margin: 10px;
        text-align: center;
        flex: 1;
        min-width: 180px;
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.4);
    }
    .stat-item h3 {
        color: #0ff;
        margin-top: 0;
        margin-bottom: 10px;
        font-size: 1.2em;
    }
    .stat-item p {
        color: #fff;
        font-size: 2em;
        font-weight: bold;
    }
    .stat-item.pending {
        border-color: #ffcc00;
        box-shadow: 0 0 10px rgba(255, 204, 0, 0.6);
    }
    .stat-item.approved {
        border-color: #00ff00;
        box-shadow: 0 0 10px rgba(0, 255, 0, 0.6);
    }
    .stat-item.rejected {
        border-color: #ff0000;
        box-shadow: 0 0 10px rgba(255, 0, 0, 0.6);
    }
    .chart-container {
        display: flex;
        justify-content: space-around;
        align-items: flex-end;
        height: 200px;
        background-color: #1a1a1a;
        border: 1px solid #0ff;
        border-radius: 5px;
        padding: 10px;
        margin-top: 10px;
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.4);
    }
    .chart-bar {
        width: 8%;
        background-color: #00ffff;
        margin: 0 1%;
        position: relative;
        display: flex;
        justify-content: center;
        align-items: flex-end;
        padding-bottom: 5px;
        border-radius: 3px 3px 0 0;
        box-shadow: 0 0 8px rgba(0, 255, 255, 0.8);
        transition: height 0.5s ease-in-out;
    }
    .chart-bar span {
        position: absolute;
        top: -20px;
        color: #fff;
        font-size: 0.8em;
        text-shadow: 0 0 5px rgba(255, 255, 255, 0.8);
    }
    .chart-labels {
        display: flex;
        justify-content: space-around;
        margin-top: 5px;
        color: #0ff;
        font-size: 0.9em;
    }
    .chart-labels span {
        flex: 1;
        text-align: center;
    }
    .back-button {
        display: inline-block;
        margin-left: 20px;
        padding: 8px 15px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 0.8em;
        transition: background-color 0.3s ease;
    }
    .back-button:hover {
        background-color: #0056b3;
    }
</style>