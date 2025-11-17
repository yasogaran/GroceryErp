# Grocery Shop ERP - Production Deployment Guide

## Table of Contents
1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Server Setup](#server-setup)
3. [Application Installation](#application-installation)
4. [Environment Configuration](#environment-configuration)
5. [Database Setup](#database-setup)
6. [Web Server Configuration](#web-server-configuration)
7. [SSL Certificate Setup](#ssl-certificate-setup)
8. [Queue Workers Setup](#queue-workers-setup)
9. [Cron Jobs Setup](#cron-jobs-setup)
10. [Final Testing](#final-testing)
11. [Post-Deployment Tasks](#post-deployment-tasks)
12. [Troubleshooting](#troubleshooting)

---

## Pre-Deployment Checklist

Before starting deployment, ensure you have:

- [ ] Server provisioned with required specifications (see SERVER_REQUIREMENTS.md)
- [ ] Domain name registered and DNS configured
- [ ] SSH access to the server
- [ ] Root or sudo privileges
- [ ] Database credentials ready
- [ ] SMTP credentials for email (optional)
- [ ] Backup strategy planned

---

## Server Setup

### Step 1: Initial Server Configuration

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Set timezone to Asia/Colombo (or your timezone)
sudo timedatectl set-timezone Asia/Colombo

# Create deployment user (if not using root)
sudo adduser groceryerp
sudo usermod -aG sudo groceryerp
```

### Step 2: Install Required Software

```bash
# Install PHP 8.2 and extensions
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-intl \
    php8.2-bcmath php8.2-redis

# Install MySQL 8.0
sudo apt install -y mysql-server

# Secure MySQL installation
sudo mysql_secure_installation

# Install Nginx
sudo apt install -y nginx

# Install Redis
sudo apt install -y redis-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Node.js 18.x and NPM
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install Supervisor (for queue workers)
sudo apt install -y supervisor

# Install Git
sudo apt install -y git

# Install UFW firewall
sudo apt install -y ufw

# Install Fail2ban
sudo apt install -y fail2ban
```

### Step 3: Configure Firewall

```bash
# Enable firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status
```

---

## Application Installation

### Step 1: Clone Repository

```bash
# Navigate to web root
cd /var/www

# Clone repository (replace with your repository URL)
sudo git clone https://github.com/yourusername/GroceryErp.git groceryerp

# Set ownership
sudo chown -R www-data:www-data /var/www/groceryerp

# Give current user access
sudo usermod -aG www-data $USER

# Navigate to application directory
cd /var/www/groceryerp
```

### Step 2: Install Dependencies

```bash
# Install Composer dependencies (production mode)
composer install --no-dev --optimize-autoloader

# Install NPM dependencies and build assets
npm ci --production
npm run build
```

### Step 3: Set Permissions

```bash
# Set correct permissions
sudo find /var/www/groceryerp -type f -exec chmod 644 {} \;
sudo find /var/www/groceryerp -type d -exec chmod 755 {} \;

# Storage and cache directories need write permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

## Environment Configuration

### Step 1: Create Environment File

```bash
# Copy example environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Secure .env file
chmod 600 .env
```

### Step 2: Configure Environment Variables

Edit `.env` file:

```ini
APP_NAME="Grocery Shop ERP"
APP_ENV=production
APP_KEY=base64:GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=groceryerp_production
DB_USERNAME=groceryerp_user
DB_PASSWORD=SECURE_PASSWORD_HERE

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Backup settings
BACKUP_RETENTION_DAYS=30
BACKUP_NOTIFICATION_EMAIL=admin@yourdomain.com

# Low Stock Alert Settings
LOW_STOCK_NOTIFICATION_EMAIL=inventory@yourdomain.com
LOW_STOCK_CHECK_ENABLED=true
```

---

## Database Setup

### Step 1: Create Database and User

```bash
# Login to MySQL
sudo mysql

# Create database
CREATE DATABASE groceryerp_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user
CREATE USER 'groceryerp_user'@'localhost' IDENTIFIED BY 'SECURE_PASSWORD_HERE';

# Grant privileges
GRANT ALL PRIVILEGES ON groceryerp_production.* TO 'groceryerp_user'@'localhost';

# Flush privileges
FLUSH PRIVILEGES;

# Exit MySQL
EXIT;
```

### Step 2: Run Migrations and Seeders

```bash
# Run database migrations
php artisan migrate --force

# Seed initial data (if needed)
php artisan db:seed --force

# Warm up cache
php artisan cache:warmup
```

---

## Web Server Configuration

### Nginx Configuration

Create Nginx configuration file:

```bash
sudo nano /etc/nginx/sites-available/groceryerp
```

Add the following configuration:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;

    # Redirect to HTTPS (will be enabled after SSL setup)
    # return 301 https://$server_name$request_uri;

    root /var/www/groceryerp/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Disable access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Disable access to sensitive directories
    location ~* (^|/)\.git {
        deny all;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~* \.(jpg|jpeg|gif|png|css|js|ico|xml|webp|svg|woff|woff2|ttf)$ {
        expires 365d;
        add_header Cache-Control "public, immutable";
    }

    # Increase upload size limits
    client_max_body_size 20M;
}
```

Enable the site and test configuration:

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/groceryerp /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx

# Enable Nginx to start on boot
sudo systemctl enable nginx
```

### PHP-FPM Configuration

Edit PHP-FPM pool configuration:

```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Important settings to verify/modify:

```ini
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

Edit PHP configuration:

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Key settings:

```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
max_input_time = 300
date.timezone = Asia/Colombo

; OPcache settings
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 2
opcache.validate_timestamps = 1
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.2-fpm
sudo systemctl enable php8.2-fpm
```

---

## SSL Certificate Setup

### Using Let's Encrypt (Free SSL)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test automatic renewal
sudo certbot renew --dry-run
```

After SSL installation, update Nginx configuration to redirect HTTP to HTTPS:

```bash
sudo nano /etc/nginx/sites-available/groceryerp
```

Uncomment the redirect line:

```nginx
return 301 https://$server_name$request_uri;
```

Reload Nginx:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## Queue Workers Setup

### Supervisor Configuration

Create supervisor configuration:

```bash
sudo nano /etc/supervisor/conf.d/groceryerp-worker.conf
```

Add the following configuration:

```ini
[program:groceryerp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/groceryerp/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/groceryerp/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:

```bash
# Reload supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start workers
sudo supervisorctl start groceryerp-worker:*

# Check status
sudo supervisorctl status
```

---

## Cron Jobs Setup

Add Laravel scheduler to crontab:

```bash
# Edit crontab for www-data user
sudo crontab -u www-data -e
```

Add the following line:

```cron
* * * * * cd /var/www/groceryerp && php artisan schedule:run >> /dev/null 2>&1
```

Verify scheduled tasks:

```bash
# List scheduled tasks
php artisan schedule:list
```

Expected scheduled tasks:
- Daily backup at 2:00 AM
- Weekly backup cleanup (Sunday 3:00 AM)
- Daily low stock check at 8:00 AM

---

## Final Testing

### Step 1: Application Testing

```bash
# Check application status
php artisan about

# Verify database connection
php artisan db:show

# Test cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Warm up cache
php artisan cache:warmup
```

### Step 2: Web Access Testing

1. **Open browser** and navigate to `https://yourdomain.com`
2. **Verify SSL** certificate is valid (green padlock)
3. **Test login** functionality
4. **Create test sale** to verify core functionality
5. **Check notifications** bell in header
6. **Test barcode** label generation
7. **Verify reports** are loading correctly

### Step 3: Performance Testing

```bash
# Monitor server resources
htop

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check Nginx status
sudo systemctl status nginx

# Check queue workers
sudo supervisorctl status

# Monitor logs
tail -f storage/logs/laravel.log
```

---

## Post-Deployment Tasks

### 1. Create Admin User

```bash
# If using seeders with default admin
# Login with: admin@groceryerp.com / password

# Or create manually in database/via tinker
php artisan tinker

>>> $user = new App\Models\User();
>>> $user->name = 'Administrator';
>>> $user->email = 'admin@yourdomain.com';
>>> $user->password = Hash::make('SecurePassword123!');
>>> $user->role = 'admin';
>>> $user->is_active = true;
>>> $user->save();
>>> exit
```

### 2. Configure Backups

```bash
# Test manual backup
php artisan backup:create --type=manual

# Verify backup was created
ls -lah storage/app/backups/

# Check backup status
php artisan backup:list
```

### 3. Monitor Application

- Set up uptime monitoring (UptimeRobot, Pingdom)
- Configure error tracking (Sentry, Bugsnag)
- Set up server monitoring (New Relic, DataDog)
- Enable email alerts for critical errors

### 4. Security Hardening

```bash
# Disable directory listing
sudo nano /etc/nginx/nginx.conf
# Add: autoindex off;

# Configure Fail2ban for Nginx
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo nano /etc/fail2ban/jail.local

# Add Nginx jail
[nginx-http-auth]
enabled = true
filter = nginx-http-auth
port = http,https
logpath = /var/log/nginx/error.log

# Restart Fail2ban
sudo systemctl restart fail2ban
```

### 5. Database Optimization

```bash
# Optimize tables
php artisan db:optimize

# Analyze slow queries
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Add under [mysqld]
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2

# Restart MySQL
sudo systemctl restart mysql
```

---

## Troubleshooting

### Common Issues

#### 1. 500 Internal Server Error

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check Nginx error logs
sudo tail -f /var/log/nginx/error.log

# Check PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### 2. Permission Issues

```bash
# Reset permissions
sudo chown -R www-data:www-data /var/www/groceryerp
sudo chmod -R 755 /var/www/groceryerp
sudo chmod -R 775 /var/www/groceryerp/storage
sudo chmod -R 775 /var/www/groceryerp/bootstrap/cache
```

#### 3. Queue Workers Not Processing

```bash
# Check supervisor status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart groceryerp-worker:*

# Check worker logs
tail -f storage/logs/worker.log

# Manually process queue
php artisan queue:work redis --once
```

#### 4. Database Connection Issues

```bash
# Verify MySQL is running
sudo systemctl status mysql

# Test connection
mysql -u groceryerp_user -p groceryerp_production

# Check .env database credentials
cat .env | grep DB_
```

#### 5. SSL Certificate Issues

```bash
# Renew certificate
sudo certbot renew

# Check certificate status
sudo certbot certificates

# Test SSL configuration
openssl s_client -connect yourdomain.com:443
```

### Performance Issues

```bash
# Enable query logging temporarily
# In config/database.php, set 'logging' => true

# Monitor database performance
sudo mysqladmin -u root -p processlist

# Check Redis
redis-cli ping
redis-cli info stats

# Monitor PHP-FPM
sudo systemctl status php8.2-fpm
```

---

## Rollback Procedure

If deployment fails, use the deployment script's automatic rollback or manual rollback:

```bash
# Manual rollback steps:

# 1. Restore database from backup
cd /var/www/groceryerp/storage/deployment-backups
mysql -u groceryerp_user -p groceryerp_production < database_YYYYMMDD_HHMMSS.sql

# 2. Restore codebase
tar -xzf codebase_YYYYMMDD_HHMMSS.tar.gz -C /var/www/groceryerp

# 3. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 4. Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
sudo supervisorctl restart groceryerp-worker:*

# 5. Disable maintenance mode
php artisan up
```

---

## Automated Deployment

Once initial deployment is complete, use the automated deployment script:

```bash
# Make sure script is executable
chmod +x /var/www/groceryerp/deploy.sh

# Run deployment
cd /var/www/groceryerp
./deploy.sh
```

The script will:
1. Create pre-deployment backup
2. Enable maintenance mode
3. Pull latest code
4. Install dependencies
5. Run migrations
6. Optimize application
7. Restart services
8. Verify deployment
9. Disable maintenance mode

---

## Maintenance

### Daily Tasks
- Monitor error logs
- Check disk space
- Verify backups are running

### Weekly Tasks
- Review performance metrics
- Update security patches
- Check slow query logs

### Monthly Tasks
- Full system backup
- Security audit
- Performance optimization review
- Update dependencies (if needed)

---

## Additional Resources

- Laravel Documentation: https://laravel.com/docs
- Nginx Documentation: https://nginx.org/en/docs/
- Let's Encrypt: https://letsencrypt.org/
- Supervisor Documentation: http://supervisord.org/

---

## Support

For issues or questions:
- Check application logs: `/var/www/groceryerp/storage/logs/`
- Check deployment logs: `/var/www/groceryerp/storage/logs/deployment.log`
- Review this guide and SERVER_REQUIREMENTS.md

---

**Document Version**: 1.0
**Last Updated**: November 2025
**Author**: System Administrator
