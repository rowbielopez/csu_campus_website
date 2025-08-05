#!/bin/bash

# Database Validation Script
# Verifies that the multi-campus database is set up correctly

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}CSU CMS Platform - Database Validation${NC}"
echo "=========================================="

# Database credentials (adjust as needed)
DB_HOST="localhost"
DB_NAME="csu_cms_platform"
DB_USER="root"

# Function to run SQL query and return result
run_query() {
    local query="$1"
    mysql -h "$DB_HOST" -u "$DB_USER" -p "$DB_NAME" -e "$query" 2>/dev/null
}

# Function to count records in a table
count_records() {
    local table="$1"
    local campus_filter="$2"
    
    if [ -n "$campus_filter" ]; then
        local query="SELECT COUNT(*) as count FROM $table WHERE campus_id = $campus_filter;"
    else
        local query="SELECT COUNT(*) as count FROM $table;"
    fi
    
    local result=$(mysql -h "$DB_HOST" -u "$DB_USER" -p "$DB_NAME" -e "$query" 2>/dev/null | tail -n 1)
    echo "$result"
}

echo -e "${YELLOW}Checking database connection...${NC}"
DB_EXISTS=$(mysql -h "$DB_HOST" -u "$DB_USER" -p -e "SHOW DATABASES LIKE '$DB_NAME';" 2>/dev/null | grep "$DB_NAME")

if [ -z "$DB_EXISTS" ]; then
    echo -e "${RED}✗ Database '$DB_NAME' not found${NC}"
    echo "Please run the deployment script first: ./scripts/deploy.sh database"
    exit 1
else
    echo -e "${GREEN}✓ Database '$DB_NAME' found${NC}"
fi

echo ""
echo -e "${YELLOW}Validating table structure...${NC}"

# Check required tables
REQUIRED_TABLES=("campuses" "users" "posts" "categories" "menus" "menu_items" "widgets" "media" "settings" "activity_logs")

for table in "${REQUIRED_TABLES[@]}"; do
    TABLE_EXISTS=$(mysql -h "$DB_HOST" -u "$DB_USER" -p "$DB_NAME" -e "SHOW TABLES LIKE '$table';" 2>/dev/null | grep "$table")
    
    if [ -n "$TABLE_EXISTS" ]; then
        echo -e "${GREEN}✓ Table '$table' exists${NC}"
    else
        echo -e "${RED}✗ Table '$table' missing${NC}"
    fi
done

echo ""
echo -e "${YELLOW}Checking campus data...${NC}"

# Validate campus records
CAMPUS_COUNT=$(count_records "campuses")
echo "Campus records: $CAMPUS_COUNT"

if [ "$CAMPUS_COUNT" == "9" ]; then
    echo -e "${GREEN}✓ All 9 campuses configured${NC}"
    
    # List campuses
    echo ""
    echo "Campus Configuration:"
    run_query "SELECT id, name, subdomain, contact_email, theme_color FROM campuses ORDER BY id;"
    
else
    echo -e "${RED}✗ Expected 9 campuses, found $CAMPUS_COUNT${NC}"
fi

echo ""
echo -e "${YELLOW}Checking user accounts...${NC}"

# Validate user records
USER_COUNT=$(count_records "users")
echo "Total users: $USER_COUNT"

# Check for super admin
SUPER_ADMIN_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p "$DB_NAME" -e "SELECT COUNT(*) as count FROM users WHERE role = 'super_admin';" 2>/dev/null | tail -n 1)
echo "Super admin accounts: $SUPER_ADMIN_COUNT"

# Check campus admins
CAMPUS_ADMIN_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p "$DB_NAME" -e "SELECT COUNT(*) as count FROM users WHERE role = 'campus_admin';" 2>/dev/null | tail -n 1)
echo "Campus admin accounts: $CAMPUS_ADMIN_COUNT"

if [ "$SUPER_ADMIN_COUNT" -ge "1" ] && [ "$CAMPUS_ADMIN_COUNT" -ge "9" ]; then
    echo -e "${GREEN}✓ Admin accounts properly configured${NC}"
else
    echo -e "${RED}✗ Missing admin accounts${NC}"
fi

echo ""
echo -e "${YELLOW}Checking content distribution...${NC}"

# Check posts per campus
echo "Posts per campus:"
run_query "SELECT c.name as campus, COUNT(p.id) as posts FROM campuses c LEFT JOIN posts p ON c.id = p.campus_id GROUP BY c.id, c.name ORDER BY c.id;"

echo ""
echo "Categories per campus:"
run_query "SELECT c.name as campus, COUNT(cat.id) as categories FROM campuses c LEFT JOIN categories cat ON c.id = cat.campus_id GROUP BY c.id, c.name ORDER BY c.id;"

echo ""
echo -e "${YELLOW}Checking menu structure...${NC}"

# Check menus per campus
MENU_COUNT=$(count_records "menus")
MENU_ITEM_COUNT=$(count_records "menu_items")
echo "Total menus: $MENU_COUNT"
echo "Total menu items: $MENU_ITEM_COUNT"

echo ""
echo "Menus per campus:"
run_query "SELECT c.name as campus, COUNT(m.id) as menus FROM campuses c LEFT JOIN menus m ON c.id = m.campus_id GROUP BY c.id, c.name ORDER BY c.id;"

echo ""
echo -e "${YELLOW}Checking settings configuration...${NC}"

# Check settings per campus
SETTINGS_COUNT=$(count_records "settings")
echo "Total settings: $SETTINGS_COUNT"

echo ""
echo "Settings per campus:"
run_query "SELECT c.name as campus, COUNT(s.id) as settings FROM campuses c LEFT JOIN settings s ON c.id = s.campus_id GROUP BY c.id, c.name ORDER BY c.id;"

echo ""
echo -e "${YELLOW}Testing campus isolation...${NC}"

# Test data isolation by checking foreign key constraints
echo "Testing foreign key relationships:"

# Check if all posts have valid campus_id
INVALID_POSTS=$(mysql -h "$DB_HOST" -u "$DB_USER" -p "$DB_NAME" -e "SELECT COUNT(*) as count FROM posts p LEFT JOIN campuses c ON p.campus_id = c.id WHERE c.id IS NULL;" 2>/dev/null | tail -n 1)

if [ "$INVALID_POSTS" == "0" ]; then
    echo -e "${GREEN}✓ All posts have valid campus associations${NC}"
else
    echo -e "${RED}✗ Found $INVALID_POSTS posts with invalid campus associations${NC}"
fi

# Check if all users (except super admin) have valid campus_id
INVALID_USERS=$(mysql -h "$DB_HOST" -u "$DB_USER" -p "$DB_NAME" -e "SELECT COUNT(*) as count FROM users u LEFT JOIN campuses c ON u.campus_id = c.id WHERE c.id IS NULL AND u.role != 'super_admin';" 2>/dev/null | tail -n 1)

if [ "$INVALID_USERS" == "0" ]; then
    echo -e "${GREEN}✓ All campus users have valid campus associations${NC}"
else
    echo -e "${RED}✗ Found $INVALID_USERS users with invalid campus associations${NC}"
fi

echo ""
echo -e "${YELLOW}Performance check...${NC}"

# Check for proper indexing
echo "Checking database indexes:"
run_query "SHOW INDEX FROM posts WHERE Key_name LIKE '%campus%';" | head -5

echo ""
echo -e "${BLUE}=========================================="
echo -e "Database validation completed!"
echo -e "===========================================${NC}"

echo ""
echo "Next steps:"
echo "1. Test campus access by visiting each subdomain"
echo "2. Run test-campus-scoping.php from each campus directory"
echo "3. Login with the provided admin credentials"
echo "4. Verify content isolation between campuses"

echo ""
echo "Default credentials:"
echo "  Super Admin: superadmin / password"
echo "  Campus Admins: admin_[campus] / password"
