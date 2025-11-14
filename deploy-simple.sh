#!/bin/bash

# ============================================
# Simple Auto Deploy Script
# ============================================

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Auto Deploy from GitHub${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Configuration
PROJECT_PATH="/var/www/manajemen_project"
BRANCH="master"

# Navigate to project directory
echo -e "${YELLOW}[1/6] Navigating to project directory...${NC}"
cd $PROJECT_PATH || { echo -e "${RED}Failed to navigate to project directory${NC}"; exit 1; }

# Check if git repository
echo -e "${YELLOW}[2/6] Checking git status...${NC}"
if [ ! -d .git ]; then
    echo -e "${RED}Error: Not a git repository${NC}"
    exit 1
fi

# Fetch latest changes
echo -e "${YELLOW}[3/6] Fetching latest changes from GitHub...${NC}"
git fetch origin $BRANCH

# Check if there are updates
LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/$BRANCH)

if [ $LOCAL = $REMOTE ]; then
    echo -e "${GREEN}✓ Already up to date!${NC}"
    exit 0
fi

echo -e "${YELLOW}[4/6] New changes detected! Pulling...${NC}"

# Stash local changes (if any)
git stash

# Pull latest changes
git pull origin $BRANCH || { echo -e "${RED}Failed to pull changes${NC}"; exit 1; }

echo -e "${YELLOW}[5/6] Installing dependencies...${NC}"
composer install --no-interaction --prefer-dist --optimize-autoloader

echo -e "${YELLOW}[6/6] Running migrations...${NC}"
php artisan migrate --force

# Clear cache
echo -e "${YELLOW}Clearing cache...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  ✓ Deployment Successful!${NC}"
echo -e "${GREEN}========================================${NC}"
