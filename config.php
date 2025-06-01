<?php

// 数据库配置
define('DB_FILE', 'database.sqlite');

// 更新检查配置
define('UPDATE_JSON_URL', 'https://admin-hosting-v.bbb-lsy07.my/json/1.json');

// 默认管理员账户 (仅在数据库为空时首次初始化使用)
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_PASS', '123456');

// 站点默认设置 (仅在数据库为空时首次初始化使用)
define('DEFAULT_SITE_TITLE', '联bBb盟 ICP 备案系统');
define('DEFAULT_SITE_URL', 'https://icp.bbb-lsy07.my');
define('DEFAULT_WELCOME_MESSAGE', '这是一个虚拟备案系统，仅供娱乐和社区互动使用，非官方备案。');
define('DEFAULT_CONTACT_EMAIL', 'admin@bbb-lsy07.my');
define('DEFAULT_QQ_GROUP', '123456789');
define('DEFAULT_BACKGROUND_IMAGE', 'https://www.dmoe.cc/random.php');
define('DEFAULT_VERSION', '2.5.2'); // 假设这是当前或目标版本

?>