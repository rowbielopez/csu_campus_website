# University CMS Platform Architecture

## Overview
A unified content management system serving 9 CSU campuses through a shared codebase with multi-tenant architecture.

## Campus Subdomains
- andrews.csu.edu.ph
- aparri.csu.edu.ph
- carig.csu.edu.ph
- gonzaga.csu.edu.ph
- lallo.csu.edu.ph
- lasam.csu.edu.ph
- piat.csu.edu.ph
- sanchezmira.csu.edu.ph
- solana.csu.edu.ph

## Architecture Principles

### 1. Single Codebase, Multiple Deployments
- One unified codebase deployed across 9 campus folders
- Each campus folder: `/var/www/[campus_name]/`
- Identical code structure in each folder
- Only difference: `config.php` with unique `campus_id`

### 2. Multi-Tenant Database Design
- Single MySQL database for all campuses
- All tables include `campus_id` foreign key
- Content scoped by campus_id for isolation
- Shared resources where appropriate (e.g., system settings)

### 3. Technology Stack
- **Backend**: PHP 7.4+ with MySQLi/PDO
- **Frontend**: Bootstrap 5 + SB Admin Pro 2
- **Database**: MySQL 8.0+
- **Web Server**: Apache/Nginx
- **Session Management**: PHP Sessions with campus scoping

### 4. Folder Structure per Campus
```
/var/www/[campus_name]/
├── config/
│   ├── config.php          # Campus-specific configuration
│   ├── database.php        # Database connection
│   └── constants.php       # System constants
├── core/
│   ├── classes/            # Core PHP classes
│   ├── functions/          # Utility functions
│   └── middleware/         # Authentication & authorization
├── admin/                  # SB Admin Pro 2 backend
├── public/                 # Public website
├── assets/                 # CSS, JS, images
├── uploads/                # Campus-specific uploads
└── vendor/                 # Composer dependencies
```

### 5. Database Schema Design
All tables follow the pattern:
```sql
CREATE TABLE table_name (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campus_id INT NOT NULL,
    -- table-specific fields --
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (campus_id) REFERENCES campuses(id),
    INDEX idx_campus_id (campus_id)
);
```

### 6. Campus Identification Flow
1. Request comes to subdomain (e.g., andrews.csu.edu.ph)
2. Web server routes to corresponding folder (/var/www/andrews/)
3. config.php loads with campus_id
4. All database queries filtered by campus_id
5. Content rendered specific to that campus

### 7. Deployment Strategy
- Master repository with complete codebase
- Deployment script copies code to all campus folders
- Each folder maintains its own config.php
- Database migrations run once, affect all campuses
- Asset management per campus for customization

### 8. Security Considerations
- Campus data isolation via database-level filtering
- File upload restrictions per campus
- Session management scoped to campus
- Admin access controls by campus_id
- Audit logging with campus tracking

### 9. Content Management Features
- **Pages**: Static pages with campus-specific content
- **Posts/News**: Blog-style content per campus
- **Menus**: Custom navigation per campus
- **Widgets**: Reusable content blocks
- **Media**: File management per campus
- **Users**: Role-based access per campus
- **Settings**: Campus-specific configurations

### 10. Scalability & Maintenance
- Horizontal scaling by adding new campus folders
- Code updates deployed to all campuses simultaneously
- Database optimized with proper indexing on campus_id
- Caching strategy respects campus boundaries
- Monitoring and logging per campus instance
