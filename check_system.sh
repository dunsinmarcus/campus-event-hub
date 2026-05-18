#!/bin/bash
# Campus Event Hub - System Verification Script
# Run this script to verify all components are in place

echo "======================================"
echo "Campus Event Hub - System Check"
echo "======================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check counter
checks_passed=0
checks_failed=0

# Function to check file exists
check_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✓${NC} $1"
        ((checks_passed++))
    else
        echo -e "${RED}✗${NC} $1 - MISSING"
        ((checks_failed++))
    fi
}

# Function to check directory exists
check_dir() {
    if [ -d "$1" ]; then
        echo -e "${GREEN}✓${NC} $1"
        ((checks_passed++))
    else
        echo -e "${RED}✗${NC} $1 - MISSING"
        ((checks_failed++))
    fi
}

# Check PHP version
echo "Checking PHP Installation..."
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1)
    echo -e "${GREEN}✓${NC} PHP installed: $PHP_VERSION"
    ((checks_passed++))
else
    echo -e "${RED}✗${NC} PHP not installed"
    ((checks_failed++))
fi
echo ""

# Check MySQL
echo "Checking MySQL Installation..."
if command -v mysql &> /dev/null; then
    echo -e "${GREEN}✓${NC} MySQL client installed"
    ((checks_passed++))
else
    echo -e "${RED}✗${NC} MySQL client not installed"
    ((checks_failed++))
fi
echo ""

# Check directories
echo "Checking Directory Structure..."
check_dir "api"
check_dir "classes"
check_dir "config"
check_dir "cron"
check_dir "database"
check_dir "public"
check_dir "public/css"
check_dir "public/js"
echo ""

# Check configuration files
echo "Checking Configuration Files..."
check_file "config/database.php"
check_file "config/config.example.php"
echo ""

# Check API files
echo "Checking API Files..."
check_file "api/auth.php"
check_file "api/get_events.php"
check_file "api/get_event_details.php"
check_file "api/rsvp.php"
check_file "api/reminder.php"
check_file "api/user_profile.php"
echo ""

# Check class files
echo "Checking Class Files..."
check_file "classes/Database.php"
check_file "classes/Event.php"
check_file "classes/RSVP.php"
check_file "classes/Reminder.php"
check_file "classes/User.php"
echo ""

# Check frontend files
echo "Checking Frontend Files..."
check_file "public/index.php"
check_file "public/login.php"
check_file "public/register.php"
check_file "public/css/style.css"
check_file "public/css/auth.css"
check_file "public/css/events.css"
check_file "public/js/main.js"
check_file "public/js/auth.js"
check_file "public/js/events.js"
echo ""

# Check database files
echo "Checking Database Files..."
check_file "database/schema.sql"
check_file "database/test_data.sql"
echo ""

# Check cron files
echo "Checking Cron Files..."
check_file "cron/send_reminders.php"
echo ""

# Check documentation
echo "Checking Documentation..."
check_file "README.md"
check_file "QUICK_START.md"
check_file "INSTALLATION.md"
check_file "DEVELOPER.md"
check_file "ADMIN.md"
check_file "PROJECT_SUMMARY.md"
echo ""

# Check configuration
echo "Checking Configuration..."
if grep -q "DB_HOST" config/database.php; then
    echo -e "${GREEN}✓${NC} Database configuration found"
    ((checks_passed++))
else
    echo -e "${RED}✗${NC} Database configuration incomplete"
    ((checks_failed++))
fi
echo ""

# Check PHP extensions
echo "Checking PHP Extensions..."
if php -m | grep -q mysqli; then
    echo -e "${GREEN}✓${NC} MySQLi extension available"
    ((checks_passed++))
else
    echo -e "${YELLOW}⚠${NC} MySQLi extension not found (may be required)"
fi

if php -m | grep -q json; then
    echo -e "${GREEN}✓${NC} JSON extension available"
    ((checks_passed++))
else
    echo -e "${RED}✗${NC} JSON extension required"
    ((checks_failed++))
fi
echo ""

# Summary
echo "======================================"
echo "System Check Summary"
echo "======================================"
echo -e "Checks Passed: ${GREEN}$checks_passed${NC}"
echo -e "Checks Failed: ${RED}$checks_failed${NC}"
echo ""

if [ $checks_failed -eq 0 ]; then
    echo -e "${GREEN}✓ All checks passed! System is ready.${NC}"
    echo ""
    echo "Next steps:"
    echo "1. Edit config/database.php with your database credentials"
    echo "2. Import the database schema: mysql -u root -p campus_event_hub < database/schema.sql"
    echo "3. Start the web server: cd public && php -S localhost:8000"
    echo "4. Visit http://localhost:8000"
    exit 0
else
    echo -e "${RED}✗ Some checks failed. Please review the errors above.${NC}"
    exit 1
fi
