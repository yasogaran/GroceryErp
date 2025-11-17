# Rollback Guide
## Grocery Shop ERP - Emergency Deployment Rollback Procedures

**IMPORTANT**: Only use this guide if deployment has failed and rollback is necessary.

---

## When to Rollback

### Critical Issues Requiring Immediate Rollback
- Application completely inaccessible
- Database corruption detected
- Security vulnerability introduced
- Payment processing failure
- Data loss detected
- Multiple critical features broken

### Issues That May Not Require Rollback
- Minor UI bugs (can be hot-fixed)
- Single non-critical feature broken
- Performance degradation (< 50%)
- Non-critical error messages
- Minor CSS issues

---

## Rollback Decision Matrix

| Issue Severity | Affected Users | Business Impact | Action |
|----------------|----------------|-----------------|--------|
| Critical | All | High | Immediate Rollback |
| Critical | Some | High | Immediate Rollback |
| Major | All | Medium | Rollback (within 1 hour) |
| Major | Some | Medium | Hot-fix or Rollback |
| Minor | All | Low | Hot-fix |
| Minor | Some | Low | Schedule fix |

---

## Automatic Rollback (Using deploy.sh)

If deployment fails during execution, the `deploy.sh` script will automatically attempt rollback.

### What the Script Does
1. Restores database from pre-deployment backup
2. Restores codebase from backup
3. Disables maintenance mode
4. Logs rollback details

### Manual Trigger (if script fails)
If the automatic rollback fails, proceed to Manual Rollback section below.

---

## Manual Rollback Procedure

### Step 1: Enable Maintenance Mode

```bash
cd /var/www/groceryerp
php artisan down --retry=60
```

**Verification**: Visit https://yourdomain.com - should show maintenance page

---

### Step 2: Identify Backup Files

```bash
cd /var/www/groceryerp/storage/deployment-backups

# List available backups (sorted by date)
ls -lht

# Find the most recent pre-deployment backup
# Files are named:
# - database_YYYYMMDD_HHMMSS.sql
# - codebase_YYYYMMDD_HHMMSS.tar.gz
# - env_YYYYMMDD_HHMMSS.backup
```

**Example Output**:
```
database_20251117_140530.sql
codebase_20251117_140530.tar.gz
env_20251117_140530.backup
```

**Note the timestamp** - all files should have matching timestamps.

---

### Step 3: Stop Queue Workers

```bash
sudo supervisorctl stop groceryerp-worker:*
```

**Verification**:
```bash
sudo supervisorctl status
# Should show: groceryerp-worker:groceryerp-worker_00  STOPPED
```

---

### Step 4: Restore Database

```bash
# Set variables (replace with your actual values)
DB_NAME="groceryerp_production"
DB_USER="groceryerp_user"
DB_PASS="your_password"
BACKUP_FILE="database_20251117_140530.sql"  # Use your actual filename

# Create safety backup of current state (just in case)
mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > \
    /var/www/groceryerp/storage/deployment-backups/pre_rollback_$(date +%Y%m%d_%H%M%S).sql

# Drop and recreate database (cleanest approach)
mysql -u"$DB_USER" -p"$DB_PASS" -e "DROP DATABASE $DB_NAME; CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Restore from backup
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < \
    /var/www/groceryerp/storage/deployment-backups/$BACKUP_FILE
```

**Verification**:
```bash
# Check tables exist
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;"

# Check row counts
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT COUNT(*) FROM products;"
```

---

### Step 5: Restore Codebase

```bash
cd /var/www/groceryerp
BACKUP_FILE="codebase_20251117_140530.tar.gz"  # Use your actual filename

# Create safety backup of current state
tar -czf storage/deployment-backups/pre_rollback_codebase_$(date +%Y%m%d_%H%M%S).tar.gz \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/logs' \
    --exclude='storage/framework/cache' \
    .

# Extract backup (this will overwrite current files)
tar -xzf storage/deployment-backups/$BACKUP_FILE -C /var/www/groceryerp

# Restore .env file
ENV_BACKUP="env_20251117_140530.backup"  # Use your actual filename
cp storage/deployment-backups/$ENV_BACKUP .env
chmod 600 .env
```

**Verification**:
```bash
# Check Git commit
git log -1 --oneline

# Check .env file
cat .env | head -5
```

---

### Step 6: Restore Dependencies

```bash
# Install exact Composer dependencies from lock file
composer install --no-dev --optimize-autoloader

# Rebuild frontend assets (if needed)
npm ci --production
npm run build
```

**Verification**:
```bash
# Check vendor directory exists
ls -ld vendor/

# Check public assets
ls -l public/build/
```

---

### Step 7: Clear All Caches

```bash
# Clear application caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Warm up application cache
php artisan cache:warmup
```

**Verification**:
```bash
# Check cache files exist
ls -l bootstrap/cache/
```

---

### Step 8: Set Correct Permissions

```bash
# Reset ownership
sudo chown -R www-data:www-data /var/www/groceryerp

# Reset permissions
sudo chmod -R 755 /var/www/groceryerp
sudo chmod -R 775 /var/www/groceryerp/storage
sudo chmod -R 775 /var/www/groceryerp/bootstrap/cache
sudo chmod 600 /var/www/groceryerp/.env
```

**Verification**:
```bash
ls -ld storage/
ls -ld bootstrap/cache/
ls -l .env
```

---

### Step 9: Restart Services

```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Restart Nginx
sudo systemctl restart nginx

# Restart queue workers
sudo supervisorctl start groceryerp-worker:*

# Restart Redis (if needed)
sudo systemctl restart redis
```

**Verification**:
```bash
# Check all services are running
sudo systemctl status php8.2-fpm
sudo systemctl status nginx
sudo supervisorctl status
redis-cli ping  # Should return PONG
```

---

### Step 10: Verify Application

```bash
# Check application status
php artisan about

# Check database connection
php artisan db:show

# Test a simple artisan command
php artisan route:list | head -10
```

**Manual Tests**:
1. Open browser: https://yourdomain.com
2. Login with admin account
3. Test core POS functionality
4. Create a test sale
5. Generate a report
6. Verify data is correct

---

### Step 11: Disable Maintenance Mode

```bash
php artisan up
```

**Verification**: Visit https://yourdomain.com - should show normal application

---

### Step 12: Monitor Application

```bash
# Monitor error logs
tail -f storage/logs/laravel.log

# Monitor Nginx logs
sudo tail -f /var/log/nginx/error.log

# Monitor queue workers
tail -f storage/logs/worker.log

# Check server resources
htop
```

**Monitor for 30 minutes minimum** to ensure stability.

---

## Rollback Verification Checklist

After rollback, verify:

- [ ] Application accessible via browser
- [ ] Login working
- [ ] Database tables intact
- [ ] Data not corrupted
- [ ] Core features working (POS, reports, inventory)
- [ ] No errors in logs
- [ ] Queue workers processing
- [ ] Backups working
- [ ] Performance acceptable
- [ ] SSL certificate valid

---

## Partial Rollback Scenarios

### Rollback Database Only (Keep New Code)

```bash
cd /var/www/groceryerp
php artisan down

# Restore database
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < \
    storage/deployment-backups/database_YYYYMMDD_HHMMSS.sql

# Re-run migrations (if safe)
php artisan migrate

php artisan cache:clear
php artisan config:cache
php artisan up
```

### Rollback Code Only (Keep Database)

```bash
cd /var/www/groceryerp
php artisan down

# Restore codebase
tar -xzf storage/deployment-backups/codebase_YYYYMMDD_HHMMSS.tar.gz

# Reinstall dependencies
composer install --no-dev --optimize-autoloader

php artisan cache:clear
php artisan config:cache
php artisan up
```

---

## Common Rollback Issues & Solutions

### Issue 1: Database Restore Fails

**Symptoms**: MySQL import errors, table creation failures

**Solutions**:
```bash
# Check disk space
df -h

# Check MySQL error log
sudo tail -f /var/log/mysql/error.log

# Try manual table-by-table restore
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" --force < backup.sql

# Last resort: Restore from older backup
```

### Issue 2: Composer Dependencies Fail

**Symptoms**: `composer install` errors

**Solutions**:
```bash
# Clear Composer cache
composer clear-cache

# Update Composer
composer self-update

# Install with verbose output
composer install --no-dev --optimize-autoloader -vvv

# Check PHP version
php -v  # Should be 8.2+
```

### Issue 3: Permission Errors

**Symptoms**: 500 errors, "Permission denied" in logs

**Solutions**:
```bash
# Reset all permissions
sudo chown -R www-data:www-data /var/www/groceryerp
sudo chmod -R 755 /var/www/groceryerp
sudo chmod -R 775 storage bootstrap/cache
sudo chmod 600 .env

# Clear cache with sudo
sudo -u www-data php artisan cache:clear
```

### Issue 4: Queue Workers Won't Start

**Symptoms**: Supervisor errors, workers in FATAL state

**Solutions**:
```bash
# Check supervisor logs
sudo tail -f /var/log/supervisor/supervisord.log

# Reread configuration
sudo supervisorctl reread
sudo supervisorctl update

# Restart all processes
sudo supervisorctl restart all

# Check worker logs
tail -f storage/logs/worker.log
```

### Issue 5: Nginx Won't Start

**Symptoms**: "nginx: [emerg]" errors

**Solutions**:
```bash
# Test Nginx configuration
sudo nginx -t

# Check error log
sudo tail -f /var/log/nginx/error.log

# Common issues:
# - Port 80/443 already in use: sudo netstat -tulpn | grep :80
# - SSL certificate missing: Check paths in nginx config
# - Syntax error: Review recent changes to nginx config

# Restart Nginx
sudo systemctl restart nginx
```

---

## Post-Rollback Actions

### Immediate Actions (Next Hour)
1. [ ] Notify team of rollback completion
2. [ ] Document rollback reason and details
3. [ ] Monitor application closely
4. [ ] Review error logs
5. [ ] Verify backups are still working
6. [ ] Check all critical features

### Short-term Actions (Next 24 Hours)
1. [ ] Investigate root cause of deployment failure
2. [ ] Identify what went wrong
3. [ ] Plan fixes for identified issues
4. [ ] Test fixes in staging environment
5. [ ] Prepare for re-deployment
6. [ ] Communicate timeline to stakeholders

### Long-term Actions (Next Week)
1. [ ] Conduct post-mortem meeting
2. [ ] Update deployment procedures
3. [ ] Improve testing coverage
4. [ ] Enhance staging environment
5. [ ] Document lessons learned
6. [ ] Schedule next deployment attempt

---

## Rollback Communication Template

### Internal Communication (Team)

```
Subject: URGENT - Production Deployment Rollback Completed

Team,

The production deployment initiated at [TIME] has been rolled back to the previous stable version.

Rollback Completed: [TIME]
Reason: [Brief description]
Current Status: Application is stable and operational
Affected Features: [List any limitations]

Next Steps:
1. [Action items]
2. [Timeline for fix]
3. [Next deployment date]

The application is now functioning normally with the previous version.

Please report any issues immediately to [CONTACT].

Thank you,
[YOUR NAME]
```

### External Communication (Users/Stakeholders) - If Needed

```
Subject: System Update Status

Dear Valued Customer,

We recently performed a system update that required a rollback to ensure the best experience for our users.

The system is now fully operational and stable.

We apologize for any inconvenience during the brief maintenance period.

If you experience any issues, please contact our support team at [CONTACT].

Thank you for your patience and understanding.

Best regards,
[COMPANY NAME]
```

---

## Rollback Log Template

**Rollback Date**: _________________
**Rollback Start Time**: _________________
**Rollback End Time**: _________________
**Performed By**: _________________

**Reason for Rollback**:
```
_________________________________________________________________

_________________________________________________________________
```

**Backup Files Used**:
- Database: _________________
- Codebase: _________________
- Environment: _________________

**Steps Completed**:
- [ ] Maintenance mode enabled
- [ ] Queue workers stopped
- [ ] Database restored
- [ ] Codebase restored
- [ ] Dependencies reinstalled
- [ ] Caches cleared
- [ ] Permissions reset
- [ ] Services restarted
- [ ] Application verified
- [ ] Maintenance mode disabled

**Issues Encountered**:
```
_________________________________________________________________

_________________________________________________________________
```

**Current Status**: ☐ Successful  ☐ Partial  ☐ Failed

**Next Steps**:
```
_________________________________________________________________

_________________________________________________________________
```

---

## Emergency Contacts

**Technical Lead**: _________________ (Phone: _________________)
**System Administrator**: _________________ (Phone: _________________)
**Database Administrator**: _________________ (Phone: _________________)
**Project Manager**: _________________ (Phone: _________________)

---

**Document Version**: 1.0
**Last Updated**: November 2025
**Review Date**: Before each deployment
