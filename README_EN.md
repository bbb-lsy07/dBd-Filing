# dBd-Filing
**A Simple Open-Source Virtual Filing System**

![image](https://img.shields.io/github/stars/bbb-lsy07/dBd-Filing?style=flat-square)
![image](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)

> **Important Notice**: This system is intended solely for technical learning and entertainment. It has no official filing authority and must not be used for illegal purposes or to store real sensitive information!

## 📌 Project Overview
A virtual ICP filing simulation system built with **PHP + SQLite**, supporting virtual filing submissions, website migration logs, filing status queries, and admin management. The project features a responsive design with dynamic backgrounds, frosted glass effects, and page transition animations. It is fully open-source, and contributions are welcome.

### Tech Stack:
- **Backend**: PHP 7.4+, SQLite (no third-party database dependencies)
- **Frontend**: HTML/CSS (responsive layout), JavaScript (particle animations)
- **Email Functionality**: PHPMailer (`vendor/PHPMailer/src`)

## ✨ Core Features
| **Feature Module** | **Description** | **Related File** |
|--------------------|-----------------|------------------|
| **Filing Submission** | Submit website info to generate a virtual filing number and embeddable HTML code | `join.php` |
| **Filing Query** | Query by filing number or website address, with email masking for privacy | `query.php` |
| **Filing Modification** | Submit requests to modify filing info (requires admin approval) | `change.php` |
| **Migration Logs** | Record website migration info, view the latest 5 logs | `travel.php` |
| **Admin Panel** | Review/delete filings, configure dynamic backgrounds, manage admin accounts | `admin.php` |
| **Public Page** | Display all approved filings in a responsive table with scrolling | `public.php` |
| **Dynamic Background** | Admins can set global background images (supports external URLs, e.g., `https://www.dmoe.cc/random.php`) | `admin.php` + `style.css` |
| **Lightweight Database** | SQLite auto-creates tables: `settings`, `filings`, `admins`, `travel_logs` | `common.php` + `database.sqlite` |

## 🗂️ File Structure
```
dBd-Filing/  
├── vendor/                  # Dependency libraries  
│   └── PHPMailer/src/       # PHPMailer email component (official library)  
├── LICENSE                  # MIT License  
├── README.md                # Project documentation  
├── about.php                # About page  
├── admin.php                # Admin management interface  
├── change.php               # Filing modification page  
├── common.php               # Database connection & shared functions (table schema)  
├── index.php                # Frontend homepage (query entry)  
├── join.php                 # Filing submission form  
├── login.php                # Admin login page (default: admin/123456)  
├── logout.php               # Logout handler  
├── process.php              # General processing logic (e.g., parameter validation)  
├── process_change.php       # Handle filing modification requests  
├── process_filing.php       # Handle new filing submissions  
├── process_update.php       # Handle system settings updates (e.g., background image)  
├── public.php               # Filing public disclosure page  
├── query.php                # Filing query page  
├── send_mail.php            # Email sending function (audit result notifications)  
├── style.css                # Global styles (responsive layout, frosted glass effects)  
├── travel.php               # Migration log submission & view page  
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
- **PHP**: 7.4+ (requires `pdo_sqlite` and `mbstring` extensions)
- **Client**: Chrome/Firefox/Edge (modern browsers supporting CSS3 and JavaScript)

### 3. Server Configuration
**Linux/macOS**:
```bash
# Set directory write permissions (for database file generation)  
chmod -R 755 .  
```

**Windows**:
- Copy the project to the web server directory (e.g., XAMPP’s `htdocs`)
- Right-click folder → Properties → Security → Edit → Check “Write” permission

### 4. First Access
- Visit: `http://your-domain/index.php`
- The system auto-creates the database and initializes the admin account (`admin/123456`). **Change the password immediately after first login**!

## 📖 Usage Guide
### 🧑‍💻 User Operations
- **Submit Filing**: Visit `join.php`, fill out the form to generate a filing number and embed code.
- **Query Filing**: Visit `query.php`, enter a filing number or website address to view details.
- **Log Migration**: Visit `travel.php`, submit an 8-digit migration code and website info, view recent logs.
- **View Public Filings**: Visit `public.php` to browse all approved filings.

### 👩‍💻 Admin Operations
- **Access Admin Panel**: Visit `login.php`, log in with default or custom credentials.
- **Audit Management**: Use `admin.php` to approve/reject/delete filing records.
- **System Settings**:
  - **Background Image**: Set an image URL in “Site Settings” and save.
  - **Domain Config**: Update `site_url` in “Site Settings” for absolute paths.
  - **Account Management**: Click “Modify Account” to update username/password, then re-login.

## ⚠️ Important Notes
### Security Risks:
- The default password `admin/123456` is insecure. **Change it immediately after first login**!
- The project has not undergone professional security audits. **Do not use in production or store sensitive data**.

### Database Dependencies:
- SQLite is suitable for low-concurrency scenarios; performance may degrade under high load.
- Ensure `database.sqlite` is writable, or the system will fail to operate.

### Font Loading:
- Uses BootCDN’s Source Han Sans for optimized access in China. If loading is slow, download fonts locally and update `style.css` paths.

## 🤝 Contribution Guide
We welcome feedback via **Issues** or code contributions via **Pull Requests**!

### Contribution Process
1. **Fork the Repository**: Click the Fork button to create a personal branch.
2. **Local Development**:
   ```bash
   git checkout -b feature/new-pagination  # Create feature branch  
   git checkout -b fix/csrf-protection     # Create bugfix branch  
   ```
3. **Submit Code**: Follow PSR-2 standards, include necessary comments.
4. **Create PR**: Clearly describe changes and await maintainer review.

### Areas for Improvement (Open for Contributions)
#### Security Enhancements:
- Add CSRF protection and SQL injection prevention (use PDO prepared statements).
- Encrypt password storage (replace plaintext storage).

#### Feature Optimizations:
- Paginated display for filings (`public.php` and `admin.php`).
- Multi-language support (Simplified Chinese/English switching).

#### Performance Improvements:
- Localize font files to reduce CDN dependency.
- Optimize particle animation performance to lower memory usage.

## 📄 License
This project is licensed under the **MIT License**, allowing free use, modification, and distribution, provided the open-source declaration is retained.

**Attribution Requirement**: If using this system, include the following at the website footer or about page:
```
Virtual filing system powered by <a href="https://github.com/bbb-lsy07/dBd-Filing" target="_blank">dBd-Filing</a>
```

## 📧 Contact
- **Author**: bbb-lsy07
- **GitHub**: https://github.com/bbb-lsy07
- **Feedback**: Submit an Issue

## 📅 Version History
🚀 **Latest Version**: V2.5.0 (2025-05-05)
- **Added**: Filing modification audit process (requires secondary admin confirmation).
- **Improved**: Admin table loading speed, added audit status filtering.
- **Fixed**: Email sending failures (optimized PHPMailer configuration).

**Full Changelog**: View [Releases](https://github.com/bbb-lsy07/dBd-Filing/releases)

---

**Thank you for your support!** If this project helps you, please give it a GitHub star ⭐ to help more developers discover it!
