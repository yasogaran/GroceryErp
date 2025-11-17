#!/bin/bash

################################################################################
# Grocery Shop ERP - Production Deployment Script
# Version: 1.0
# Description: Automated deployment script for production server
################################################################################

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="${SCRIPT_DIR}"
LOG_FILE="${APP_DIR}/storage/logs/deployment.log"
BACKUP_DIR="${APP_DIR}/storage/deployment-backups"
DATE=$(date +"%Y%m%d_%H%M%S")

# Function to print colored messages
print_message() {
    local color=$1
    local message=$2
    echo -e "${color}[$(date '+%Y-%m-%d %H:%M:%S')] ${message}${NC}"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ${message}" >> "$LOG_FILE"
}

print_success() { print_message "$GREEN" "✓ $1"; }
print_error() { print_message "$RED" "✗ $1"; }
print_warning() { print_message "$YELLOW" "⚠ $1"; }
print_info() { print_message "$BLUE" "ℹ $1"; }

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to create backup before deployment
create_pre_deployment_backup() {
    print_info "Creating pre-deployment backup..."

    mkdir -p "$BACKUP_DIR"

    # Backup database
    if command_exists mysqldump; then
        DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2)
        DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2)
        DB_PASS=$(grep DB_PASSWORD .env | cut -d '=' -f2)

        mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/database_${DATE}.sql"
        print_success "Database backup created: database_${DATE}.sql"
    else
        print_warning "mysqldump not found, skipping database backup"
    fi

    # Backup .env file
    cp .env "$BACKUP_DIR/env_${DATE}.backup"
    print_success "Environment file backed up"

    # Backup current codebase (exclude vendor and node_modules)
    tar -czf "$BACKUP_DIR/codebase_${DATE}.tar.gz" \
        --exclude='vendor' \
        --exclude='node_modules' \
        --exclude='storage/logs' \
        --exclude='storage/framework/cache' \
        --exclude='storage/framework/sessions' \
        --exclude='storage/framework/views' \
        .
    print_success "Codebase backup created: codebase_${DATE}.tar.gz"
}

# Function to enable maintenance mode
enable_maintenance_mode() {
    print_info "Enabling maintenance mode..."
    php artisan down --retry=60 --secret="deployment-secret-key"
    print_success "Maintenance mode enabled"
}

# Function to disable maintenance mode
disable_maintenance_mode() {
    print_info "Disabling maintenance mode..."
    php artisan up
    print_success "Application is now live!"
}

# Function to pull latest code from Git
pull_latest_code() {
    print_info "Pulling latest code from Git..."

    # Stash any local changes
    if [[ -n $(git status -s) ]]; then
        print_warning "Stashing local changes..."
        git stash
    fi

    # Pull latest code
    git pull origin $(git branch --show-current)
    print_success "Code updated successfully"
}

# Function to install/update Composer dependencies
install_composer_dependencies() {
    print_info "Installing Composer dependencies..."

    if ! command_exists composer; then
        print_error "Composer is not installed!"
        exit 1
    fi

    composer install --no-dev --optimize-autoloader --no-interaction
    print_success "Composer dependencies installed"
}

# Function to install/update NPM dependencies and build assets
build_frontend_assets() {
    print_info "Building frontend assets..."

    if ! command_exists npm; then
        print_warning "NPM is not installed, skipping asset build"
        return
    fi

    npm ci --production
    npm run build
    print_success "Frontend assets built successfully"
}

# Function to run database migrations
run_migrations() {
    print_info "Running database migrations..."
    php artisan migrate --force
    print_success "Migrations completed"
}

# Function to clear and optimize caches
optimize_application() {
    print_info "Optimizing application..."

    # Clear all caches
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear

    # Optimize for production
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache

    # Warm up application cache
    php artisan cache:warmup

    print_success "Application optimized"
}

# Function to set correct permissions
set_permissions() {
    print_info "Setting file permissions..."

    # Set ownership (adjust user:group as needed)
    # chown -R www-data:www-data "$APP_DIR"

    # Set directory permissions
    find "$APP_DIR/storage" -type d -exec chmod 775 {} \;
    find "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} \;

    # Set file permissions
    find "$APP_DIR/storage" -type f -exec chmod 664 {} \;
    find "$APP_DIR/bootstrap/cache" -type f -exec chmod 664 {} \;

    # Secure .env file
    chmod 600 "$APP_DIR/.env"

    print_success "Permissions set correctly"
}

# Function to restart queue workers
restart_queue_workers() {
    print_info "Restarting queue workers..."

    if command_exists supervisorctl; then
        supervisorctl restart groceryerp-worker:*
        print_success "Queue workers restarted"
    else
        print_warning "Supervisor not found, manually restart queue workers if needed"
    fi
}

# Function to restart PHP-FPM
restart_php_fpm() {
    print_info "Restarting PHP-FPM..."

    if command_exists systemctl; then
        sudo systemctl restart php8.2-fpm
        print_success "PHP-FPM restarted"
    else
        print_warning "systemctl not found, manually restart PHP-FPM if needed"
    fi
}

# Function to verify deployment
verify_deployment() {
    print_info "Verifying deployment..."

    # Check if application is responding
    if php artisan about >/dev/null 2>&1; then
        print_success "Application is responding correctly"
    else
        print_error "Application verification failed!"
        exit 1
    fi

    # Check database connection
    if php artisan db:show >/dev/null 2>&1; then
        print_success "Database connection verified"
    else
        print_warning "Database connection check failed"
    fi
}

# Function to cleanup old backups (keep last 5)
cleanup_old_backups() {
    print_info "Cleaning up old deployment backups..."

    cd "$BACKUP_DIR"
    ls -t database_*.sql | tail -n +6 | xargs -r rm
    ls -t env_*.backup | tail -n +6 | xargs -r rm
    ls -t codebase_*.tar.gz | tail -n +6 | xargs -r rm

    print_success "Old backups cleaned up"
}

# Function to rollback deployment
rollback_deployment() {
    print_error "Deployment failed! Starting rollback..."

    # Find latest backup
    LATEST_DB_BACKUP=$(ls -t "$BACKUP_DIR"/database_*.sql 2>/dev/null | head -n 1)
    LATEST_CODE_BACKUP=$(ls -t "$BACKUP_DIR"/codebase_*.tar.gz 2>/dev/null | head -n 1)

    if [[ -n "$LATEST_CODE_BACKUP" ]]; then
        print_info "Restoring codebase from backup..."
        tar -xzf "$LATEST_CODE_BACKUP" -C "$APP_DIR"
        print_success "Codebase restored"
    fi

    if [[ -n "$LATEST_DB_BACKUP" ]]; then
        print_info "Restoring database from backup..."
        DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2)
        DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2)
        DB_PASS=$(grep DB_PASSWORD .env | cut -d '=' -f2)
        mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$LATEST_DB_BACKUP"
        print_success "Database restored"
    fi

    disable_maintenance_mode
    print_error "Rollback completed. Please investigate the deployment failure."
    exit 1
}

# Main deployment function
main() {
    print_info "========================================="
    print_info "Grocery Shop ERP - Production Deployment"
    print_info "Started at: $(date)"
    print_info "========================================="

    # Ensure we're in the correct directory
    cd "$APP_DIR"

    # Create logs directory if it doesn't exist
    mkdir -p "$(dirname "$LOG_FILE")"

    # Trap errors and rollback
    trap rollback_deployment ERR

    # Pre-deployment checks
    if [[ ! -f ".env" ]]; then
        print_error ".env file not found!"
        exit 1
    fi

    if [[ ! -d "vendor" ]] && [[ ! -f "composer.json" ]]; then
        print_error "Not a Laravel application directory!"
        exit 1
    fi

    # Run deployment steps
    create_pre_deployment_backup
    enable_maintenance_mode
    pull_latest_code
    install_composer_dependencies
    build_frontend_assets
    run_migrations
    optimize_application
    set_permissions
    restart_queue_workers
    restart_php_fpm
    verify_deployment
    disable_maintenance_mode
    cleanup_old_backups

    print_success "========================================="
    print_success "Deployment completed successfully!"
    print_success "Completed at: $(date)"
    print_success "========================================="

    # Remove error trap
    trap - ERR
}

# Run main function
main "$@"
