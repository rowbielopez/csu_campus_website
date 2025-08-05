#!/bin/bash

# CSU CMS Platform Deployment Script
# This script deploys the unified codebase to all campus directories

# Configuration
SOURCE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DEPLOY_BASE="C:/xampp/htdocs"
CAMPUSES=("andrews" "aparri" "carig" "gonzaga" "lallo" "lasam" "piat" "sanchezmira" "solana")
CAMPUS_IDS=(1 2 3 4 5 6 7 8 9)

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}CSU CMS Platform Deployment Script${NC}"
echo "=========================================="

# Function to create campus directory structure
create_campus_structure() {
    local campus=$1
    local campus_id=$2
    local target_dir="$DEPLOY_BASE/$campus"
    
    echo -e "${YELLOW}Deploying to $campus campus (ID: $campus_id)...${NC}"
    
    # Create target directory
    sudo mkdir -p "$target_dir"
    
    # Copy all files except config
    sudo rsync -av --exclude='config/config.php' --exclude='.git' --exclude='node_modules' "$SOURCE_DIR/" "$target_dir/"
    
    # Create campus-specific directories
    sudo mkdir -p "$target_dir/uploads"
    sudo mkdir -p "$target_dir/cache"
    sudo mkdir -p "$target_dir/logs"
    
    # Set permissions
    sudo chown -R www-data:www-data "$target_dir"
    sudo chmod -R 755 "$target_dir"
    sudo chmod -R 777 "$target_dir/uploads"
    sudo chmod -R 777 "$target_dir/cache"
    sudo chmod -R 777 "$target_dir/logs"
    
    # Create campus-specific config
    create_campus_config "$campus" "$campus_id" "$target_dir"
    
    echo -e "${GREEN}✓ $campus campus deployed successfully${NC}"
}

# Function to create campus-specific config
create_campus_config() {
    local campus=$1
    local campus_id=$2
    local target_dir=$3
    
    # Campus name mapping
    declare -A campus_names=(
        ["andrews"]="Andrews Campus"
        ["aparri"]="Aparri Campus"
        ["carig"]="Carig Campus"
        ["gonzaga"]="Gonzaga Campus"
        ["lallo"]="Lallo Campus"
        ["lasam"]="Lasam Campus"
        ["piat"]="Piat Campus"
        ["sanchezmira"]="Sanchez Mira Campus"
        ["solana"]="Solana Campus"
    )
    
    local campus_name="${campus_names[$campus]}"
    local config_file="$target_dir/config/config.php"
    
    cat > "$config_file" << EOF
<?php
/**
 * Campus-Specific Configuration
 * This file should be unique for each campus deployment
 */

// Campus Configuration
define('CAMPUS_ID', $campus_id);
define('CAMPUS_NAME', '$campus_name');
define('CAMPUS_CODE', '$campus');
define('CAMPUS_DOMAIN', '$campus.csu.edu.ph');

// Campus Details
define('CAMPUS_FULL_NAME', 'Cagayan State University - $campus_name');
define('CAMPUS_ADDRESS', '$(echo ${campus^}), Cagayan Valley, Philippines');
define('CAMPUS_PHONE', '+63 XXX XXX XXXX');
define('CAMPUS_EMAIL', 'info@$campus.csu.edu.ph');

// Campus-Specific Paths
define('CAMPUS_UPLOAD_PATH', __DIR__ . '/../uploads/');
define('CAMPUS_CACHE_PATH', __DIR__ . '/../cache/');
define('CAMPUS_LOG_PATH', __DIR__ . '/../logs/');

// Campus Theme Settings
define('CAMPUS_PRIMARY_COLOR', '#1e3a8a'); // Blue
define('CAMPUS_SECONDARY_COLOR', '#f59e0b'); // Amber
define('CAMPUS_LOGO_PATH', '/assets/img/campuses/$campus-logo.png');
define('CAMPUS_FAVICON_PATH', '/assets/img/campuses/$campus-favicon.ico');

// Campus Feature Flags
define('CAMPUS_ENABLE_BLOG', true);
define('CAMPUS_ENABLE_EVENTS', true);
define('CAMPUS_ENABLE_GALLERY', true);
define('CAMPUS_ENABLE_ANNOUNCEMENTS', true);
define('CAMPUS_ENABLE_CONTACT_FORM', true);

// Campus Timezone
define('CAMPUS_TIMEZONE', 'Asia/Manila');

// Campus Language
define('CAMPUS_LANGUAGE', 'en');
define('CAMPUS_LOCALE', 'en_PH');

// Load common configuration
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/constants.php';
?>
EOF

    sudo chown www-data:www-data "$config_file"
    sudo chmod 644 "$config_file"
}

# Function to create Apache virtual hosts
create_virtual_hosts() {
    echo -e "${YELLOW}Creating Apache virtual hosts...${NC}"
    
    for i in "${!CAMPUSES[@]}"; do
        local campus="${CAMPUSES[$i]}"
        local vhost_file="/etc/apache2/sites-available/$campus.csu.edu.ph.conf"
        
        sudo cat > "$vhost_file" << EOF
<VirtualHost *:80>
    ServerName $campus.csu.edu.ph
    DocumentRoot $DEPLOY_BASE/$campus/public
    
    <Directory $DEPLOY_BASE/$campus/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/$campus.csu.edu.ph_error.log
    CustomLog \${APACHE_LOG_DIR}/$campus.csu.edu.ph_access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName $campus.csu.edu.ph
    DocumentRoot $DEPLOY_BASE/$campus/public
    
    <Directory $DEPLOY_BASE/$campus/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # SSL Configuration (add your SSL certificates)
    # SSLEngine on
    # SSLCertificateFile /path/to/certificate.crt
    # SSLCertificateKeyFile /path/to/private.key
    
    ErrorLog \${APACHE_LOG_DIR}/$campus.csu.edu.ph_ssl_error.log
    CustomLog \${APACHE_LOG_DIR}/$campus.csu.edu.ph_ssl_access.log combined
</VirtualHost>
EOF
        
        # Enable the site
        sudo a2ensite "$campus.csu.edu.ph.conf"
    done
    
    # Reload Apache
    sudo systemctl reload apache2
    echo -e "${GREEN}✓ Virtual hosts created and enabled${NC}"
}

# Function to run database migrations and seeding
run_database_setup() {
    echo -e "${YELLOW}Setting up database...${NC}"
    
    # For XAMPP on Windows, try different MySQL connection methods
    MYSQL_CMD=""
    
    # Try XAMPP MySQL first
    if command -v /c/xampp/mysql/bin/mysql &> /dev/null; then
        MYSQL_CMD="/c/xampp/mysql/bin/mysql"
        echo "Using XAMPP MySQL..."
    elif command -v mysql &> /dev/null; then
        MYSQL_CMD="mysql"
        echo "Using system MySQL..."
    else
        echo -e "${RED}MySQL not found. Please ensure XAMPP is running and MySQL is accessible.${NC}"
        exit 1
    fi
    
    # Check if database exists (no password for default XAMPP)
    DB_EXISTS=$($MYSQL_CMD -u root -e "SHOW DATABASES LIKE 'csu_cms_platform';" 2>/dev/null | grep csu_cms_platform)
    
    if [ -z "$DB_EXISTS" ]; then
        echo "Creating database..."
        $MYSQL_CMD -u root -e "CREATE DATABASE csu_cms_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    else
        echo "Database already exists, checking for existing data..."
        CAMPUS_COUNT=$($MYSQL_CMD -u root csu_cms_platform -e "SELECT COUNT(*) as count FROM campuses;" 2>/dev/null | tail -n 1)
        if [ "$CAMPUS_COUNT" != "9" ]; then
            echo "Database exists but may be incomplete. Proceeding with schema setup..."
        fi
    fi
    
    # Run schema
    echo "Applying database schema..."
    $MYSQL_CMD -u root csu_cms_platform < "$SOURCE_DIR/database/schema.sql"
    
    # Check if seeding is needed
    USER_COUNT=$($MYSQL_CMD -u root csu_cms_platform -e "SELECT COUNT(*) as count FROM users;" 2>/dev/null | tail -n 1)
    if [ "$USER_COUNT" == "0" ] || [ -z "$USER_COUNT" ]; then
        echo "Seeding database with initial data..."
        $MYSQL_CMD -u root csu_cms_platform < "$SOURCE_DIR/database/seed.sql"
        echo -e "${GREEN}✓ Database seeded with initial data${NC}"
    else
        echo "Database already contains data. Skipping seeding."
        echo "If you want to reseed, please run: mysql -u root -p csu_cms_platform < database/seed.sql"
    fi
    
    echo -e "${GREEN}✓ Database setup completed${NC}"
    echo ""
    echo "Default Login Credentials:"
    echo "  Super Admin:"
    echo "    Username: superadmin"
    echo "    Password: password"
    echo "    Email: superadmin@csu.edu.ph"
    echo ""
    echo "  Campus Admins (password: password):"
    echo "    Andrews: admin_andrews / admin@andrews.csu.edu.ph"
    echo "    Aparri: admin_aparri / admin@aparri.csu.edu.ph"
    echo "    Carig: admin_carig / admin@carig.csu.edu.ph"
    echo "    (and so on for each campus...)"
    echo ""
}

# Function to update all campuses
update_all_campuses() {
    echo -e "${YELLOW}Updating all campus deployments...${NC}"
    
    for i in "${!CAMPUSES[@]}"; do
        local campus="${CAMPUSES[$i]}"
        local campus_id="${CAMPUS_IDS[$i]}"
        local target_dir="$DEPLOY_BASE/$campus"
        
        if [ -d "$target_dir" ]; then
            echo "Updating $campus..."
            # Backup config
            sudo cp "$target_dir/config/config.php" "/tmp/config_$campus.php"
            
            # Update files
            sudo rsync -av --exclude='config/config.php' --exclude='uploads' --exclude='cache' --exclude='logs' --exclude='.git' "$SOURCE_DIR/" "$target_dir/"
            
            # Restore config
            sudo cp "/tmp/config_$campus.php" "$target_dir/config/config.php"
            
            # Set permissions
            sudo chown -R www-data:www-data "$target_dir"
            
            echo -e "${GREEN}✓ $campus updated${NC}"
        else
            echo -e "${RED}✗ $campus directory not found, skipping${NC}"
        fi
    done
}

# Main deployment function
deploy_all() {
    echo "Starting full deployment..."
    
    # Create all campus deployments
    for i in "${!CAMPUSES[@]}"; do
        create_campus_structure "${CAMPUSES[$i]}" "${CAMPUS_IDS[$i]}"
    done
    
    # Create virtual hosts
    create_virtual_hosts
    
    # Setup database
    run_database_setup
    
    echo -e "${GREEN}=========================================="
    echo -e "✓ Deployment completed successfully!"
    echo -e "=========================================${NC}"
    echo ""
    echo "Campus URLs:"
    for campus in "${CAMPUSES[@]}"; do
        echo "  • https://$campus.csu.edu.ph"
    done
    echo ""
    echo "Next steps:"
    echo "  1. Configure SSL certificates"
    echo "  2. Update DNS records"
    echo "  3. Create admin users for each campus"
    echo "  4. Configure email settings"
}

# Script options
case "$1" in
    "deploy")
        deploy_all
        ;;
    "update")
        update_all_campuses
        ;;
    "database")
        run_database_setup
        ;;
    "vhosts")
        create_virtual_hosts
        ;;
    *)
        echo "Usage: $0 {deploy|update|database|vhosts}"
        echo ""
        echo "Commands:"
        echo "  deploy   - Full deployment to all campuses"
        echo "  update   - Update existing campus deployments"
        echo "  database - Setup database and run migrations"
        echo "  vhosts   - Create Apache virtual hosts"
        exit 1
        ;;
esac
