# CSU CMS Platform - Multi-Campus Content Management System

A unified content management system designed to serve 9 Cagayan State University campuses through a shared codebase with multi-tenant architecture.

## 🌟 Features

### Core Architecture
- **Single Codebase**: One unified codebase deployed across 9 campus directories
- **Multi-Tenant Database**: Single MySQL database with campus_id scoping
- **Horizontal Scaling**: Add new campuses by copying folders and updating config
- **Campus Isolation**: Complete data separation between campuses

### Technology Stack
- **Backend**: PHP 7.4+ with MySQLi/PDO
- **Frontend**: Bootstrap 5 + SB Admin Pro 2
- **Database**: MySQL 8.0+
- **Admin Theme**: SB Admin Pro 2 with campus-specific customization

### Content Management
- **Pages**: Static content with custom templates
- **Posts/News**: Blog-style content with categories
- **Events**: Campus event management
- **Media Library**: File management with campus isolation
- **Carousel Management**: Full-featured carousel content management with drag-and-drop reordering
- **Menus**: Custom navigation per campus
- **Widgets**: Reusable content blocks with image hyperlink support
- **Users**: Role-based access control per campus

## 🏫 Campus Domains

| Campus | Domain | Campus ID |
|--------|--------|-----------|
| Andrews Campus | andrews.csu.edu.ph | 1 |
| Aparri Campus | aparri.csu.edu.ph | 2 |
| Carig Campus | carig.csu.edu.ph | 3 |
| Gonzaga Campus | gonzaga.csu.edu.ph | 4 |
| Lallo Campus | lallo.csu.edu.ph | 5 |
| Lasam Campus | lasam.csu.edu.ph | 6 |
| Piat Campus | piat.csu.edu.ph | 7 |
| Sanchez Mira Campus | sanchezmira.csu.edu.ph | 8 |
| Solana Campus | solana.csu.edu.ph | 9 |

## 📁 Directory Structure

```
/var/www/[campus_name]/
├── config/
│   ├── config.php          # Campus-specific configuration
│   ├── database.php        # Database connection
│   └── constants.php       # System constants
├── core/
│   ├── bootstrap.php       # Application initialization
│   ├── classes/            # Core PHP classes
│   │   ├── Campus.php      # Campus management
│   │   ├── User.php        # User authentication
│   │   └── Content.php     # Content management
│   └── functions/          # Utility functions
├── admin/                  # SB Admin Pro 2 backend
│   ├── dashboard.php       # Main admin dashboard
│   ├── pages/              # Page management
│   ├── posts/              # Post management
│   ├── users/              # User management
│   └── settings/           # Campus settings
├── public/                 # Public website
├── assets/                 # CSS, JS, images
├── uploads/                # Campus-specific uploads
├── cache/                  # Cache files
├── logs/                   # Error logs
└── database/               # Database schema
    └── schema.sql          # Complete database structure
```

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server

### Installation
1. **Database Setup**
   ```bash
   mysql -u root -p -e "CREATE DATABASE csu_cms_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p csu_cms_platform < database/schema.sql
   ```

2. **Deploy to Campus Directories**
   ```bash
   # Make deployment script executable
   chmod +x scripts/deploy.sh
   
   # Run full deployment
   ./scripts/deploy.sh deploy
   ```

3. **Configure Web Server**
   - Set up virtual hosts for each campus domain
   - Point document root to `/var/www/[campus]/public`
   - Enable URL rewriting

4. **Access Admin Panel**
   - Visit `https://andrews.csu.edu.ph/admin`
   - Create initial admin user
   - Configure campus settings

## 🔧 Configuration

Each campus requires a unique `config/config.php` file with:
- Campus ID (1-9)
- Campus name and contact details
- Theme colors and branding
- Feature toggles
- File paths

## 🛡️ Security Features

- **Campus Data Isolation**: All data scoped by campus_id
- **Role-based Access Control**: Granular permissions
- **CSRF Protection**: Built-in token validation
- **Input Sanitization**: XSS protection
- **Activity Logging**: Complete audit trail

## 📊 Admin Dashboard

Built with SB Admin Pro 2, featuring:
- Campus-specific statistics
- Content management tools
- **Carousel Management**: Advanced carousel editor with image cropping, drag-and-drop reordering, and live preview
- User administration
- Media library with campus isolation
- Settings management
- Modern toast notifications
- Responsive design

## 🎨 Customization

- Campus-specific themes and colors
- Logo and branding per campus
- Feature toggles (blog, events, gallery)
- Custom templates and layouts

## 🔄 Updates & Maintenance

```bash
# Validate database structure
./scripts/validate-database.sh

# Update all campuses (when deployment scripts are available)
# rsync -av --exclude='config/config.php' source/ /var/www/campus_name/
```

**Note**: Frontend build scripts have been removed as this system uses direct PHP templating without Node.js build processes.

## 📚 Documentation

- Database Schema: [schema.sql](database/schema.sql)
- Database Validation: [validate-database.sh](scripts/validate-database.sh)
- Campus Configurations: [config/campus-configs/](config/campus-configs/)
- Email Setup Guide: [EMAIL_SETUP_GUIDE.md](EMAIL_SETUP_GUIDE.md)
- Login Credentials: [LOGIN_CREDENTIALS.md](LOGIN_CREDENTIALS.md)

## 🆘 Support

For technical support:
- Email: it-support@csu.edu.ph
- Development Team: CSU IT Department

---

**Version**: 1.1.0  
**Last Updated**: August 11, 2025  
**Developed by**: CSU IT Department

*Built on SB Admin Pro 2 by Start Bootstrap*

## 🆕 Recent Updates (v1.1.0)

- ✅ **Carousel Management System**: Full-featured carousel content management with drag-and-drop reordering, image cropping, and live preview
- ✅ **Modern UI**: Replaced alert dialogs with Bootstrap toast notifications
- ✅ **Image Processing**: Advanced image handling with automatic optimization and thumbnail generation
- ✅ **Campus-Specific URLs**: Fixed frontend links to use correct campus-specific paths
- ✅ **Code Cleanup**: Removed unnecessary Node.js build scripts and focused on PHP-only architecture
- ✅ **Widget System**: Enhanced widget templates with proper error handling and debugging support
