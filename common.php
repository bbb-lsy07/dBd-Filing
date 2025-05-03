<?php
session_start();

function init_database() {
    $db = new SQLite3('database.sqlite');

    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        site_title TEXT DEFAULT '联bBb盟 ICP 备案系统',
        site_url TEXT DEFAULT 'https://icp.bbb-lsy07.my',
        welcome_message TEXT DEFAULT '这是一个虚拟备案系统，仅供娱乐和社区互动使用，非官方备案。',
        contact_email TEXT DEFAULT 'admin@bbb-lsy07.my',
        qq_group TEXT DEFAULT '123456789',
        smtp_host TEXT,
        smtp_port INTEGER,
        smtp_username TEXT,
        smtp_password TEXT,
        smtp_secure TEXT DEFAULT 'tls',
        background_image TEXT DEFAULT 'https://www.dmoe.cc/random.php'
    )");

    $result = $db->query("PRAGMA table_info(settings)");
    $columns = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $columns[] = $row['name'];
    }
    if (!in_array('background_image', $columns)) {
        $db->exec("ALTER TABLE settings ADD COLUMN background_image TEXT DEFAULT 'https://www.dmoe.cc/random.php'");
    }

    $db->exec("CREATE TABLE IF NOT EXISTS filings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        filing_number TEXT UNIQUE,
        website_name TEXT,
        website_url TEXT,
        description TEXT,
        contact_email TEXT,
        submission_date TEXT,
        status TEXT DEFAULT 'pending'
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE,
        password TEXT,
        force_reset INTEGER DEFAULT 0,
        role TEXT DEFAULT 'admin'
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS travel_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        travel_number TEXT,
        website_name TEXT,
        website_url TEXT,
        travel_time TEXT
    )");

    if (!$db->querySingle("SELECT * FROM settings")) {
        $db->exec("INSERT INTO settings (site_title, site_url, welcome_message, contact_email, qq_group, background_image) 
                   VALUES ('联bBb盟 ICP 备案系统', 'https://icp.bbb-lsy07.my', '这是一个虚拟备案系统，仅供娱乐和社区互动使用，非官方备案。', 'admin@bbb-lsy07.my', '123456789', 'https://www.dmoe.cc/random.php')");
    }

    if ($db->querySingle("SELECT COUNT(*) FROM admins") == 0) {
        $stmt = $db->prepare("INSERT INTO admins (username, password, force_reset, role) VALUES (:username, :password, 1, 'admin')");
        $stmt->bindValue(':username', 'admin', SQLITE3_TEXT);
        $stmt->bindValue(':password', password_hash('123456', PASSWORD_DEFAULT), SQLITE3_TEXT);
        $stmt->execute();
    }

    return $db;
}

define('APP_VERSION', '1.0.1');

function getFooterText() {
    $db = init_database();
    $stmt = $db->prepare("SELECT site_title FROM settings LIMIT 1");
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $row = $row ?: ['site_title' => '联bBb盟 ICP 备案系统'];
    return "<footer>
        <p>版权所有 © " . date('Y') . " " . htmlspecialchars($row['site_title']) . " | 版本 " . APP_VERSION . "</p>
    </footer>";
}
?>