#!/bin/bash

# ============================================
# Advanced Auto Deploy Script with Backup & Rollback
# ============================================

set -e  # Exit on error

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
PROJECT_PATH="/var/www/manajemen_project"
BRANCH="master"
BACKUP_DIR="/var/backups/manajemen_project"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_PATH="$BACKUP_DIR/backup_$TIMESTAMP"

# Functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Header
clear
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  🚀 Auto Deploy from GitHub${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""
log_info "Starting deployment at $(date)"
echo ""

# Step 1: Navigate to project
log_info "[1/10] Navigating to project directory..."
cd $PROJECT_PATH || { log_error "Failed to navigate to $PROJECT_PATH"; exit 1; }
log_success "In directory: $(pwd)"

# Step 2: Check git status
log_info "[2/10] Checking git repository..."
if [ ! -d .git ]; then
    log_error "Not a git repository"
    exit 1
fi
log_success "Git repository verified"

# Step 3: Fetch updates
log_info "[3/10] Fetching latest changes from GitHub..."
git fetch origin $BRANCH || { log_error "Failed to fetch from GitHub"; exit 1; }

# Step 4: Check for updates
LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/$BRANCH)

if [ $LOCAL = $REMOTE ]; then
    log_success "Already up to date! No deployment needed."
    echo ""
    echo -e "${GREEN}Current commit: $LOCAL${NC}"
    exit 0
fi

log_warning "New changes detected!"
echo -e "${YELLOW}Local:  $LOCAL${NC}"
echo -e "${YELLOW}Remote: $REMOTE${NC}"
echo ""

# Step 5: Create backup
log_info "[4/10] Creating backup..."
mkdir -p $BACKUP_DIR

# Backup files
log_info "Backing up files..."
rsync -av --exclude='node_modules' --exclude='vendor' --exclude='.git' \
      $PROJECT_PATH/ $BACKUP_PATH/ > /dev/null 2>&1
log_success "Backup created: $BACKUP_PATH"

# Backup database
log_info "Backing up database..."
DB_NAME=$(grep DB_DATABASE $PROJECT_PATH/.env | cut -d '=' -f2)
DB_USER=$(grep DB_USERNAME $PROJECT_PATH/.env | cut -d '=' -f2)
DB_PASS=$(grep DB_PASSWORD $PROJECT_PATH/.env | cut -d '=' -f2)

if [ -n "$DB_NAME" ]; then
    mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > $BACKUP_PATH/database.sql 2>/dev/null || log_warning "Database backup failed"
    log_success "Database backup completed"
fi

# Step 6: Put application in maintenance mode
log_info "[5/10] Enabling maintenance mode..."
php artisan down || log_warning "Failed to enable maintenance mode"

# Step 7: Stash local changes
log_info "[6/10] Stashing local changes..."
git stash || log_warning "No local changes to stash"

# Step 8: Pull latest changes
log_info "[7/10] Pulling latest changes from GitHub..."
if ! git pull origin $BRANCH; then
    log_error "Failed to pull changes! Rolling back..."
    git reset --hard $LOCAL
    php artisan up
    exit 1
fi
log_success "Successfully pulled latest changes"

# Step 9: Install dependencies
log_info "[8/10] Installing dependencies..."
log_info "Running composer install..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev || {
    log_error "Composer install failed! Rolling back..."
    git reset --hard $LOCAL
    php artisan up
    exit 1
}
log_success "Dependencies installed"

# Step 10: Run migrations
log_info "[9/10] Running database migrations..."
php artisan migrate --force || {
    log_error "Migration failed! Rolling back..."
    git reset --hard $LOCAL
    composer install --no-interaction
    php artisan migrate:rollback
    php artisan up
    exit 1
}
log_success "Migrations completed"

# Step 11: Clear cache
log_info "[10/10] Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
log_success "Cache cleared"

# Step 12: Bring application back up
log_info "Disabling maintenance mode..."
php artisan up
log_success "Application is back online"

# Step 13: Set permissions
log_info "Setting proper permissions..."
chown -R www-data:www-data $PROJECT_PATH/storage $PROJECT_PATH/bootstrap/cache
chmod -R 775 $PROJECT_PATH/storage $PROJECT_PATH/bootstrap/cache
log_success "Permissions set"

# Cleanup old backups (keep last 5)
log_info "Cleaning up old backups..."
cd $BACKUP_DIR
ls -t | tail -n +6 | xargs -r rm -rf
log_success "Old backups cleaned"

# Summary
echo ""
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  ✓ Deployment Successful!${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""
log_success "Deployed at: $(date)"
log_success "Backup location: $BACKUP_PATH"
log_success "New commit: $REMOTE"
echo ""
log_info "Check logs for any warnings"
echo ""
