# Campus Event Hub - Installation & Testing Guide

## System Overview

This is a **production-ready** campus event and reminder system built entirely with **PHP and SQL** (MySQL). The system manages events, RSVPs, and personal reminders for students.

### Technology Stack
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Architecture**: MVC with REST-like API endpoints

## Installation Steps

### Step 1: Prerequisites
- PHP 7.4 or higher
- MySQL Server (local or remote)
- Apache/Nginx web server
- Git (optional)

### Step 2: Clone/Extract Project
```bash
cd /var/www/html
git clone https://github.com/dunsinmarcus/campus-event-hub.git
cd campus-event-hub
```

### Step 3: Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE campus_event_hub;
exit

# Import schema
mysql -u root -p campus_event_hub < database/schema.sql

# Optional: Load test data (for quick testing)
mysql -u root -p campus_event_hub < database/test_data.sql
```

### Step 4: Configure Database
Edit `config/database.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'campus_event_hub');
```

### Step 5: Web Server Configuration

#### Option A: Using PHP Built-in Server (Development)
```bash
cd public
php -S localhost:8000
```
Visit: http://localhost:8000

#### Option B: Apache Configuration
Create virtual host in Apache:
```apache
<VirtualHost *:80>
    ServerName campus-event-hub.local
    DocumentRoot /var/www/html/campus-event-hub/public
    
    <Directory /var/www/html/campus-event-hub/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Enable mod_rewrite:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Option C: Nginx Configuration
```nginx
server {
    listen 80;
    server_name campus-event-hub.local;
    root /var/www/html/campus-event-hub/public;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### Step 6: Set Up Reminders (Optional)
Add cron job to send automated reminders:
```bash
# Edit crontab
crontab -e

# Add this line to run reminders every hour
0 * * * * php /var/www/html/campus-event-hub/cron/send_reminders.php
```

## Testing the System

### 1. Register a Student
1. Navigate to login page
2. Click "Register here"
3. Fill in details:
   - Student ID: STU001
   - Name: Your Name
   - Email: your@email.com
   - Password: password123
4. Click Register

### 2. Browse Events
- View all upcoming events on homepage
- Use category filters (Seminar, Sports, etc.)
- Search for specific events

### 3. Test RSVP
- Click on any event
- Choose response: Confirmed, Interested, or Declined
- Click RSVP button

### 4. Test Reminders
- On event details page
- Select reminder type: Email, SMS, or In-app
- Set reminder time
- Click "Set"

### 5. View Your Events
- Click "My Events" in navbar
- See all registered events
- View RSVP status

### 6. Manage Profile
- Click "Profile" in navbar
- View your information
- Update profile details

## API Endpoints Reference

### Authentication
```
POST   /api/auth.php?action=register       - Register new student
POST   /api/auth.php?action=login          - Login
GET    /api/auth.php?action=logout         - Logout
GET    /api/auth.php?action=check_session  - Check login status
```

### Events
```
GET    /api/get_events.php                 - Get all upcoming events
GET    /api/get_events.php?category_id=1   - Filter by category
GET    /api/get_events.php?search=keyword  - Search events
GET    /api/get_event_details.php?id=1     - Get event details
```

### RSVP
```
POST   /api/rsvp.php?action=rsvp           - RSVP to event
POST   /api/rsvp.php?action=cancel         - Cancel RSVP
GET    /api/rsvp.php?action=my_rsvps       - Get my RSVPs
```

### Reminders
```
POST   /api/reminder.php?action=set        - Set reminder
POST   /api/reminder.php?action=delete     - Delete reminder
GET    /api/reminder.php?action=my_reminders - Get my reminders
```

### User Profile
```
GET    /api/user_profile.php?action=profile           - Get profile
POST   /api/user_profile.php?action=update_profile    - Update profile
POST   /api/user_profile.php?action=change_password   - Change password
```

## Database Schema Overview

### Users Table
- Stores student credentials and information
- Supports password hashing with bcrypt
- Tracks department and contact info

### Events Table
- Stores event details
- Tracks capacity and current attendance
- Supports event status (upcoming, ongoing, completed, cancelled)
- Foreign key to categories and organizers

### RSVPs Table
- Links students to events
- Tracks response status (confirmed, interested, declined)
- Unique constraint prevents duplicate RSVPs

### Reminders Table
- Stores reminder preferences per student per event
- Supports multiple reminder types
- Tracks if reminder has been sent

### Notifications Table
- Stores in-app notifications
- Tracks read/unread status

## Features

### ✅ Implemented
- User registration and authentication
- Event browsing with pagination
- Event filtering by category
- Event search functionality
- RSVP management (Confirmed/Interested/Declined)
- Personal reminders (Email/SMS/In-app)
- Automated reminder sending (via cron)
- User profile management
- Attendee tracking
- Event capacity management
- Event status management

### 📋 Future Enhancements
- Event organizer dashboard
- Admin panel for event management
- Event ratings and reviews
- Ticket system for paid events
- Calendar view
- Real-time notifications
- Mobile app integration
- Advanced analytics

## Troubleshooting

### Issue: "Connection failed"
**Solution**: 
- Verify MySQL is running: `sudo service mysql status`
- Check credentials in `config/database.php`
- Ensure database exists: `mysql -u root -p -e "SHOW DATABASES;"`

### Issue: "404 Not Found"
**Solution**:
- Verify .htaccess is in place (for Apache)
- Check virtual host is configured
- Ensure DocumentRoot points to `public/` directory

### Issue: "Cannot login"
**Solution**:
- Check database is populated with users
- Verify password hashing works: Use bcrypt
- Clear browser cookies and try again

### Issue: "RSVP not saving"
**Solution**:
- Ensure user is logged in
- Check session is enabled in PHP
- Verify database write permissions
- Review PHP error logs

### Issue: "Reminders not sending"
**Solution**:
- Check cron job is running: `crontab -l`
- Verify email configuration in `cron/send_reminders.php`
- Check mail server is configured
- Review error logs in cron directory

## Performance Tips

1. **Database Optimization**
   - Use the provided indexes
   - Archive old events periodically
   - Optimize queries with EXPLAIN

2. **Caching**
   - Cache event categories
   - Cache event lists with Memcached
   - Use browser caching for static files

3. **Scalability**
   - Use database connection pooling
   - Implement query optimization
   - Consider read replicas for high traffic

## Security Best Practices

1. ✅ Passwords hashed with bcrypt
2. ✅ SQL injection protection (prepared statements)
3. ✅ Session management
4. ✅ Input validation

**Recommended additions:**
- HTTPS/SSL encryption
- CSRF token validation
- Rate limiting
- Two-factor authentication
- API authentication tokens

## Support & Documentation

- **Main README**: [README.md](README.md)
- **Quick Start**: [QUICK_START.md](QUICK_START.md)
- **This Guide**: [INSTALLATION.md](INSTALLATION.md)

## License

This project is open source and available under the MIT License.

---

**Campus Event Hub - Built with PHP and SQL** 🎓
