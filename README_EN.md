
# dBd-Filing
**A Simple Open-Source Virtual Filing System**

![image](https://img.shields.io/github/stars/bbb-lsy07/dBd-Filing?style=flat-square)
![image](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)

> **Important Notice**: This system is intended solely for technical learning and entertainment. It has no official filing authority and must not be used for illegal purposes or to store real sensitive information!

## 📌 Project Overview
A virtual ICP filing simulation system built with **PHP + SQLite**, supporting virtual filing submissions, website migration logs, filing status queries, and admin management. The project features a responsive design with dynamic backgrounds, frosted glass effects, and page transition animations. It is fully open-source, and contributions are welcome.

### Tech Stack:
- **Backend**: PHP 7.4+, SQLite (no third-party database dependencies)
- **Frontend**: HTML/CSS (responsive layout), JavaScript (particle animations, 3D stars)
- **Email Functionality**: PHPMailer (`vendor/PHPMailer/src`)

## ✨ Core Features
| **Feature Module** | **Description** | **Related File** |
|--------------------|-----------------|------------------|
| **Filing Submission** | Submit website info to generate a virtual filing number and embeddable HTML code | `join.php` |
| **Filing Query** | Query by filing number or website address, with email masking for privacy, and downloadable filing certificate | `query.php` |
| **Filing Modification** | Submit requests to modify filing info, requiring email verification and admin approval | `change.php` / `change_verify.php` / `process_change.php` |
| **Migration Logs (Starlink Shuttle)** | Randomly displays approved websites, simulating a Starlink shuttle experience, records migration info, and shows the latest 5 logs | `travel.php` |
| **Admin Panel** | Review/delete filings, configure dynamic backgrounds, manage admin accounts, **view data statistics**, **manual website health checks**, **online system updates** | `admin.php` / `admin_statistics.php` |
| **Public Page** | Display all approved filings in a responsive table with scrolling | `public.php` |
| **Dynamic Background** | Admins can set global background images (supports external URLs, e.g., `https://www.dmoe.cc/random.php`), with cool 3D star backgrounds on some pages | `admin.php` + `style.css` + `travel.php` |
| **Lightweight Database** | SQLite auto-creates tables: `settings`, `filings`, `admins`, `travel_logs`, supporting website health status and version information storage | `common.php` + `database.sqlite` |
| **Email Notifications** | Supports email notifications for filing submissions, approval/rejection results, and website health anomalies | `send_mail.php` |
| **Website Health Check** | Periodically or manually checks the online status of approved websites, sending notifications if anomalies are found | `check_website_health.php` / `process_health_check.php` |
| **Online System Update** | Admin panel provides an online update feature to check for and install the latest version | `process_update.php` |
| **Forced Password Reset on First Login** | Forces administrators to change the initial password upon first login, enhancing security | `login.php` |
| **CSRF Protection** | All form requests include CSRF token validation to prevent cross-site request forgery attacks | `process.php` / `process_filing.php` / `process_change.php` etc. |

## 🗂️ File Structure
```
dBd-Filing/  
├── vendor/                  # Dependency libraries  
│   ├── PHPMailer/           # PHPMailer email component (official library)  
│   │   ├── src/  
│   │   │   ├── Exception.php  
│   │   │   ├── PHPMailer.php  
│   │   │   └── SMTP.php  
│   │   └── (other PHPMailer files...)  
├── LICENSE                  # MIT License  
├── README.md                # Project documentation (Chinese)  
├── README_EN.md             # Project documentation (English)  
├── about.php                # About page  
├── admin.php                # Admin management interface  
├── admin_statistics.php     # Admin data statistics module  
├── admin_version_check.php  # (Legacy) Admin version check stub, new versioning handled by admin.php & process_update.php  
├── api_status.php           # API status query endpoint  
├── change.php               # Filing modification query entry  
├── change_verify.php        # Filing modification email/verification code validation page  
├── check_website_health.php # Backend website health auto-check script (recommended as a Cron Job)  
├── common.php               # Database connection & shared functions (table schema, settings loading)  
├── config.php               # Global configuration (database file, update URL, default admin info, site settings, version number)  
├── dark-mode.css            # Dark mode styles (not fully enabled, for reference only)  
├── generate_certificate.php # Filing certificate generation page  
├── index.php                # Frontend homepage (query entry)  
├── join.php                 # Filing submission form  
├── login.php                # Admin login page  
├── logout.php               # Logout handler  
├── process.php              # General filing submission processing logic (some overlap with process_filing.php)  
├── process_change.php       # Handles final submission of filing modification requests  
├── process_filing.php       # Handles new filing submission requests  
├── process_health_check.php # Backend manual website health check AJAX interface  
├── process_update.php       # Handles system online update logic (SSE streaming output)  
├── public.php               # Filing public disclosure page  
├── query.php                # Filing query page  
├── send_mail.php            # Email sending function (PHPMailer wrapper)  
├── style.css                # Global styles (responsive layout, frosted glass, neon effects, dynamic background)  
├── travel.php               # Migration log submission & view page, includes 3D star background  
└── update_errors.log        # System update error log (auto-generated)  
```

**Database File**: `database.sqlite` is auto-created on first run, no manual setup required.

## 🚀 Installation Steps
### 1. Get the Code
```bash
# Clone via Git  
git clone https://github.com/bbb-lsy07/dBd-Filing.git  
cd dBd-Filing  

# Or download the Release package: https://github.com/bbb-lsy07/dBd-Filing/releases  
```

### 2. Environment Requirements
- **Web Server**: Apache/Nginx (Apache + mod_rewrite recommended)
- **PHP**: **7.4+** (requires `pdo_sqlite`, `mbstring`, `curl`, and `gd` extensions to be enabled)
- **Client**: Chrome/Firefox/Edge (modern browsers supporting CSS3 and JavaScript)

### 3. Server Configuration
**Linux/macOS**:
```bash
# Set directory write permissions (for database file, log files, and update packages)  
chmod -R 755 .  
```

**Windows**:
- Copy the project to the web server directory (e.g., XAMPP’s `htdocs`)
- Right-click folder → Properties → Security → Edit → Check “Write” permission

### 4. First Access
- Visit: `http://your-domain/admin.php`
- The system will automatically perform an environment check. If necessary PHP extensions are missing or file write permissions are insufficient, detailed error messages will be displayed. Please fix them as prompted.
- Once the environment check passes, the system will auto-create the database and initialize the admin account (`admin/123456`). **Change the password immediately after first login**!

## 📖 Usage Guide
### 🧑‍💻 User Operations
- **Submit Filing**: Visit `join.php` or `index.php` to submit filing information. Fill out the form to generate a virtual filing number and embed code.
- **Query Filing**: Visit `query.php`, enter a filing number or website address to view details. Approved filings can download a virtual certificate.
- **Modify Filing**: Visit `change.php`. After querying, you'll need to verify through the registered email to modify the information.
- **Starlink Shuttle (Migration Logs)**: Visit `travel.php` to experience randomly visiting approved websites and view recent migration records.
- **View Public Filings**: Visit `public.php` to browse all approved filing records.

### 👩‍💻 Admin Operations
- **Access Admin Panel**: Visit `login.php`. Log in with the default or custom credentials. The first login will force a password change.
- **Filing Management**: On `admin.php`, approve/reject/delete filing records. Approved filings can trigger a manual health check.
- **Data Statistics**: On `admin.php`, click "数据统计" (Data Statistics) to view an overview of filings, daily/monthly new filing charts.
- **System Settings**: On `admin.php`, click "站点设置" (Site Settings) to modify the site title, URL, welcome message, contact email, QQ group, SMTP email configuration, and global background image URL.
- **Account Management**: On `admin.php`, click "修改账户" (Modify Account) to update the administrator's username and password. You will need to log in again after saving.
- **System Update**: On `admin.php`, click "检查更新" (Check Update) to view the latest version information and install new versions online. Clicking "从GitHub获取" (Get from GitHub) can force pulling the latest code from GitHub (use with caution, this operation might lead to data loss! Please back up before proceeding!).

## ⚠️ Important Notes
### Security Risks:
- The default password `admin/123456` is insecure. **Change it immediately after first login**!
- The project has not undergone professional security audits. **Do not use in production or store sensitive data**.
- The update mechanism (`process_update.php`) directly overwrites files. Always back up before performing an update!

### Database Dependencies:
- SQLite is suitable for low-concurrency scenarios; performance may degrade under high load.
- Ensure `database.sqlite` is writable, or the system will fail to operate.
- **Recommended Cron Job for Health Check**: Configure a scheduled task (e.g., hourly) for `check_website_health.php` to ensure automatic updates of website health status and anomaly notifications.

### Font Loading:
- Uses BootCDN’s Source Han Sans for optimized access in China. If loading is slow, download fonts locally and update `style.css` paths.

## 🤝 Contribution Guide
We welcome feedback via **Issues** or code contributions via **Pull Requests**!

### Contribution Process
1.  **Fork the Repository**: Click the Fork button to create a personal branch.
2.  **Local Development**:
    ```bash
    git checkout -b feature/new-pagination  # Create feature branch  
    git checkout -b fix/csrf-protection     # Create bugfix branch  
    ```
3.  **Submit Code**: Follow PSR-2 standards, include necessary comments.
4.  **Create PR**: Clearly describe changes and await maintainer review.

### Areas for Improvement (Open for Contributions)
#### Security Enhancements:
- Password storage (currently hashed, but consider stronger hashing algorithms or key management).
- More comprehensive input validation and sanitization to prevent XSS and other injection attacks.
- Optimize the update mechanism to avoid direct file overwrites, potentially leading to data loss or permission issues.

#### Feature Optimizations:
- Paginated display for filing records (`public.php` and `admin.php`).
- Multi-language support (Simplified Chinese/English switching).
- Filing modification approval workflow (currently direct modification after verification).
- Backend implementation for the `travel.php` rating and sharing features.

#### Performance Improvements:
- Localize font files to reduce CDN dependency.
- Optimize particle animation and 3D star performance to lower memory usage.

## 📄 License
This project is licensed under the **MIT License**, allowing free use, modification, and distribution, provided the open-source declaration is retained.

**Attribution Requirement**: If using this system, include the following at the website footer or about page:
```html
Virtual filing system powered by <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank">dBd-Filing</a>
```

## 📧 Contact
- **Author**: bbb-lsy07
- **GitHub**: https://github.com/bbb-lsy07
- **Feedback**: Submit an Issue

## 📅 Version History
🚀 **Latest Version**: V2.5.3 (2025-05-05)
- **Added**: Admin backend data statistics, showing total, pending, approved, rejected filings, and daily/monthly new filing charts.
- **Added**: Approved website health check feature, supporting manual triggers and scheduled Cron Jobs, sending email notifications on anomalies.
- **Added**: Online system update feature in the admin panel to fetch and install the latest version from GitHub.
- **Added**: Filing modification process now includes email and verification code validation for enhanced security.
- **Added**: Filing certificate generation feature; approved filings can download a virtual certificate from the query page.
- **Added**: Forced password change for administrators on first login, and ability for admins to modify their account info in the backend.
- **Added**: Starlink Shuttle (Travel) page (`travel.php`), featuring a 3D star background, random website redirection, travel log recording, and client-side rating/sharing functions.
- **Optimized**: Unified the logic for filing submissions, now supporting manual filing number input (automatically generates if duplicate).
- **Optimized**: All form requests now include CSRF token validation for improved security.
- **Optimized**: PHPMailer configuration loaded from database settings for more flexible email sending.
- **Optimized**: UI enhancements, including cool neon forms, frosted glass effects, and smoother page transition animations.
- **Fixed**: Website access path issues, ensuring normal operation in different subdirectories.
- **Fixed**: Email sending failures, with more detailed error logging.
- **Fixed**: Various other bug fixes and performance optimizations.

**Full Changelog**: View [Releases](https://github.com/bbb-lsy07/dBd-Filing/releases)

---

**Thank you for your support!** If this project helps you, please give it a GitHub star ⭐ to help more developers discover it!
