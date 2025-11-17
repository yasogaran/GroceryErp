# Server Requirements for Grocery Shop ERP

## Production Server Specifications

### Minimum Requirements
- **CPU**: 2 vCPUs (2.4 GHz or higher)
- **RAM**: 4 GB
- **Storage**: 50 GB SSD
- **Bandwidth**: 100 Mbps unmetered
- **Concurrent Users**: Up to 10 users

### Recommended Requirements
- **CPU**: 4 vCPUs (2.8 GHz or higher)
- **RAM**: 8 GB
- **Storage**: 100 GB SSD
- **Bandwidth**: 1 Gbps unmetered
- **Concurrent Users**: 20+ users

---

## Software Requirements

### Operating System
- **Ubuntu 22.04 LTS** (Recommended)
- **Ubuntu 20.04 LTS** (Supported)
- **Debian 11/12** (Supported)
- **CentOS Stream 9** (Supported)

### Web Server
- **Nginx 1.18+** (Recommended)
- **Apache 2.4+** (Supported)

### PHP
- **PHP 8.2** or higher
- Required PHP Extensions:
  - `php8.2-fpm`
  - `php8.2-cli`
  - `php8.2-mysql`
  - `php8.2-mbstring`
  - `php8.2-xml`
  - `php8.2-curl`
  - `php8.2-zip`
  - `php8.2-gd`
  - `php8.2-intl`
  - `php8.2-bcmath`
  - `php8.2-redis` (optional but recommended)

### Database
- **MySQL 8.0+** (Recommended)
- **MariaDB 10.6+** (Supported)

### Cache & Queue
- **Redis 6.0+** (Recommended for cache and sessions)
- **Supervisor** (For queue workers)

### SSL Certificate
- **Let's Encrypt** (Free SSL certificate)
- Or any valid SSL certificate provider

### Additional Tools
- **Composer 2.x** (Dependency manager)
- **Node.js 18.x+** and **NPM** (For asset compilation)
- **Git** (For deployment)
- **Cron** (For scheduled tasks)

---

## Network Requirements

### Firewall Rules
Open the following ports:
- **Port 80** (HTTP) - Required for Let's Encrypt validation
- **Port 443** (HTTPS) - For secure application access
- **Port 22** (SSH) - For server management
- **Port 3306** (MySQL) - Only allow from localhost/internal network

### Domain Requirements
- A registered domain name (e.g., groceryerp.example.com)
- DNS properly configured pointing to server IP
- Subdomain support (optional)

---

## Security Requirements

### Server Security
- SSH key-based authentication (disable password auth)
- Firewall enabled (UFW or iptables)
- Fail2ban installed and configured
- Regular security updates enabled
- Root login disabled

### Application Security
- SSL/TLS certificate installed
- Secure file permissions (755 for directories, 644 for files)
- `.env` file protected (600 permissions)
- `storage` and `bootstrap/cache` writable by web server
- All sensitive data encrypted

---

## Performance Optimization

### PHP Configuration
Recommended `php.ini` settings:
```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
max_input_time = 300
date.timezone = Asia/Colombo
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 2
```

### MySQL Configuration
Recommended `my.cnf` settings:
```ini
[mysqld]
max_connections = 200
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
query_cache_size = 0
query_cache_type = 0
```

### Redis Configuration
```ini
maxmemory 512mb
maxmemory-policy allkeys-lru
```

---

## Backup Requirements

### Daily Backups
- Database backup (automated via cron)
- File backup (uploaded images, receipts)
- Backup retention: 30 days
- Backup storage: Off-site or cloud storage

### Backup Storage
- Minimum 100 GB for backup storage
- Daily incremental backups
- Weekly full backups
- Monthly archive backups

---

## Monitoring Requirements

### Application Monitoring
- Error tracking (Sentry, Bugsnag, or similar)
- Performance monitoring (New Relic, DataDog, or similar)
- Uptime monitoring (UptimeRobot, Pingdom)

### Server Monitoring
- CPU and RAM usage
- Disk space monitoring
- MySQL slow query log
- Nginx/Apache error logs
- PHP-FPM logs

---

## Estimated Costs

### Cloud Server (Monthly)
- **DigitalOcean**: $24-48/month (4GB-8GB Droplet)
- **AWS EC2**: $30-60/month (t3.medium or t3.large)
- **Linode**: $24-48/month (4GB-8GB plan)
- **Vultr**: $24-48/month (4GB-8GB plan)

### Domain & SSL
- Domain: $10-15/year
- SSL Certificate: FREE (Let's Encrypt)

### Optional Services
- Backup Storage (S3, Spaces): $5-10/month
- Monitoring (Sentry, New Relic): $0-29/month
- Email Service (SendGrid, Mailgun): $0-10/month

**Total Estimated Cost**: $30-75/month depending on configuration

---

## Next Steps
1. Provision server with required specifications
2. Install required software (see INSTALLATION_GUIDE.md)
3. Configure security settings
4. Deploy application (see DEPLOYMENT_GUIDE.md)
5. Set up monitoring and backups
6. Perform final testing

---

**Document Version**: 1.0
**Last Updated**: November 2025
**Contact**: System Administrator
