# Campus Event Hub - Admin Management Guide

## Overview

This guide covers administrative tasks for managing the Campus Event Hub system.

## Database Management

### Backup Database
```bash
# Full backup
mysqldump -u root -p campus_event_hub > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup with compression
mysqldump -u root -p campus_event_hub | gzip > backup_$(date +%Y%m%d).sql.gz
```

### Restore Database
```bash
# From uncompressed backup
mysql -u root -p campus_event_hub < backup_20260513.sql

# From compressed backup
gunzip < backup_20260513.sql.gz | mysql -u root -p campus_event_hub
```

### Database Maintenance
```sql
-- Check database size
SELECT 
    table_name,
    ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb
FROM information_schema.tables
WHERE table_schema = 'campus_event_hub'
ORDER BY size_mb DESC;

-- Optimize tables
OPTIMIZE TABLE users, events, rsvps, reminders, notifications;

-- Check table integrity
CHECK TABLE users, events, rsvps;

-- Repair if needed
REPAIR TABLE users;
```

## User Management

### View All Users
```sql
SELECT id, student_id, name, email, department, created_at FROM users;
```

### Reset User Password
```sql
-- First, generate a temporary password hash
-- Use PHP: echo password_hash('temppassword123', PASSWORD_BCRYPT);

UPDATE users SET password = '$2y$10$...' WHERE id = 1;
```

### Disable User Account
```sql
-- Option 1: Add status column (requires migration)
ALTER TABLE users ADD COLUMN status ENUM('active', 'disabled') DEFAULT 'active';
UPDATE users SET status = 'disabled' WHERE id = 1;

-- Option 2: Delete user (use with caution)
-- This will cascade delete RSVPs and reminders
DELETE FROM users WHERE id = 1;
```

### View User Activity
```sql
-- User's RSVPs
SELECT u.name, e.title, r.status, r.rsvp_date 
FROM rsvps r
JOIN users u ON r.user_id = u.id
JOIN events e ON r.event_id = e.id
ORDER BY r.rsvp_date DESC;

-- User's reminders
SELECT u.name, e.title, r.reminder_type, r.reminder_time, r.is_sent
FROM reminders r
JOIN users u ON r.user_id = u.id
JOIN events e ON r.event_id = e.id
WHERE u.id = 1;
```

## Event Management

### Create Event
```php
<?php
require_once 'classes/Event.php';

$event = new Event();
$event_id = $event->createEvent([
    'title' => 'Event Title',
    'description' => 'Event description',
    'category_id' => 1,
    'location' => 'Main Hall',
    'event_date' => '2026-06-01 10:00:00',
    'end_date' => '2026-06-01 12:00:00',
    'capacity' => 100,
    'organizer_id' => 1
]);
?>
```

### Update Event
```sql
UPDATE events SET 
    title = 'New Title',
    description = 'New description',
    status = 'ongoing'
WHERE id = 1;
```

### Cancel Event
```sql
UPDATE events SET status = 'cancelled' WHERE id = 1;

-- Notify attendees (add logic in PHP)
SELECT u.email, u.name FROM rsvps r
JOIN users u ON r.user_id = u.id
WHERE r.event_id = 1 AND r.status = 'confirmed';
```

### View Event Statistics
```sql
-- Event attendance
SELECT e.title, e.capacity,
    COUNT(CASE WHEN r.status = 'confirmed' THEN 1 END) as confirmed,
    COUNT(CASE WHEN r.status = 'interested' THEN 1 END) as interested,
    COUNT(CASE WHEN r.status = 'declined' THEN 1 END) as declined
FROM events e
LEFT JOIN rsvps r ON e.id = r.event_id
GROUP BY e.id;

-- Upcoming events
SELECT title, event_date, location, capacity,
    (SELECT COUNT(*) FROM rsvps WHERE event_id = events.id AND status = 'confirmed') as attendees
FROM events
WHERE event_date > NOW()
ORDER BY event_date;
```

## Reminder Management

### Check Pending Reminders
```sql
-- Reminders not yet sent
SELECT r.*, u.email, u.name, e.title, e.event_date
FROM reminders r
JOIN users u ON r.user_id = u.id
JOIN events e ON r.event_id = e.id
WHERE r.is_sent = FALSE
AND DATE_ADD(e.event_date, INTERVAL -r.reminder_time r.reminder_unit) <= NOW()
ORDER BY e.event_date;
```

### Manual Reminder Test
```bash
# Run reminder script manually
php cron/send_reminders.php

# Check output
php cron/send_reminders.php 2>&1 | tee reminder_test.log
```

### Delete Old Reminders
```sql
-- Delete reminders for past events
DELETE FROM reminders 
WHERE event_id IN (
    SELECT id FROM events WHERE event_date < DATE_SUB(NOW(), INTERVAL 30 DAY)
);
```

## System Monitoring

### Check Error Logs
```bash
# Apache error log
tail -f /var/log/apache2/error.log

# PHP error log
tail -f /var/log/php-fpm.log

# System log
tail -f /var/log/syslog
```

### Monitor Database
```sql
-- Current connections
SHOW PROCESSLIST;

-- Kill slow query
KILL 1234;

-- Check slow log
SHOW VARIABLES LIKE 'slow_query_log%';
```

### Disk Space Monitoring
```bash
# Check disk usage
df -h

# Check directory size
du -sh campus-event-hub/

# Find large files
find campus-event-hub -type f -size +10M
```

## Cron Job Management

### Set Up Reminders Cron
```bash
# Edit crontab
crontab -e

# Add job (runs every hour)
0 * * * * php /var/www/html/campus-event-hub/cron/send_reminders.php >> /var/log/ceh-reminders.log 2>&1

# Add job (runs every 30 minutes)
*/30 * * * * php /var/www/html/campus-event-hub/cron/send_reminders.php

# Add daily database backup
0 2 * * * mysqldump -u root -p'password' campus_event_hub > /backups/ceh_$(date +\%Y\%m\%d).sql
```

### View Current Cron Jobs
```bash
# List current jobs
crontab -l

# Edit root crontab
sudo crontab -e

# Check cron logs
grep CRON /var/log/syslog
```

## Email Configuration

### Configure PHP Mail
Update `cron/send_reminders.php`:
```php
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: noreply@campuseventhub.edu\r\n";

mail($to, $subject, $message, $headers);
```

### Configure SMTP
Use PHPMailer or Swift Mailer for SMTP:
```php
$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'app-password';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
$mail->setFrom('noreply@campuseventhub.edu');
$mail->addAddress($recipient);
```

### Test Email Configuration
```bash
# Send test email
echo "Test message" | mail -s "Test Subject" admin@campus.edu

# Check mail logs
tail -f /var/log/mail.log
```

## Performance Tuning

### Database Optimization
```sql
-- Analyze table statistics
ANALYZE TABLE users, events, rsvps;

-- Rebuild indexes
REPAIR TABLE users;
OPTIMIZE TABLE users;

-- Check query performance
EXPLAIN SELECT * FROM events WHERE event_date > NOW() LIMIT 10;
```

### MySQL Configuration
Edit `/etc/mysql/my.cnf`:
```ini
[mysqld]
max_connections = 100
wait_timeout = 600
max_allowed_packet = 16M
innodb_buffer_pool_size = 1G
```

### PHP Optimization
Edit `/etc/php/7.4/fpm/php.ini`:
```ini
max_execution_time = 30
memory_limit = 128M
upload_max_filesize = 20M
post_max_size = 20M
```

## Security Maintenance

### Change Database Password
```bash
# As MySQL user
mysql -u root -p
ALTER USER 'root'@'localhost' IDENTIFIED BY 'new_password';
FLUSH PRIVILEGES;
EXIT;

# Update config/database.php with new password
```

### Check File Permissions
```bash
# Correct permissions
chmod 644 public/css/* public/js/* public/*.php
chmod 755 public/ api/ classes/ config/ database/ cron/
chmod 600 config/database.php
```

### Rotate Logs
```bash
# Set up logrotate
sudo vim /etc/logrotate.d/campus-event-hub

# Add:
/var/www/html/campus-event-hub/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
}
```

## Troubleshooting

### High CPU Usage
```bash
# Check running processes
top

# Find bottleneck
ps aux | grep php
ps aux | grep mysql

# Check database slow log
SHOW VARIABLES LIKE 'slow_query_log';
```

### Database Connection Issues
```sql
-- Check max connections
SHOW VARIABLES LIKE 'max_connections';

-- Check current connections
SHOW PROCESSLIST;

-- Kill idle connections
SELECT ID FROM INFORMATION_SCHEMA.PROCESSLIST WHERE TIME > 600;
```

### Low Disk Space
```bash
# Check disk usage
du -sh /var/www/html/campus-event-hub/*

# Find large files
find /var/www/html/campus-event-hub -type f -size +100M

# Clean old logs
rm /var/log/php-fpm.log.*
```

## Regular Maintenance Schedule

### Daily
- Monitor error logs
- Check server resources
- Verify reminders are sending

### Weekly
- Backup database
- Review user registrations
- Check disk space

### Monthly
- Analyze slow queries
- Optimize database
- Review system logs
- Update documentation

### Quarterly
- Security audit
- Performance review
- Capacity planning
- Backup verification

## Disaster Recovery

### Database Recovery
```bash
# Restore from backup
mysql -u root -p campus_event_hub < backup_latest.sql
```

### File Recovery
```bash
# Restore from version control
git checkout database/schema.sql
git checkout api/

# Or restore from backup
rsync -av /backups/campus-event-hub/ /var/www/html/campus-event-hub/
```

## Support

For admin issues:
- Check error logs first
- Review this guide
- Consult [INSTALLATION.md](INSTALLATION.md)
- Review [DEVELOPER.md](DEVELOPER.md)

---

**Campus Event Hub - Admin Guide** 🎓
