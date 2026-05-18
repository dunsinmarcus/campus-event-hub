# Campus Event Hub 🎓

A comprehensive campus event and reminder system built with **PHP and SQL** that allows students to discover upcoming events, RSVP to events, and receive personal reminders.

## Features ✨

### Student Features
- **Browse Events**: View all upcoming campus events with filters and search functionality
- **Event Details**: See comprehensive event information including location, time, capacity, and organizer details
- **RSVP System**: Confirm, express interest, or decline event attendance
- **Personal Reminders**: Set email, SMS, or in-app reminders before events
- **My Events Dashboard**: View all registered events in one place
- **User Profile**: Manage personal information and preferences

### System Features
- **Event Categories**: Organized event filtering (Seminars, Sports, Cultural, Social, Career, Tech)
- **Automatic Status Management**: Events automatically transition between upcoming, ongoing, and completed status
- **Capacity Management**: Track event capacity and prevent overbooking
- **Attendance Tracking**: View attendee lists and RSVP statistics
- **Email Reminders**: Automated reminder system using cron jobs
- **Session Management**: Secure user authentication and session handling

## Technology Stack 🛠️

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Architecture**: MVC pattern with API endpoints

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Command line access

### Step 1: Clone the Repository
```bash
git clone https://github.com/dunsinmarcus/campus-event-hub.git
cd campus-event-hub
```

### Step 2: Database Setup

1. Create a new MySQL database:
```sql
CREATE DATABASE campus_event_hub;
```

2. Import the schema:
```bash
mysql -u root -p campus_event_hub < database/schema.sql
```

3. Update database credentials in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'campus_event_hub');
```

### Step 3: Web Server Configuration

**For Apache:**
1. Create a virtual host pointing to the `public/` directory
2. Enable `.htaccess` support (mod_rewrite)
3. Restart Apache

**For Nginx:**
```nginx
server {
    listen 80;
    server_name campus-event-hub.local;
    root /path/to/campus-event-hub/public;
    index index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### Step 4: Set Up Reminder Cron Job

Add this to your crontab to send reminders every hour:
```bash
0 * * * * php /path/to/campus-event-hub/cron/send_reminders.php
```

Or run manually:
```bash
php cron/send_reminders.php
```

## Project Structure 📁

```
campus-event-hub/
├── api/                           # API endpoints
│   ├── auth.php                   # Authentication (login, register)
│   ├── get_events.php             # Get events with filtering
│   ├── get_event_details.php      # Get single event details
│   ├── rsvp.php                   # RSVP management
│   ├── reminder.php               # Reminder management
│   └── user_profile.php           # User profile management
├── classes/                       # PHP classes
│   ├── Database.php               # Database helper class
│   ├── Event.php                  # Event model
│   ├── RSVP.php                   # RSVP model
│   ├── Reminder.php               # Reminder model
│   └── User.php                   # User model
├── config/                        # Configuration files
│   └── database.php               # Database connection
├── database/                      # Database files
│   └── schema.sql                 # Database schema
├── public/                        # Web root
│   ├── css/                       # Stylesheets
│   │   ├── style.css              # Main styles
│   │   ├── auth.css               # Auth page styles
│   │   └── events.css             # Event styles
│   ├── js/                        # JavaScript files
│   │   ├── main.js                # Main functionality
│   │   ├── auth.js                # Auth functions
│   │   └── events.js              # Event functions
│   ├── index.php                  # Home page
│   ├── login.php                  # Login page
│   └── register.php               # Registration page
└── cron/                          # Cron jobs
    └── send_reminders.php         # Send reminder script
```

## API Endpoints

### Authentication
- **POST** `/api/auth.php?action=register` - Register new student
- **POST** `/api/auth.php?action=login` - Login student
- **GET** `/api/auth.php?action=logout` - Logout
- **GET** `/api/auth.php?action=check_session` - Check login status

### Events
- **GET** `/api/get_events.php?limit=20&offset=0` - Get upcoming events (with pagination)
- **GET** `/api/get_events.php?category_id=1` - Filter by category
- **GET** `/api/get_events.php?search=query` - Search events
- **GET** `/api/get_event_details.php?id=1` - Get single event details

### RSVP
- **POST** `/api/rsvp.php?action=rsvp` - RSVP to event
- **POST** `/api/rsvp.php?action=cancel` - Cancel RSVP
- **GET** `/api/rsvp.php?action=my_rsvps` - Get user's RSVPs
- **GET** `/api/rsvp.php?action=event_rsvps&event_id=1` - Get event RSVPs

### Reminders
- **POST** `/api/reminder.php?action=set` - Set reminder
- **POST** `/api/reminder.php?action=delete` - Delete reminder
- **GET** `/api/reminder.php?action=my_reminders` - Get user's reminders
- **GET** `/api/reminder.php?action=event_reminders&event_id=1` - Get event reminders

### User Profile
- **GET** `/api/user_profile.php?action=profile` - Get user profile
- **POST** `/api/user_profile.php?action=update_profile` - Update profile
- **POST** `/api/user_profile.php?action=change_password` - Change password

## Usage Guide

### For Students

1. **Register**: Create an account with your student ID and email
2. **Explore Events**: Browse upcoming events by category or search
3. **View Details**: Click on an event to see full details, attendees, and organizer info
4. **RSVP**: Confirm your attendance or express interest
5. **Set Reminders**: Choose reminder type (email/SMS/in-app) and timing
6. **Manage Profile**: Update your information and view registered events

### For Administrators

Admin features can be implemented by:
1. Adding admin role to users table
2. Creating admin routes/pages
3. Adding event creation/management endpoints

## Database Schema

### Users Table
- Stores student information and authentication
- Fields: id, student_id, name, email, password, phone, department

### Events Table
- Stores event information
- Fields: id, title, description, category_id, location, event_date, capacity, etc.

### RSVPs Table
- Tracks student attendance responses
- Prevents duplicate RSVPs with unique constraint on (event_id, user_id)

### Reminders Table
- Stores reminder preferences
- Supports multiple reminder types per event

### Notifications Table
- Stores in-app notifications
- Used for notification center functionality

## Security Features 🔒

- **Password Hashing**: Bcrypt password hashing
- **Prepared Statements**: SQL injection protection
- **Session Management**: Secure PHP sessions
- **Input Validation**: Server-side validation on all inputs
- **CSRF Protection**: Can be added with token validation
- **XSS Prevention**: HTML escaping in output

## Configuration

### Reminder Settings
Edit `cron/send_reminders.php` to customize:
- Email template
- SMS provider integration
- In-app notification messages
- Retry logic for failed reminders

### Email Configuration
To enable actual email sending, update `send_reminders.php`:
```php
// Replace the mail() call with your email service
// Example: SendGrid, Mailgun, AWS SES, etc.
mail($to, $subject, $message, $headers);
```

## Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check credentials in `config/database.php`
- Ensure database and tables exist

### RSVP Not Working
- Verify user is logged in (check session)
- Check database permissions
- Look for PHP errors in error logs

### Reminders Not Sending
- Verify cron job is running
- Check mail configuration
- Review error logs in `cron/send_reminders.php`

### Sessions Not Persisting
- Verify session save path is writable
- Check PHP session configuration
- Clear browser cookies and retry

## Performance Optimization

1. **Database Indexes**: Already optimized in schema
2. **Pagination**: Events paginated by 12 per page
3. **Lazy Loading**: Event images only load when needed
4. **Caching**: Can be added for category and event lists
5. **Database Connection Pooling**: Recommended for high traffic

## Future Enhancements

- [ ] Event ratings and reviews
- [ ] Ticket system for paid events
- [ ] Event organizer dashboard
- [ ] Real-time notifications with WebSocket
- [ ] Mobile app integration
- [ ] Analytics dashboard
- [ ] Calendar view for events
- [ ] Event collaboration features

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License

This project is open source and available under the MIT License.

## Support

For issues and questions:
- Create an issue on GitHub
- Contact: support@campuseventhub.local

## Authors

**Campus Event Hub Development Team**

---

**Built with ❤️ using PHP and SQL**
