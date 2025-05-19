# dBd-Filing
**开源精简虚拟备案系统 / A Simple Virtual Filing System**

![image](https://img.shields.io/github/stars/bbb-lsy07/dBd-Filing?style=flat-square)
![image](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)

🌐 **Language Switch**: [English README](https://github.com/bbb-lsy07/dBd-Filing/blob/main/README_EN.md)

> **重要提示**：本系统仅用于技术学习与娱乐，不具备任何官方备案效力，禁止用于非法场景或存储真实敏感信息！

## 📌 项目简介
基于 **PHP + SQLite** 的虚拟 ICP 备案模拟系统，支持用户提交虚拟备案、记录网站迁跃日志、查询备案状态及后台管理。项目采用响应式设计，包含动态背景、毛玻璃特效和页面切换动画，完全开源，欢迎体验与贡献代码。

### 技术栈：
- **后端**：PHP 7.4+、SQLite（无第三方数据库依赖）
- **前端**：HTML/CSS（响应式布局）、JavaScript（粒子动画）
- **邮件功能**：PHPMailer（`vendor/PHPMailer/src`）

## ✨ 核心功能特性
| **功能模块** | **说明** | **对应文件** |
|--------------|----------|--------------|
| **备案提交** | 填写网站信息生成虚拟备案号，获取可嵌入的 HTML 代码 | `join.php` |
| **备案查询** | 支持备案号 / 网站地址精确查询，邮箱自动掩码保护隐私 | `query.php` |
| **备案变更** | 提交备案信息修改请求（需管理员审核） | `change.php` |
| **迁跃日志** | 记录网站迁跃信息，查看最近 5 条日志 | `travel.php` |
| **后台管理** | 审核 / 删除备案记录、配置动态背景图、修改管理员账户 | `admin.php` |
| **公示页面** | 展示所有通过审核的备案记录，支持响应式表格和滚动 | `public.php` |
| **动态背景** | 管理员可设置全局背景图（支持外部 URL，如 `https://www.dmoe.cc/random.php`） | `admin.php` + `style.css` |
| **轻量数据库** | SQLite 自动创建，包含 `settings`, `filings`, `admins`, `travel_logs` 表 | `common.php` + `database.sqlite` |

## 🗂️ 文件结构
```
dBd-Filing/  
├── vendor/                  # 依赖库  
│   └── PHPMailer/src/       # PHPMailer 邮件组件（官方库）  
├── LICENSE                  # MIT 开源协议  
├── README.md                # 项目说明（中文）  
├── README_EN.md             # 项目说明（英文）  
├── about.php                # 关于页面  
├── admin.php                # 后台管理主界面  
├── change.php               # 备案变更页面  
├── common.php               # 数据库连接 & 公共函数（表结构定义）  
├── index.php                # 前台主页（查询入口）  
├── join.php                 # 备案提交表单  
├── login.php                # 管理员登录页面（默认账户：admin/123456）  
├── logout.php               # 退出登录处理  
├── process.php              # 通用处理逻辑（如参数验证）  
├── process_change.php       # 处理备案变更请求  
├── process_filing.php       # 处理新备案提交  
├── process_update.php       # 处理系统设置更新（如背景图）  
├── public.php               # 备案公示页面  
├── query.php                # 备案查询页面  
├── send_mail.php            # 邮件发送功能（审核结果通知）  
├── style.css                # 全局样式（响应式布局、毛玻璃效果等）  
├── travel.php               # 迁跃日志提交 & 查看页面  
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
- **PHP**：7.4+（需启用 `pdo_sqlite` 和 `mbstring` 扩展）
- **客户端**：Chrome/Firefox/Edge（支持 CSS3 和 JavaScript 的现代浏览器）

### 3. 配置服务器
**Linux/macOS**：
```bash
# 设置目录写入权限（用于生成数据库文件）  
chmod -R 755 .  
```

**Windows**：
- 将项目复制到 Web 服务器目录（如 XAMPP 的 `htdocs`）
- 右键文件夹 → 属性 → 安全 → 编辑 → 勾选 “写入” 权限

### 4. 首次访问
- 浏览器输入：`http://你的域名/index.php`
- 系统自动创建数据库并初始化管理员账户（`admin/123456`），**首次登录后请立即修改密码**！

## 📖 使用说明
### 🧑‍💻 用户端操作
- **提交备案**：访问 `join.php`，填写表单后生成备案号和嵌入代码。
- **查询备案**：访问 `query.php`，输入备案号或网站地址查看详情。
- **记录迁跃**：访问 `travel.php`，提交 8 位迁跃编号和网站信息，查看最近日志。
- **查看公示**：访问 `public.php`，浏览所有通过审核的备案记录。

### 👩‍💻 管理员端操作
- **登录后台**：访问 `login.php`，使用默认账户或自定义账户登录。
- **审核管理**：在 `admin.php` 中对备案记录进行通过 / 拒绝 / 删除操作。
- **系统设置**：
  - **背景图配置**：在 “站点设置” 中输入图片 URL 并保存。
  - **域名配置**：若需绝对路径，在 “站点设置” 中修改 `site_url`。
  - **账户管理**：点击 “修改账户” 更新用户名和密码，保存后重新登录。

## ⚠️ 注意事项
### 安全风险：
- 默认密码 `admin/123456` 存在安全隐患，**务必首次登录后修改**！
- 项目未经过专业安全审计，**严禁用于生产环境或存储敏感数据**。

### 数据库依赖：
- SQLite 适用于低并发场景，高负载时可能性能下降。
- `database.sqlite` 文件需确保可写，否则系统无法正常运行。

### 字体加载：
- 使用 BootCDN 思源黑体优化国内访问，若加载缓慢可将字体文件下载至本地并修改 `style.css` 路径。

## 🤝 贡献指南
欢迎通过 **Issue** 反馈问题或提交 **Pull Request** 参与开发！

### 贡献流程
1. **Fork 仓库**：点击右上角 Fork 按钮，创建个人开发分支。
2. **本地开发**：
   ```bash
   git checkout -b feature/new-pagination  # 新建功能分支  
   git checkout -b fix/csrf-protection     # 新建 Bug 修复分支  
   ```
3. **提交代码**：确保代码规范（参考 PSR-2），添加必要注释。
4. **创建 PR**：清晰描述变更内容，等待维护者审核合并。

### 待改进方向（欢迎认领）
#### 安全性增强：
- 添加 CSRF 防护和 SQL 注入预防（使用 PDO 预处理语句）。
- 密码加密存储（替换现有明文存储）。

#### 功能优化：
- 备案记录分页显示（`public.php` 和 `admin.php`）。
- 多语言支持（简中 / 英文切换）。

#### 性能提升：
- 本地化字体文件，减少对 CDN 的依赖。
- 优化粒子动画性能，降低内存占用。

## 📄 开源许可
本项目采用 **MIT 许可证**，允许自由使用、修改和分发，但需保留项目开源声明。

**引用要求**：若使用本系统，请在网站底部或关于页面注明：
```
虚拟备案系统由 <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank">dBd-Filing</a> 提供技术支持  
```

## 📧 联系方式
- **作者**：bbb-lsy07
- **GitHub**：https://github.com/bbb-lsy07
- **问题反馈**：提交 Issue

## 📅 版本更新
🚀 **最新版本**：V2.5.0（2025-05-05）
- **新增**：备案变更审核流程（需管理员二次确认）。
- **优化**：后台表格加载速度，支持按审核状态过滤。
- **修复**：邮件发送失败问题（PHPMailer 配置优化）。

**完整更新日志**：查看 [Releases](https://github.com/bbb-lsy07/dBd-Filing/releases)

---

**感谢您的支持！** 如果项目对您有帮助，欢迎点亮 GitHub 星标 ⭐，让更多开发者发现！
