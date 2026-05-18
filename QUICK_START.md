# Campus Event Hub - Quick Start Guide

Get the system running in 5 minutes!

## 1. Database Setup (2 minutes)

```bash
# Create database
mysql -u root -p << EOF
CREATE DATABASE campus_event_hub;
EOF

# Import schema
mysql -u root -p campus_event_hub < database/schema.sql

# (Optional) Load test data
mysql -u root -p campus_event_hub < database/test_data.sql
```

## 2. Configure Database Connection (1 minute)

Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Your MySQL user
define('DB_PASS', 'password');    // Your MySQL password
define('DB_NAME', 'campus_event_hub');
```

## 3. Start Web Server (1 minute)

**Using PHP Built-in Server:**
```bash
cd public
php -S localhost:8000
```

Visit: `http://localhost:8000`

**Using Apache/Nginx:**
Configure virtual host to point to `public/` directory

## 4. Create Test User (1 minute)

Visit the registration page and create an account:
- **Student ID**: STU001
- **Name**: Your Name
- **Email**: your@email.com
- **Password**: password123

## Quick Test

1. **Register** → Click "Register here" link
2. **Login** → Enter your credentials
3. **Browse Events** → View event catalog (if test data loaded)
4. **RSVP** → Click event and confirm attendance
5. **Set Reminder** → Choose reminder type and timing

## Database Quick Reference

### Create a test event via MySQL:
```sql
INSERT INTO events (title, description, category_id, location, event_date, capacity, organizer_id)
VALUES ('My Event', 'Event description', 1, 'Main Hall', '2026-06-01 10:00:00', 100, 5);
```

### View all events:
```sql
SELECT id, title, event_date, location FROM events ORDER BY event_date;
```

### View RSVPs for an event:
```sql
SELECT u.name, r.status FROM rsvps r
JOIN users u ON r.user_id = u.id
WHERE r.event_id = 1;
```

## Troubleshooting

**Can't connect to database?**
- Check MySQL is running: `sudo service mysql status`
- Verify credentials in `config/database.php`
- Check database exists: `mysql -u root -p -e "SHOW DATABASES;"`

**Page not loading?**
- Ensure PHP is installed: `php -v`
- Check Apache/Nginx is running
- Review error logs for details

**RSVP not working?**
- Make sure you're logged in
- Check session cookie settings
- Verify database permissions

## Key Features to Try

✓ **Event Browsing** - Filter by category, search functionality
✓ **RSVP Management** - Confirm/decline/interested status
✓ **Reminders** - Email, SMS, or in-app notifications
✓ **User Profile** - Update personal information
✓ **Attendance Tracking** - View event attendee lists

## Next Steps

1. Add real event organizers
2. Configure email service for reminders
3. Set up cron job for automated reminders
4. Customize event categories
5. Add admin dashboard

## Support

For detailed documentation, see the main [README.md](README.md)

---

**Enjoy Campus Event Hub! 🎓**
