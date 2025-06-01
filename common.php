<?php
require_once 'config.php';
session_start();

function init_database() {
    try {
        $db = new SQLite3(DB_FILE);
    } catch (Exception $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Error: Could not connect to the database. Please check the server logs for more details.");
    }

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
        background_image TEXT DEFAULT 'https://www.dmoe.cc/random.php',
        version TEXT DEFAULT '2.5.0'
    )");

    $result = $db->query("PRAGMA table_info(settings)");
    $columns = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $columns[] = $row['name'];
    }
    if (!in_array('background_image', $columns)) {
        $db->exec("ALTER TABLE settings ADD COLUMN background_image TEXT DEFAULT 'https://www.dmoe.cc/random.php'");
    }
    if (!in_array('version', $columns)) {
        $db->exec("ALTER TABLE settings ADD COLUMN version TEXT DEFAULT '1.0.0'");
    }

    $db->exec("CREATE TABLE IF NOT EXISTS filings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        filing_number TEXT UNIQUE,
        website_name TEXT,
        website_url TEXT,
        description TEXT,
        contact_email TEXT,
        submission_date TEXT,
        status TEXT DEFAULT 'pending',
        is_healthy INTEGER DEFAULT 1,
        last_check_time TEXT
    )");

    // Add is_healthy column if it doesn't exist
    $result = $db->query("PRAGMA table_info(filings)");
    $columns = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $columns[] = $row['name'];
    }
    if (!in_array('is_healthy', $columns)) {
        $db->exec("ALTER TABLE filings ADD COLUMN is_healthy INTEGER DEFAULT 1");
    }
    if (!in_array('last_check_time', $columns)) {
        $db->exec("ALTER TABLE filings ADD COLUMN last_check_time TEXT");
    }

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
        $db->exec("INSERT INTO settings (site_title, site_url, welcome_message, contact_email, qq_group, background_image, version) 
                   VALUES ('" . DEFAULT_SITE_TITLE . "', '" . DEFAULT_SITE_URL . "', '" . DEFAULT_WELCOME_MESSAGE . "', '" . DEFAULT_CONTACT_EMAIL . "', '" . DEFAULT_QQ_GROUP . "', '" . DEFAULT_BACKGROUND_IMAGE . "', '" . DEFAULT_VERSION . "')");
    }

    if ($db->querySingle("SELECT COUNT(*) FROM admins") == 0) {
        $stmt = $db->prepare("INSERT INTO admins (username, password, force_reset, role) VALUES (:username, :password, 1, 'admin')");
        $stmt->bindValue(':username', DEFAULT_ADMIN_USER, SQLITE3_TEXT);
        $stmt->bindValue(':password', password_hash(DEFAULT_ADMIN_PASS, PASSWORD_DEFAULT), SQLITE3_TEXT);
        $stmt->execute();
    }

    return $db;
}

define('APP_VERSION', DEFAULT_VERSION);

function getFooterText() {
    $db = init_database();
    $stmt = $db->prepare("SELECT site_title, version FROM settings LIMIT 1");
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $row = $row ?: ['site_title' => '联bBb盟 ICP 备案系统', 'version' => '1.0.0'];
    return "<p>版权所有 © " . date('Y') . " " . htmlspecialchars($row['site_title']) . " | 版本 " . htmlspecialchars($row['version']) . "</p>";
}
?>