# dBd-Filing
A Simple Virtual Filing System

---

# 联bBb盟 ICP 备案系统 / Virtual ICP Filing System by LianbBb Alliance

## 项目简介 / Project Overview
这是一个基于 PHP 和 SQLite 的虚拟 ICP 备案系统，仅用于娱乐和社区互动，不具备任何官方效力。用户可以通过此系统提交虚拟网站备案信息，查询备案状态，并在后台管理系统中审核或管理备案记录。项目完全开源，欢迎体验和贡献代码。  
This is a virtual ICP filing system based on PHP and SQLite, intended solely for entertainment and community interaction, with no official authority. Users can submit virtual website filing information, check filing status, and manage or review records in the admin backend. The project is fully open-source, and we welcome you to try it out and contribute code.

---

## 功能特性 / Features
- **提交备案 / Submit Filing**  
  用户可以提交网站名称、地址、描述和联系邮箱，生成唯一的虚拟备案号。  
  Users can submit website name, URL, description, and contact email to generate a unique virtual filing number.  
- **查询备案 / Query Filing**  
  支持通过备案号或网站地址查询备案详情，邮箱部分信息会自动掩码保护。  
  Supports querying filing details by filing number or website URL, with partial email masking for privacy.  
- **后台管理 / Admin Management**  
  管理员可以登录后台，审核、删除或修改备案记录，并管理账户信息。  
  Admins can log in to review, delete, or modify filing records and manage account information.  
- **公示页面 / Public Page**  
  展示所有通过审核的备案信息。  
  Displays all approved filing information.  
- **响应式设计 / Responsive Design**  
  界面适配桌面和移动设备，包含美观的样式和 GitHub 开源角标。  
  Interface adapts to desktop and mobile devices, featuring attractive styles and a GitHub open-source badge.  
- **轻量数据库 / Lightweight Database**  
  使用 SQLite，无需额外配置数据库服务。  
  Uses SQLite, requiring no additional database service setup.

---

## 文件结构 / File Structure
```
virtual-filing/
├── index.php        # 主页（提交表单） / Homepage (Submission Form)
├── query.php        # 查询页面 / Query Page
├── process.php      # 处理表单提交 / Process Form Submission
├── admin.php        # 后台管理页面 / Admin Management Page
├── login.php        # 后台登录页面 / Admin Login Page
├── logout.php       # 退出登录 / Logout
├── public.php       # 公示页面 / Public Page
├── style.css        # 美化样式 / Styling
└── database.sqlite  # SQLite 数据库（运行时自动创建） / SQLite Database (Auto-created at Runtime)
```

### 文件功能说明 / File Function Descriptions
1. **`index.php`**  
   主页，提供备案提交表单，包含网站名称、地址、描述和联系邮箱的输入字段。  
   Homepage, provides a filing submission form with fields for website name, URL, description, and contact email.  
2. **`query.php`**  
   查询页面，用户可输入备案号或网站地址查看详细信息。  
   Query page, allows users to enter a filing number or website URL to view details.  
3. **`process.php`**  
   处理表单提交，生成备案号并存入数据库，返回备案成功的提示和展示代码。  
   Processes form submissions, generates a filing number, stores it in the database, and returns a success message with display code.  
4. **`admin.php`**  
   后台管理页面，管理员可查看所有备案记录，进行审核（通过/拒绝）、删除操作，并修改账户信息。  
   Admin management page, where admins can view all filing records, approve/reject/delete them, and modify account info.  
5. **`login.php`**  
   后台登录页面，默认账户为 `admin` / `123456`（首次运行自动创建）。  
   Admin login page, default account is `admin` / `123456` (auto-created on first run).  
6. **`logout.php`**  
   退出登录，销毁会话并跳转到登录页面。  
   Logs out, destroys the session, and redirects to the login page.  
7. **`public.php`**  
   公示页面，展示所有状态为“已通过”的备案记录。  
   Public page, displays all records with "approved" status.  
8. **`style.css`**  
   美化样式文件，包含响应式设计和 GitHub 角标样式。  
   Styling file, includes responsive design and GitHub badge styles.

---

## 安装步骤 / Installation Steps
1. **克隆仓库或下载发行版本 / Clone the Repository or Download Release**  
   - 通过 Git 克隆 / Clone via Git:  
     ```
     git clone https://github.com/bbb-lsy07/dBd-Filing.git
     cd dBd-Filing
     ```
   - 或直接下载发行版本并解压到你的网站目录 / Or download the release and extract it to your web directory.  
2. **配置服务器 / Configure the Server**  
   - 将项目文件夹放入 Web 服务器目录（如 Apache 的 `htdocs` 或 Nginx 的 `html`）。  
     Place the project folder in the web server directory (e.g., Apache’s `htdocs` or Nginx’s `html`).  
   - 确保服务器支持 PHP（推荐 PHP 7.4 或更高版本）并启用 SQLite 扩展。  
     Ensure the server supports PHP (PHP 7.4 or higher recommended) with the SQLite extension enabled.  
3. **设置权限 / Set Permissions**  
   - 确保项目目录可写，以便自动创建 `database.sqlite` 文件。  
     Ensure the project directory is writable to auto-create the `database.sqlite` file.  
   - 示例（Linux 系统） / Example (Linux System):  
     ```
     chmod -R 755 .
     ```
4. **访问项目 / Access the Project**  
   - 在浏览器中输入项目地址（如 `http://localhost/dBd-Filing/index.php`）。  
     Enter the project URL in your browser (e.g., `http://localhost/dBd-Filing/index.php`).  
   - 系统会自动创建 SQLite 数据库并初始化管理员账户。  
     The system will auto-create the SQLite database and initialize the admin account.

---

## 使用说明 / Usage Instructions
### 用户端 / User Side
1. **提交备案 / Submit Filing**  
   - 访问主页（`index.php`），填写表单并提交。  
     Visit the homepage (`index.php`), fill out the form, and submit.  
   - 提交后会生成备案号和一段 HTML 代码，可复制到网站页脚。  
     After submission, a filing number and HTML code will be generated for copying to your website footer.  
2. **查询备案 / Query Filing**  
   - 访问查询页面（`query.php`），输入备案号或网站地址查看详情。  
     Visit the query page (`query.php`), enter a filing number or URL to view details.  
3. **查看公示 / View Public Records**  
   - 访问公示页面（`public.php`），查看所有通过审核的备案。  
     Visit the public page (`public.php`) to view all approved filings.

### 管理员端 / Admin Side
1. **登录后台 / Log into Admin Panel**  
   - 访问 `login.php`，使用默认账户 `admin` / `123456` 登录（首次运行自动创建）。  
     Visit `login.php` and log in with the default account `admin` / `123456` (auto-created on first run).  
2. **管理备案 / Manage Filings**  
   - 在 `admin.php` 中查看所有备案记录。  
     View all filing records in `admin.php`.  
   - 可通过、拒绝或删除备案记录。  
     Approve, reject, or delete filing records.  
3. **修改账户 / Modify Account**  
   - 点击“修改账户”，输入新用户名和密码，保存后需重新登录。  
     Click "Modify Account," enter a new username and password, and re-login after saving.  
4. **退出登录 / Log Out**  
   - 点击“退出登录”跳转回登录页面。  
     Click "Log Out" to return to the login page.

---

## 注意事项 / Notes
- **安全性 / Security**  
  - 默认管理员账户密码为 `admin` / `123456`，建议首次登录后立即修改。  
    The default admin account is `admin` / `123456`; change it immediately after the first login.  
  - 项目仅用于娱乐，未经过严格的安全加固，不建议用于生产环境。  
    This project is for entertainment only, not hardened for security, and not recommended for production use.  
- **数据库 / Database**  
  - SQLite 文件（`database.sqlite`）会在首次运行时自动创建，需确保目录可写。  
    The SQLite file (`database.sqlite`) is auto-created on first run; ensure the directory is writable.  
- **域名配置 / Domain Configuration**  
  - 代码中的链接（如查询 URL）使用相对路径，部署时无需修改。若需绝对路径，请根据实际情况调整。  
    Links in the code (e.g., query URL) use relative paths; no changes needed for deployment. Adjust to absolute paths if necessary.

---

## 贡献指南 / Contribution Guide
欢迎提交 Issue 或 Pull Request！以下是贡献步骤：  
We welcome Issues or Pull Requests! Here are the steps to contribute:  
1. **Fork 本仓库 / Fork this repository**  
2. **创建你的功能分支 / Create your feature branch**  
   ```
   git checkout -b feature/YourFeature
   ```
3. **提交更改 / Submit changes**  
   ```
   git commit -m "Add YourFeature"
   ```
4. **推送到远程分支 / Push to the remote branch**  
   ```
   git push origin feature/YourFeature
   ```
5. **创建 Pull Request / Create a Pull Request**

### 建议改进方向 / Suggested Improvements
- 添加多语言支持 / Add multi-language support.  
- 增强安全性（如防止 SQL 注入、CSRF 保护） / Enhance security (e.g., prevent SQL injection, CSRF protection).  
- 支持批量审核功能 / Support batch review functionality.  
- 添加备案状态通知（通过邮箱） / Add filing status notifications (via email).

---

## 开源许可 / Open Source License
本项目采用 [MIT 许可证](LICENSE)，欢迎自由使用和修改。  
This project is licensed under the [MIT License](LICENSE), free to use and modify.

---

## 联系方式 / Contact
- 作者 / Author: bbb-lsy07  
- GitHub: [https://github.com/bbb-lsy07](https://github.com/bbb-lsy07)  
- 问题反馈 / Feedback: 请提交 [Issue](https://github.com/bbb-lsy07/dBd-Filing/issues)  
  Please submit an [Issue](https://github.com/bbb-lsy07/dBd-Filing/issues)

感谢使用和支持！ / Thank you for using and supporting!
