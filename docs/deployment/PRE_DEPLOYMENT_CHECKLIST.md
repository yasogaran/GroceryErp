# Pre-Deployment Checklist
## Grocery Shop ERP - Production Readiness Assessment

**Date**: _________________
**Prepared By**: _________________
**Target Deployment Date**: _________________

---

## 1. Server Infrastructure

### Server Provisioning
- [ ] Server provisioned with minimum specifications (4GB RAM, 2 vCPUs, 50GB SSD)
- [ ] Server OS installed (Ubuntu 22.04 LTS recommended)
- [ ] Server hostname configured properly
- [ ] Timezone set to correct region (Asia/Colombo)
- [ ] Server accessible via SSH
- [ ] Root or sudo access verified
- [ ] Static IP address assigned (if applicable)

### Domain & DNS
- [ ] Domain name registered
- [ ] DNS A record points to server IP
- [ ] DNS propagation completed (verify with `dig yourdomain.com`)
- [ ] WWW subdomain configured (if needed)
- [ ] DNS TTL set appropriately (300-3600 seconds)

---

## 2. Required Software Installation

### Core Software
- [ ] PHP 8.2 or higher installed
- [ ] MySQL 8.0 or MariaDB 10.6+ installed
- [ ] Nginx or Apache web server installed
- [ ] Redis 6.0+ installed
- [ ] Composer 2.x installed
- [ ] Node.js 18.x+ and NPM installed
- [ ] Git installed
- [ ] Supervisor installed (for queue workers)

### PHP Extensions
- [ ] php8.2-fpm
- [ ] php8.2-cli
- [ ] php8.2-mysql
- [ ] php8.2-mbstring
- [ ] php8.2-xml
- [ ] php8.2-curl
- [ ] php8.2-zip
- [ ] php8.2-gd
- [ ] php8.2-intl
- [ ] php8.2-bcmath
- [ ] php8.2-redis

### Verification Commands
```bash
php -v                    # Should show 8.2+
composer --version        # Should show 2.x
node -v                   # Should show 18.x+
npm -v                    # Should be installed
mysql --version           # Should show 8.0+
redis-cli --version       # Should show 6.0+
supervisorctl version     # Should be installed
```

---

## 3. Security Configuration

### Firewall Setup
- [ ] UFW or iptables configured
- [ ] Port 22 (SSH) open
- [ ] Port 80 (HTTP) open
- [ ] Port 443 (HTTPS) open
- [ ] Port 3306 (MySQL) restricted to localhost only
- [ ] Port 6379 (Redis) restricted to localhost only
- [ ] Unnecessary ports closed
- [ ] Firewall enabled and active

### SSH Security
- [ ] SSH key-based authentication configured
- [ ] Password authentication disabled (if using keys)
- [ ] Root login disabled
- [ ] SSH port changed (optional but recommended)
- [ ] Fail2ban installed and configured
- [ ] SSH connection tested from multiple locations

### MySQL Security
- [ ] `mysql_secure_installation` run
- [ ] Root password set
- [ ] Anonymous users removed
- [ ] Remote root login disabled
- [ ] Test database removed
- [ ] Production database created
- [ ] Production database user created with limited privileges

---

## 4. Application Preparation

### Code Repository
- [ ] Application code in Git repository
- [ ] Repository accessible from server
- [ ] Deploy key or credentials configured (if private repo)
- [ ] Production branch created (e.g., `main` or `production`)
- [ ] All code merged to production branch
- [ ] Latest code tested in staging environment
- [ ] No debug code or test data in production branch

### Environment Configuration
- [ ] `.env.example` file updated with all required variables
- [ ] Production `.env` file prepared (DO NOT commit to Git)
- [ ] `APP_ENV` set to `production`
- [ ] `APP_DEBUG` set to `false`
- [ ] `APP_KEY` will be generated during deployment
- [ ] `APP_URL` set to production domain
- [ ] Database credentials prepared
- [ ] Redis configuration prepared
- [ ] Mail server credentials prepared (if using email)

### Dependencies
- [ ] All Composer dependencies listed in `composer.json`
- [ ] All NPM dependencies listed in `package.json`
- [ ] Package versions locked in `composer.lock` and `package-lock.json`
- [ ] No dev-only dependencies in production
- [ ] Third-party packages verified for compatibility

---

## 5. Database Preparation

### Database Setup
- [ ] Production database created
- [ ] Database user created with appropriate privileges
- [ ] Database user password is strong (min 16 characters)
- [ ] Database character set: `utf8mb4`
- [ ] Database collation: `utf8mb4_unicode_ci`
- [ ] Database connection tested from application server
- [ ] Database backup strategy planned

### Migration Strategy
- [ ] All migration files reviewed
- [ ] Migration order verified
- [ ] Seeder files prepared (if needed)
- [ ] Initial admin user creation planned
- [ ] Rollback procedures documented
- [ ] Test migrations run in staging environment

---

## 6. Third-Party Services

### Email Service (Optional)
- [ ] SMTP server configured (or using service like SendGrid, Mailgun)
- [ ] SMTP credentials obtained
- [ ] Test email sent successfully
- [ ] `MAIL_*` environment variables configured
- [ ] From address and name configured

### Backup Storage (Optional)
- [ ] Cloud storage configured (S3, Spaces, etc.)
- [ ] Backup credentials obtained
- [ ] Backup retention policy defined (30 days recommended)
- [ ] Backup schedule planned (daily at 2 AM)
- [ ] Backup restore procedure tested

### Monitoring Services (Optional)
- [ ] Uptime monitoring configured (UptimeRobot, Pingdom)
- [ ] Error tracking configured (Sentry, Bugsnag)
- [ ] Server monitoring configured (New Relic, DataDog)
- [ ] Alert email addresses configured
- [ ] Alert thresholds configured

---

## 7. SSL/TLS Certificate

### Certificate Setup
- [ ] Let's Encrypt Certbot installed
- [ ] Domain ownership verified
- [ ] SSL certificate obtained
- [ ] Certificate installed in Nginx/Apache
- [ ] HTTPS enabled and working
- [ ] HTTP to HTTPS redirect configured
- [ ] SSL certificate auto-renewal configured
- [ ] SSL test passed (https://www.ssllabs.com/ssltest/)

---

## 8. Performance Optimization

### PHP Configuration
- [ ] `memory_limit = 256M`
- [ ] `upload_max_filesize = 20M`
- [ ] `post_max_size = 20M`
- [ ] `max_execution_time = 300`
- [ ] OPcache enabled and configured
- [ ] PHP-FPM pool configured for production

### Database Optimization
- [ ] InnoDB buffer pool size configured (50-70% of RAM)
- [ ] Query cache disabled (MySQL 8.0+)
- [ ] Slow query log enabled
- [ ] Database indexes verified (from Phase 6 migration)

### Caching Strategy
- [ ] Redis configured for sessions
- [ ] Redis configured for cache
- [ ] Redis configured for queue
- [ ] Cache warming strategy implemented

---

## 9. Documentation Review

### Deployment Documentation
- [ ] SERVER_REQUIREMENTS.md reviewed
- [ ] DEPLOYMENT_GUIDE.md reviewed
- [ ] Configuration files reviewed (Nginx, Supervisor)
- [ ] Deployment script (`deploy.sh`) reviewed and tested
- [ ] Rollback procedure documented
- [ ] Emergency contact list prepared

### Application Documentation
- [ ] User manual prepared (if needed)
- [ ] Admin documentation prepared
- [ ] API documentation prepared (if applicable)
- [ ] Troubleshooting guide reviewed
- [ ] FAQ document prepared (if needed)

---

## 10. Testing & Validation

### Pre-Deployment Testing
- [ ] All features tested in staging environment
- [ ] User acceptance testing (UAT) completed
- [ ] Performance testing completed
- [ ] Security testing completed
- [ ] Browser compatibility tested (Chrome, Firefox, Safari, Edge)
- [ ] Mobile responsiveness tested
- [ ] Print functionality tested (receipts, reports, labels)

### Data Migration (if applicable)
- [ ] Data export from old system completed
- [ ] Data transformation scripts prepared
- [ ] Data import tested in staging
- [ ] Data validation completed
- [ ] Old system backup created
- [ ] Data migration rollback plan prepared

---

## 11. Team Preparation

### Access & Credentials
- [ ] Server SSH access for team members
- [ ] Database access credentials shared securely
- [ ] Application admin accounts created
- [ ] Email accounts configured
- [ ] Password manager configured (LastPass, 1Password, etc.)
- [ ] Emergency access procedures documented

### Training
- [ ] Admin users trained on system
- [ ] Staff trained on POS functionality
- [ ] Inventory managers trained
- [ ] Report users trained
- [ ] Training documentation provided
- [ ] Video tutorials created (optional)

### Communication Plan
- [ ] Deployment announcement prepared
- [ ] Maintenance window communicated
- [ ] Support channels established (email, phone, chat)
- [ ] Escalation procedures defined
- [ ] Go-live communication sent to stakeholders

---

## 12. Legal & Compliance

### Compliance Requirements
- [ ] Data privacy policy prepared
- [ ] Terms of service prepared
- [ ] GDPR compliance verified (if applicable)
- [ ] Tax calculation verified for region
- [ ] Receipt format compliant with local regulations
- [ ] Data retention policy defined
- [ ] User data handling procedures documented

### Licensing
- [ ] Software licenses verified
- [ ] Third-party library licenses reviewed
- [ ] Font licenses verified (if using commercial fonts)
- [ ] Image licenses verified

---

## 13. Backup & Disaster Recovery

### Backup Procedures
- [ ] Automated daily backup configured (2 AM)
- [ ] Backup storage location verified
- [ ] Backup retention policy configured (30 days)
- [ ] Backup restoration tested successfully
- [ ] Off-site backup configured (recommended)
- [ ] Database backup script tested
- [ ] File backup script tested

### Disaster Recovery Plan
- [ ] Recovery Time Objective (RTO) defined
- [ ] Recovery Point Objective (RPO) defined
- [ ] Disaster recovery procedures documented
- [ ] Emergency contact list prepared
- [ ] Alternate server prepared (optional)
- [ ] Failover procedures documented

---

## 14. Post-Deployment Support

### Monitoring Setup
- [ ] Log monitoring configured
- [ ] Error tracking active
- [ ] Performance monitoring active
- [ ] Uptime monitoring active
- [ ] Disk space monitoring configured
- [ ] Alert notifications configured

### Support Plan
- [ ] Support hours defined
- [ ] Support team assigned
- [ ] Ticketing system configured (if needed)
- [ ] Bug reporting process defined
- [ ] Feature request process defined
- [ ] SLA defined (if applicable)

---

## 15. Final Verification

### Pre-Launch Checks
- [ ] All items in this checklist completed
- [ ] Staging environment matches production configuration
- [ ] Deployment script tested in staging
- [ ] Rollback procedure tested
- [ ] Emergency procedures reviewed
- [ ] All team members briefed
- [ ] Deployment scheduled in low-traffic window

### Go/No-Go Decision
- [ ] Technical lead approval: _________________ (Signature & Date)
- [ ] Project manager approval: _________________ (Signature & Date)
- [ ] Business owner approval: _________________ (Signature & Date)

---

## Notes & Comments

_Use this space to document any issues, concerns, or special instructions:_

```
________________________________________________________________________________

________________________________________________________________________________

________________________________________________________________________________

________________________________________________________________________________

________________________________________________________________________________
```

---

## Deployment Schedule

**Planned Deployment Date**: _________________
**Planned Deployment Time**: _________________
**Expected Downtime**: _________________
**Rollback Deadline**: _________________

---

**Document Version**: 1.0
**Last Updated**: November 2025
**Next Review**: Before each deployment
