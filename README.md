# dBd-Filing
**开源精简虚拟备案系统 / A Simple Virtual Filing System**

![image](https://img.shields.io/github/stars/bbb-lsy07/dBd-Filing?style=flat-square)
![image](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)

>由于最初设计缺陷，本服务不再维护，大概在2025.8.31日前推出全新的版本，可持续关注账号动态！
>Due to initial design flaws, this service is no longer maintained. A brand new version will be released around August 31, 2025. Please continue to follow the account updates!

🌐 **Language Switch**: [English README](https://github.com/bbb-lsy07/dBd-Filing/blob/main/README_EN.md)

> **重要提示**：本系统仅用于技术学习与娱乐，不具备任何官方备案效力，禁止用于非法场景或存储真实敏感信息！

## 📌 项目简介
基于 **PHP + SQLite** 的虚拟 ICP 备案模拟系统，支持用户提交虚拟备案、记录网站迁跃日志、查询备案状态及后台管理。项目采用响应式设计，包含动态背景、毛玻璃特效和页面切换动画，完全开源，欢迎体验与贡献代码。

### 技术栈：
- **后端**：PHP 7.4+、SQLite（无第三方数据库依赖）
- **前端**：HTML/CSS（响应式布局）、JavaScript（粒子动画、3D星空）
- **邮件功能**：PHPMailer（`vendor/PHPMailer/src`）

## ✨ 核心功能特性
| **功能模块** | **说明** | **对应文件** |
|--------------|----------|--------------|
| **备案提交** | 填写网站信息生成虚拟备案号，获取可嵌入的 HTML 代码 | `join.php` |
| **备案查询** | 支持备案号 / 网站地址精确查询，邮箱自动掩码保护隐私，可下载备案证书 | `query.php` |
| **备案变更** | 提交备案信息修改请求，需通过备案邮箱进行验证，并等待管理员审核 | `change.php` / `change_verify.php` / `process_change.php` |
| **迁跃日志** | 随机展示已通过备案的网站，模拟星链穿梭体验，记录迁跃信息，查看最近 5 条日志 | `travel.php` |
| **后台管理** | 审核 / 删除备案记录、配置动态背景图、修改管理员账户、**查看数据统计**、**手动网站健康检查**、**系统在线更新** | `admin.php` / `admin_statistics.php` |
| **公示页面** | 展示所有通过审核的备案记录，支持响应式表格和滚动 | `public.php` |
| **动态背景** | 管理员可设置全局背景图（支持外部 URL，如 `https://www.dmoe.cc/random.php`），部分页面有炫酷的3D星空效果 | `admin.php` + `style.css` + `travel.php` |
| **轻量数据库** | SQLite 自动创建，包含 `settings`, `filings`, `admins`, `travel_logs` 表，支持网站健康状态和版本信息存储 | `common.php` + `database.sqlite` |
| **邮件通知** | 支持备案提交、审核结果（通过/拒绝）、网站健康异常的邮件通知 | `send_mail.php` |
| **网站健康检查** | 定时或手动检查已备案网站的在线状态，若异常发送通知 | `check_website_health.php` / `process_health_check.php` |
| **系统在线更新** | 后台提供在线更新功能，可检查最新版本并安装 | `process_update.php` |
| **首次登录强制修改密码** | 管理员首次登录系统强制修改初始密码，提高安全性 | `login.php` |
| **CSRF 防护** | 所有表单请求均包含 CSRF token 验证，防止跨站请求伪造攻击 | `process.php` / `process_filing.php` / `process_change.php` 等 |

## 🗂️ 文件结构
```
dBd-Filing/  
├── vendor/                  # 依赖库  
│   ├── PHPMailer/           # PHPMailer 邮件组件（官方库）  
│   │   ├── src/  
│   │   │   ├── Exception.php  
│   │   │   ├── PHPMailer.php  
│   │   │   └── SMTP.php  
│   │   └── (其他PHPMailer文件...)  
├── LICENSE                  # MIT 开源协议  
├── README.md                # 项目说明（中文）  
├── README_EN.md             # 项目说明（英文）  
├── about.php                # 关于页面  
├── admin.php                # 后台管理主界面  
├── admin_statistics.php     # 后台数据统计模块  
├── admin_version_check.php  # (旧版) 后台版本检查存根，新版已通过 admin.php 及 process_update.php 实现  
├── api_status.php           # API 状态查询接口  
├── change.php               # 备案变更查询入口  
├── change_verify.php        # 备案变更邮箱/验证码验证页面  
├── check_website_health.php # 后台网站健康自动检查脚本 (建议配置为Cron Job定时运行)  
├── common.php               # 数据库连接 & 公共函数（表结构定义、设置加载）  
├── config.php               # 全局配置（数据库文件、更新地址、默认管理员信息、站点默认设置、版本号）  
├── dark-mode.css            # 深色模式样式（未完全启用，仅供参考）  
├── generate_certificate.php # 备案证书生成页面  
├── index.php                # 前台主页（查询入口）  
├── join.php                 # 备案提交表单  
├── login.php                # 管理员登录页面  
├── logout.php               # 退出登录处理  
├── process.php              # 通用备案提交处理逻辑（部分功能与 process_filing.php 重叠）  
├── process_change.php       # 处理备案变更请求的最终提交  
├── process_filing.php       # 处理新备案提交请求  
├── process_health_check.php # 后台手动网站健康检查AJAX接口  
├── process_update.php       # 处理系统在线更新逻辑 (SSE 流式输出)  
├── public.php               # 备案公示页面  
├── query.php                # 备案查询页面  
├── send_mail.php            # 邮件发送功能（基于PHPMailer封装）  
├── style.css                # 全局样式（响应式布局、毛玻璃效果、霓虹光效、动态背景）  
├── travel.php               # 迁跃日志提交 & 查看页面，包含3D星空背景  
└── update_errors.log        # 系统更新错误日志（自动生成）  
```

**数据库文件**：`database.sqlite` 由系统首次运行时自动创建，无需手动配置。

## 🚀 安装步骤
### 1. 获取代码
```bash
# 通过 Git 克隆  
git clone https://github.com/bbb-lsy07/dBd-Filing.git  
cd dBd-Filing  

# 或下载 Release 包：https://github.com/bbb-lsy07/dBd-Filing/releases  
```

### 2. 环境要求
- **Web 服务器**：Apache/Nginx（推荐 Apache + mod_rewrite）
- **PHP**：**7.4+**（需启用 `pdo_sqlite`、`mbstring`、`curl` 和 `gd` 扩展）
- **客户端**：Chrome/Firefox/Edge（支持 CSS3 和 JavaScript 的现代浏览器）

### 3. 配置服务器
**Linux/macOS**：
```bash
# 设置目录写入权限（用于生成数据库文件、日志文件和更新包）  
chmod -R 755 .  
```

**Windows**：
- 将项目复制到 Web 服务器目录（如 XAMPP 的 `htdocs`）
- 右键文件夹 → 属性 → 安全 → 编辑 → 勾选 “写入” 权限

### 4. 首次访问
- 浏览器输入：`http://你的域名/admin.php`
- 系统会自动执行环境检测，如果缺少必要的 PHP 扩展或文件写入权限，会显示详细错误信息。请根据提示修复。
- 环境检测通过后，系统将自动创建数据库并初始化管理员账户（`admin/123456`），**首次登录后请立即修改密码**！

## 📖 使用说明
### 🧑‍💻 用户端操作
- **提交备案**：访问 `join.php` 或 `index.php` 提交备案信息，填写表单后生成虚拟备案号和嵌入代码。
- **查询备案**：访问 `query.php`，输入备案号或网站地址查看详情，已通过的备案可下载备案证书。
- **变更备案**：访问 `change.php`，查询到备案后需要通过备案邮箱验证才能修改信息。
- **星链穿梭（迁跃日志）**：访问 `travel.php`，体验随机访问已备案网站，并查看最近的迁跃记录。
- **查看公示**：访问 `public.php`，浏览所有通过审核的备案记录。

### 👩‍💻 管理员端操作
- **登录后台**：访问 `login.php`，使用默认账户或自定义账户登录。首次登录会强制要求修改密码。
- **备案管理**：在 `admin.php` 中对备案记录进行通过 / 拒绝 / 删除操作。已通过的备案可触发手动健康检查。
- **数据统计**：在 `admin.php` 点击“数据统计”查看备案总览、每日/每月新增备案等图表。
- **系统设置**：在 `admin.php` 点击“站点设置”修改站点标题、URL、欢迎信息、联系邮箱、QQ群、SMTP邮件配置和全局背景图URL。
- **账户管理**：在 `admin.php` 点击“修改账户”更新管理员用户名和密码，保存后需重新登录。
- **系统更新**：在 `admin.php` 点击“检查更新”可以查看最新版本信息，并通过在线更新功能安装新版本。点击“从GitHub获取”按钮可以强制从GitHub拉取最新代码（此操作可能导致数据丢失，请谨慎！）。

## ⚠️ 注意事项
### 安全风险：
- 默认密码 `admin/123456` 存在安全隐患，**务必首次登录后修改**！
- 项目未经过专业安全审计，**严禁用于生产环境或存储敏感数据**。
- 更新机制（`process_update.php`）直接覆盖文件，操作前请务必备份！

### 数据库依赖：
- SQLite 适用于低并发场景，高负载时可能性能下降。
- `database.sqlite` 文件需确保可写，否则系统无法正常运行。
- **建议配置网站健康检查 Cron Job**：为 `check_website_health.php` 设置定时任务（例如每小时执行一次），以确保网站健康状态的自动更新和异常通知。

### 字体加载：
- 使用 BootCDN 思源黑体优化国内访问，若加载缓慢可将字体文件下载至本地并修改 `style.css` 路径。

## 🤝 贡献指南
欢迎通过 **Issue** 反馈问题或提交 **Pull Request** 参与开发！

### 贡献流程
1.  **Fork 仓库**：点击右上角 Fork 按钮，创建个人开发分支。
2.  **本地开发**：
    ```bash
    git checkout -b feature/new-pagination  # 新建功能分支  
    git checkout -b fix/csrf-protection     # 新建 Bug 修复分支  
    ```
3.  **提交代码**：确保代码规范（参考 PSR-2），添加必要注释。
4.  **创建 PR**：清晰描述变更内容，等待维护者审核合并。

### 待改进方向（欢迎认领）
#### 安全性增强：
- 密码加密存储（目前已哈希，可考虑更强的加密或密钥管理）。
- 更全面的输入验证和过滤，防止 XSS 和其他注入攻击。
- 更新机制优化，避免直接覆盖文件导致数据丢失或权限问题。

#### 功能优化：
- 备案记录分页显示（`public.php` 和 `admin.php`）。
- 多语言支持（简中 / 英文切换）。
- `travel.php` 的评分和分享功能后端实现。

#### 性能提升：
- 优化粒子动画和3D星空性能，降低内存占用。

## 📄 开源许可
本项目采用 **MIT 许可证**，允许自由使用、修改和分发，但需保留项目开源声明。

**引用要求**：若使用本系统，请在网站底部或关于页面注明：
```html
虚拟备案系统由 <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank">dBd-Filing</a> 提供技术支持  
```

## 📧 联系方式
- **作者**：bbb-lsy07
- **GitHub**：https://github.com/bbb-lsy07
- **问题反馈**：提交 Issue

## 📅 版本更新
🚀 **最新版本**：V2.5.3（2025-05-05）
- **新增**：管理员后台数据统计，显示总备案、待审批、已通过、已拒绝数量以及每日/每月新增备案图表。
- **新增**：已通过备案网站健康检查功能，支持手动触发和Cron Job定时检查，异常时发送邮件通知。
- **新增**：后台系统在线更新功能，可从GitHub获取最新版本并更新。
- **新增**：备案变更流程加入邮箱验证和验证码验证，提升安全性。
- **新增**：备案证书生成功能，已通过备案可在查询页面下载虚拟证书。
- **新增**：首次登录强制修改管理员密码，并支持管理员在后台修改账户信息。
- **新增**：网站迁跃页面（`travel.php`），提供3D星空背景、随机网站跳转、迁跃日志记录、评分和分享功能（前端）。
- **优化**：统一了备案提交的逻辑，支持手动输入备案号（若重复则自动生成）。
- **优化**：所有表单均添加 CSRF token 验证，增强安全性。
- **优化**：PHPMailer 配置加载自数据库设置，邮件发送更加灵活。
- **优化**：界面UI更新，包括炫酷的霓虹表单、毛玻璃特效和更流畅的页面切换动画。
- **修复**：网站访问路径问题，确保在不同子目录下的正常运行。
- **修复**：邮件发送失败问题，日志记录更详细。
- **修复**：其他若干BUG修复和性能优化。

**完整更新日志**：查看 [Releases](https://github.com/bbb-lsy07/dBd-Filing/releases)

---

**感谢您的支持！** 如果项目对您有帮助，欢迎点亮 GitHub 星标 ⭐，让更多开发者发现！
