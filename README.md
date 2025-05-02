dBd-Filing
A Simple Virtual Filing System

联bBb盟 ICP 备案系统 / Virtual ICP Filing System by LianbBb Alliance
项目简介 / Project Overview
这是一个基于 PHP 和 SQLite 的虚拟 ICP 备案系统，仅用于娱乐和社区互动，不具备任何官方效力。用户可以通过此系统提交虚拟网站备案信息、记录网站迁跃日志、查询备案状态，并在后台管理系统中审核或管理记录。项目采用响应式设计，支持动态背景图、毛玻璃效果和页面切换动画，完全开源，欢迎体验和贡献代码。This is a virtual ICP filing system based on PHP and SQLite, intended solely for entertainment and community interaction, with no official authority. Users can submit virtual website filing information, log website travel records, query filing status, and manage or review records in the admin backend. The project features responsive design, dynamic background images, glassmorphism effects, and page transition animations, and is fully open-source. We welcome you to try it out and contribute code.

功能特性 / Features

提交备案 / Submit Filing用户可以提交网站名称、地址、描述和联系邮箱，生成唯一的虚拟备案号，并获取可嵌入网站的 HTML 代码。Users can submit website name, URL, description, and contact email to generate a unique virtual filing number and obtain HTML code for embedding on their website.  
网站迁跃 / Website Travel Logging用户可记录网站迁跃信息（迁跃编号、网站名称、地址），并查看最近的迁跃日志。Users can log website travel information (travel number, website name, URL) and view recent travel logs.  
查询备案 / Query Filing支持通过备案号或网站地址查询备案详情，邮箱部分信息自动掩码以保护隐私。Supports querying filing details by filing number or website URL, with partial email masking for privacy.  
后台管理 / Admin Management管理员可以登录后台，审核、删除或修改备案记录，管理站点设置（包括动态背景图），并修改账户信息。Admins can log in to review, delete, or modify filing records, manage site settings (including dynamic background images), and update account information.  
公示页面 / Public Page展示所有通过审核的备案信息，支持响应式表格显示和水平/垂直滚动。Displays all approved filing information with responsive table display and horizontal/vertical scrolling.  
响应式设计 / Responsive Design界面适配桌面和移动设备，包含毛玻璃效果、粒子动画、页面切换动画和 GitHub 开源角标。Interface adapts to desktop and mobile devices, featuring glassmorphism effects, particle animations, page transition animations, and a GitHub open-source badge.  
轻量数据库 / Lightweight Database使用 SQLite，无需额外配置数据库服务，支持 settings、filings、admins 和 travel_logs 表。Uses SQLite, requiring no additional database service setup, supporting settings, filings, admins, and travel_logs tables.  
国内字体加速 / Accelerated Font Loading使用 BootCDN 提供的 Noto Sans SC 字体，优化国内访问速度。Uses Noto Sans SC font from BootCDN to optimize loading speed in China.  
动态背景图 / Dynamic Background Image管理员可在后台设置全局背景图，支持外部 URL（如 https://www.dmoe.cc/random.php）。Admins can set a global background image via the backend, supporting external URLs (e.g., https://www.dmoe.cc/random.php).


文件结构 / File Structure
virtual-filing/
├── index.php        # 主页（欢迎页面和查询入口） / Homepage (Welcome and Query Entry)
├── query.php        # 查询页面 / Query Page
├── join.php         # 提交备案页面 / Filing Submission Page
├── change.php       # 备案变更页面 / Filing Change Page
├── travel.php       # 网站迁跃页面 / Website Travel Logging Page
├── admin.php        # 后台管理页面 / Admin Management Page
├── login.php        # 后台登录页面 / Admin Login Page
├── logout.php       # 退出登录 / Logout
├── public.php       # 公示页面 / Public Page
├── about.php        # 关于页面 / About Page
├── common.php       # 数据库初始化和公共函数 / Database Initialization and Common Functions
├── send_mail.php    # 邮件发送功能 / Email Sending Function
├── style.css        # 美化样式 / Styling
└── database.sqlite  # SQLite 数据库（运行时自动创建） / SQLite Database (Auto-created at Runtime)

文件功能说明 / File Function Descriptions

index.php主页，展示欢迎信息并提供查询备案的入口。Homepage, displays welcome message and provides an entry point for querying filings.  
query.php查询页面，用户可输入备案号或网站地址查看详细信息。Query page, allows users to enter a filing number or website URL to view details.  
join.php备案提交页面，包含网站名称、地址、描述和联系邮箱的表单，提交后生成备案号。Filing submission page with a form for website name, URL, description, and contact email, generating a filing number upon submission.  
change.php备案变更页面，允许用户提交备案信息的变更请求。Filing change page, allows users to submit requests to modify filing information.  
travel.php网站迁跃页面，用户可提交迁跃编号、网站名称和地址，查看最近的迁跃记录。Website travel logging page, allows users to submit travel number, website name, and URL, and view recent travel logs.  
admin.php后台管理页面，管理员可查看所有备案记录，进行审核（通过/拒绝）、删除操作，管理站点设置和账户信息。Admin management page, where admins can view all filing records, approve/reject/delete them, and manage site settings and account info.  
login.php后台登录页面，默认账户为 admin / 123456（首次运行自动创建）。Admin login page, default account is admin / 123456 (auto-created on first run).  
logout.php退出登录，销毁会话并跳转到登录页面。Logs out, destroys the session, and redirects to the login page.  
public.php公示页面，展示所有状态为“已通过”的备案记录，支持响应式表格显示。Public page, displays all records with "approved" status, with responsive table display.  
about.php关于页面，介绍项目背景和联系方式。About page, introduces project background and contact information.  
common.php数据库初始化逻辑，定义 settings、filings、admins 和 travel_logs 表，包含 background_image 字段的迁移逻辑。Database initialization logic, defines settings, filings, admins, and travel_logs tables, includes migration logic for background_image field.  
send_mail.php邮件发送功能，用于通知用户备案审核结果。Email sending function, used to notify users of filing review results.  
style.css美化样式文件，包含响应式设计、毛玻璃效果、粒子动画、页面切换动画和 GitHub 角标样式，使用 BootCDN 的 Noto Sans SC 字体。Styling file, includes responsive design, glassmorphism effects, particle animations, page transition animations, and GitHub badge styles, using Noto Sans SC font from BootCDN.


安装步骤 / Installation Steps

克隆仓库或下载发行版本 / Clone the Repository or Download Release  
通过 Git 克隆 / Clone via Git:  git clone https://github.com/bbb-lsy07/dBd-Filing.git
cd dBd-Filing


或直接下载发行版本并解压到你的网站目录 / Or download the release and extract it to your web directory.


配置服务器 / Configure the Server  
将项目文件夹放入 Web 服务器目录（如 Apache 的 htdocs 或 Nginx 的 html）。Place the project folder in the web server directory (e.g., Apache’s htdocs or Nginx’s html).  
确保服务器支持 PHP（推荐 PHP 7.4 或更高版本）并启用 SQLite 扩展。Ensure the server supports PHP (PHP 7.4 or higher recommended) with the SQLite extension enabled.


设置权限 / Set Permissions  
确保项目目录可写，以便自动创建 database.sqlite 文件。Ensure the project directory is writable to auto-create the database.sqlite file.  
示例（Linux 系统） / Example (Linux System):  chmod -R 755 .




访问项目 / Access the Project  
在浏览器中输入项目地址（如 http://localhost/dBd-Filing/index.php）。Enter the project URL in your browser (e.g., http://localhost/dBd-Filing/index.php).  
系统会自动创建 SQLite 数据库，初始化管理员账户（admin / 123456）和默认设置（包括 background_image）。The system will auto-create the SQLite database, initialize the admin account (admin / 123456), and set default settings (including background_image).


验证数据库迁移 / Verify Database Migration  
首次运行后，检查 database.sqlite 是否包含 settings 表，且 background_image 字段存在（默认值 https://www.dmoe.cc/random.php）。After the first run, verify that database.sqlite contains the settings table with the background_image field (default: https://www.dmoe.cc/random.php).




使用说明 / Usage Instructions
用户端 / User Side

提交备案 / Submit Filing  
访问 join.php，填写网站名称、地址、描述和联系邮箱，提交后获取备案号和 HTML 代码。Visit join.php, fill out website name, URL, description, and contact email, and receive a filing number and HTML code upon submission.


记录网站迁跃 / Log Website Travel  
访问 travel.php，输入8位迁跃编号、网站名称和地址，提交后查看最近5条迁跃记录。Visit travel.php, enter an 8-digit travel number, website name, and URL, and view the latest 5 travel logs after submission.


查询备案 / Query Filing  
访问 query.php，输入备案号或网站地址查看详情。Visit query.php, enter a filing number or URL to view details.


查看公示 / View Public Records  
访问 public.php，查看所有通过审核的备案记录。Visit public.php to view all approved filings.



管理员端 / Admin Side

登录后台 / Log into Admin Panel  
访问 login.php，使用默认账户 admin / 123456 登录（首次运行自动创建）。Visit login.php and log in with the default account admin / 123456 (auto-created on first run).


管理备案 / Manage Filings  
在 admin.php 中查看所有备案记录，支持通过、拒绝或删除操作。View all filing records in admin.php, with options to approve, reject, or delete.


设置背景图 / Set Background Image  
在 admin.php 的“站点设置”中，输入背景图 URL（如 https://www.dmoe.cc/random.php），保存后全局应用。In admin.php under "Site Settings," enter a background image URL (e.g., https://www.dmoe.cc/random.php) and save to apply globally.


修改账户 / Modify Account  
点击“修改账户”，输入新用户名和密码，保存后需重新登录。Click "Modify Account," enter a new username and password, and re-login after saving.


退出登录 / Log Out  
点击“退出登录”跳转回登录页面。Click "Log Out" to return to the login page.




注意事项 / Notes

安全性 / Security  
默认管理员账户密码为 admin / 123456，建议首次登录后立即修改。The default admin account is admin / 123456; change it immediately after the first login.  
项目仅用于娱乐，未经过严格的安全加固，不建议用于生产环境。This project is for entertainment only, not hardened for security, and not recommended for production use.


数据库 / Database  
SQLite 文件（database.sqlite）会在首次运行时自动创建，需确保目录可写。The SQLite file (database.sqlite) is auto-created on first run; ensure the directory is writable.  
若数据库缺少 background_image 字段，common.php 会自动添加（默认值 https://www.dmoe.cc/random.php）。If the background_image field is missing, common.php will auto-add it (default: https://www.dmoe.cc/random.php).


字体加载 / Font Loading  
使用 BootCDN 的 Noto Sans SC 字体，优化国内访问速度。若仍慢，可将字体文件托管到本地。Uses Noto Sans SC from BootCDN to optimize loading speed in China. If still slow, host font files locally.


表格显示 / Table Display  
admin.php 和 public.php 的表格支持水平和垂直滚动，防止内容溢出，表头固定以提高可读性。Tables in admin.php and public.php support horizontal and vertical scrolling to prevent overflow, with fixed headers for better readability.


域名配置 / Domain Configuration  
代码中的链接使用相对路径，部署时无需修改。若需绝对路径，请在 admin.php 的“站点设置”中更新 site_url。Links use relative paths; no changes needed for deployment. For absolute paths, update site_url in admin.php under "Site Settings."




贡献指南 / Contribution Guide
欢迎提交 Issue 或 Pull Request！以下是贡献步骤：We welcome Issues or Pull Requests! Here are the steps to contribute:  

Fork 本仓库 / Fork this repository  
创建你的功能分支 / Create your feature branch  git checkout -b feature/YourFeature


提交更改 / Submit changes  git commit -m "Add YourFeature"


推送到远程分支 / Push to the remote branch  git push origin feature/YourFeature


创建 Pull Request / Create a Pull Request

建议改进方向 / Suggested Improvements

添加分页功能以优化大量备案记录的显示 / Add pagination to optimize display of large numbers of filing records.  
本地化字体文件以进一步提升加载速度 / Localize font files to further improve loading speed.  
添加多语言支持 / Add multi-language support.  
增强安全性（如防止 SQL 注入、CSRF 保护） / Enhance security (e.g., prevent SQL injection, CSRF protection).  
支持批量审核功能 / Support batch review functionality.  
添加备案状态通知（通过邮箱或站内消息） / Add filing status notifications (via email or in-site messages).  
优化粒子动画性能 / Optimize particle animation performance.


开源许可 / Open Source License
本项目采用 MIT 许可证，欢迎自由使用和修改。This project is licensed under the MIT License, free to use and modify.

联系方式 / Contact

作者 / Author: bbb-lsy07  
GitHub: https://github.com/bbb-lsy07  
问题反馈 / Feedback: 请提交 IssuePlease submit an Issue

感谢使用和支持！ / Thank you for using and supporting!
