# Campus Frontend Implementation Guide

## Overview

This document outlines the complete frontend implementation for the Campus Website System, which provides campus-specific websites with dynamic content, responsive design, and SEO optimization.

## System Architecture

### Core Components

1. **Frontend Helper Functions** (`core/functions/frontend.php`)
   - Campus configuration management
   - Dynamic content retrieval
   - Menu and widget rendering
   - SEO metadata generation
   - Breadcrumb navigation
   - Pagination utilities

2. **Caching System** (`core/functions/cache.php`)
   - File-based caching with expiration
   - Campus-specific cache management
   - HTML minification
   - Output compression

3. **Campus-Specific Structure**
   ```
   {campus_name}/
   ├── config.php          # Campus configuration
   └── public/
       ├── index.php       # Homepage
       ├── posts.php       # News listing
       ├── post.php        # Single post view
       ├── about.php       # About page
       ├── 404.php         # Error page
       └── layouts/
           ├── header.php  # Header with navigation
           └── footer.php  # Footer with widgets
   ```

## Implemented Campuses

### 1. Andrews Campus
- **Campus ID**: 1
- **Theme Color**: #1e3a8a (Blue)
- **Domain**: andrews.csu.edu.ph
- **URL**: `/andrews/public/`

### 2. Carig Campus
- **Campus ID**: 3
- **Theme Color**: #7c3aed (Purple)
- **Domain**: carig.csu.edu.ph
- **URL**: `/carig/public/`

## Features

### Campus Isolation
- Each campus has its own configuration
- Content is filtered by campus ID
- Posts and menus are campus-specific
- Themes and branding are customizable

### Responsive Design
- Bootstrap 5 framework
- Mobile-first approach
- Flexible grid system
- Responsive navigation

### SEO Optimization
- Dynamic meta tags
- Structured data
- Social media integration
- Clean URLs

### Performance
- File-based caching
- HTML minification
- Lazy loading ready
- Optimized database queries

## Creating New Campuses

### Step 1: Campus Configuration
Create `{campus_name}/config.php`:

```php
<?php
/**
 * Campus Configuration
 * Replace values with actual campus information
 */

// Campus identification
define('CAMPUS_ID', X); // Replace X with campus ID from database
define('CAMPUS_CODE', 'CAMPUS_CODE'); // Replace with actual campus code

// Include core configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/functions/auth.php';
require_once __DIR__ . '/../core/functions/frontend.php';
```

### Step 2: Public Directory Structure
Create the following files in `{campus_name}/public/`:

1. **index.php** - Homepage with featured content
2. **posts.php** - News and updates listing
3. **post.php** - Individual post display
4. **about.php** - Campus information
5. **404.php** - Error page
6. **layouts/header.php** - Header with navigation
7. **layouts/footer.php** - Footer with widgets

### Step 3: Database Setup
Ensure the campus exists in the `campuses` table with:
- Unique ID
- Campus name and code
- Theme colors
- Contact information
- Domain configuration

### Step 4: Content Creation
Use the admin panel to:
- Create campus-specific posts
- Set up navigation menus
- Configure widgets
- Customize settings

## Testing

### Automated Testing
Run `test-frontend.php` to verify:
- Core functions loading
- Database connectivity
- Campus configuration
- Content isolation
- File structure
- Cache system

### Manual Testing
1. Visit campus homepages
2. Check navigation functionality
3. Verify post listings
4. Test responsive design
5. Validate SEO meta tags

## File Templates

### Campus Config Template
```php
<?php
// Campus identification
define('CAMPUS_ID', X);
define('CAMPUS_CODE', 'CODE');

// Include core configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/functions/auth.php';
require_once __DIR__ . '/../core/functions/frontend.php';
```

### Page Template Structure
```php
<?php
// Load campus configuration
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../../core/functions/frontend.php';

// Get campus data
$campus = get_campus_config();
$page_title = 'Page Title';
$page_description = 'Page description for SEO';

include 'layouts/header.php';
?>

<!-- Page content here -->

<?php include 'layouts/footer.php'; ?>
```

## Maintenance

### Regular Tasks
1. Clear cache when content changes
2. Update campus configurations
3. Monitor performance metrics
4. Test responsive breakpoints

### Troubleshooting
- Check file permissions for cache directory
- Verify database connections
- Validate campus ID configurations
- Test include paths

## Security Considerations

1. **Input Validation**: All user inputs are sanitized
2. **SQL Injection Prevention**: PDO prepared statements
3. **XSS Protection**: HTML escaping for outputs
4. **File Access**: Restricted to designated directories
5. **Session Security**: Secure session configuration

## Performance Optimization

1. **Caching**: Aggressive caching with campus-specific keys
2. **Database**: Optimized queries with proper indexing
3. **Assets**: Minified CSS/JS (future enhancement)
4. **Images**: Lazy loading implementation ready
5. **CDN**: Ready for content delivery network integration

## Future Enhancements

1. **Additional Campus Templates**: Create templates for remaining 7 campuses
2. **Advanced Widgets**: Calendar, events, photo galleries
3. **Search Functionality**: Full-text search across campus content
4. **Multi-language Support**: Internationalization framework
5. **API Integration**: RESTful API for mobile applications
6. **Analytics**: Campus-specific analytics tracking

## Support

For technical support or questions about the frontend implementation:
1. Check the test results at `/test-frontend.php`
2. Review the database structure in `/admin/`
3. Examine existing campus implementations as references
4. Test in isolation to identify specific issues

---

**Last Updated**: December 2024
**Version**: 1.0
**Status**: Production Ready
