# Campus Event Hub - Developer Guide

## Architecture

### Design Pattern: MVC with API
- **Models** (`classes/`): Database operations (Event, RSVP, Reminder, User)
- **Controllers** (`api/`): API endpoints for frontend
- **Views** (`public/`): HTML pages and JavaScript

### Data Flow
```
Frontend (JS) → API Endpoints (PHP) → Models (Classes) → Database (SQL)
```

## Code Structure

### Classes Overview

#### Database.php
Core database operations with prepared statements:
```php
$db = new Database();
$row = $db->getRow("SELECT * FROM users WHERE id = ?", "i", [1]);
$rows = $db->getRows("SELECT * FROM events WHERE status = ?", "s", ["upcoming"]);
$id = $db->insert("events", ["title" => "Event", ...]);
$db->update("events", ["status" => "ongoing"], "id = ?", "i", [1]);
```

#### Event.php
Event management operations:
```php
$event = new Event();
$upcoming = $event->getUpcomingEvents($limit, $offset);
$filtered = $event->getEventsByCategory($category_id);
$search = $event->searchEvents($keyword);
$event->createEvent($data);
$event->updateEvent($event_id, $data);
```

#### RSVP.php
RSVP management:
```php
$rsvp = new RSVP();
$rsvp->rsvpEvent($event_id, $user_id, $status);
$rsvp->updateRsvp($event_id, $user_id, "confirmed");
$rsvp->getRsvpCount($event_id, "confirmed");
$rsvp->isEventFull($event_id);
```

#### Reminder.php
Reminder management:
```php
$reminder = new Reminder();
$reminder->setReminder($event_id, $user_id, "email", 24, "hours");
$pending = $reminder->getPendingReminders();
$reminder->markReminderAsSent($reminder_id);
```

#### User.php
User authentication:
```php
$user = new User();
$result = $user->register($student_id, $name, $email, $password);
$result = $user->login($email, $password);
$user->updateProfile($user_id, $data);
$user->changePassword($user_id, $old_pass, $new_pass);
```

## API Development

### Creating a New Endpoint

1. **Create API file** in `api/` directory:
```php
<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Model.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? null;
    $model = new Model();

    if ($method === 'GET') {
        $data = $model->getAll();
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        throw new Exception('Invalid method');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
```

2. **Call from JavaScript**:
```javascript
fetch('api/endpoint.php?action=get_data', {
    method: 'GET'
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log(data.data);
    }
});
```

### Response Format
All endpoints should return JSON:
```json
{
    "success": true|false,
    "data": {...},
    "message": "Error message if success is false"
}
```

## Model Development

### Creating a New Model

1. **Create class** extending Database operations:
```php
<?php
require_once __DIR__ . '/Database.php';

class NewModel {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getAll() {
        $sql = "SELECT * FROM table_name";
        return $this->db->getRows($sql);
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM table_name WHERE id = ?";
        return $this->db->getRow($sql, "i", [$id]);
    }
    
    public function create($data) {
        return $this->db->insert('table_name', $data);
    }
}
?>
```

## Frontend Development

### Adding a New Page

1. **Create PHP file** in `public/`:
```php
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- HTML -->
    <script src="js/main.js"></script>
</body>
</html>
```

2. **Add styling** in `css/`:
```css
/* CSS for new page */
```

3. **Add functionality** in `js/`:
```javascript
// JavaScript for new page
```

### Common Patterns

#### Fetch API Call
```javascript
fetch('api/endpoint.php?action=get', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        key: 'value'
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        // Handle success
    } else {
        console.error(data.message);
    }
});
```

#### Modal Handling
```javascript
function openModal(modalId) {
    document.getElementById(modalId).classList.add('show');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}
```

## Database Development

### Adding a New Table

1. **Create migration** in `database/schema.sql`:
```sql
CREATE TABLE new_table (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES parent_table(id)
);
```

2. **Create index**:
```sql
CREATE INDEX idx_name ON new_table(name);
```

### Query Optimization

Use EXPLAIN to analyze queries:
```sql
EXPLAIN SELECT * FROM events 
WHERE event_date > NOW() 
ORDER BY event_date;
```

## Testing

### Manual Testing Checklist

- [ ] User registration
- [ ] User login/logout
- [ ] Event browsing
- [ ] Category filtering
- [ ] Event search
- [ ] RSVP creation
- [ ] RSVP update
- [ ] Reminder setting
- [ ] Profile update
- [ ] Session management

### Database Testing

```sql
-- Check for orphaned records
SELECT * FROM rsvps WHERE event_id NOT IN (SELECT id FROM events);

-- Check data integrity
SELECT COUNT(*) FROM events WHERE capacity < 0;

-- Verify triggers
SHOW TRIGGERS;
```

## Performance Optimization

### Indexing Strategy
```sql
-- Add indexes for frequently searched columns
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_event_date ON events(event_date);
CREATE INDEX idx_status ON events(status);
```

### Query Optimization
```php
// Bad: N+1 query problem
foreach ($events as $event) {
    $organizer = $db->getRow("SELECT * FROM users WHERE id = ?", "i", [$event['organizer_id']]);
}

// Good: Single query with JOIN
$sql = "SELECT e.*, u.name as organizer_name FROM events e 
        JOIN users u ON e.organizer_id = u.id";
```

## Error Handling

### Best Practices
```php
try {
    // Operation
    if (!$result) {
        throw new Exception('Operation failed');
    }
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
```

## Security Guidelines

### Input Validation
```php
// Always validate input
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    throw new Exception('Invalid email');
}
```

### SQL Injection Prevention
```php
// Good: Prepared statements
$stmt = $db->query("SELECT * FROM users WHERE id = ?", "i", [$id]);

// Bad: String concatenation
$query = "SELECT * FROM users WHERE id = " . $_GET['id']; // Vulnerable!
```

### Password Security
```php
// Hash passwords
$hash = password_hash($password, PASSWORD_BCRYPT);

// Verify passwords
$verified = password_verify($input_password, $hash);
```

## Debugging

### Enable Debug Mode
In `config/database.php`:
```php
define('DEBUG_MODE', true);
```

### Log Queries
```php
error_log('Query: ' . $sql);
error_log('Result: ' . json_encode($result));
```

### Browser Console
```javascript
// Check API responses
fetch('api/endpoint.php')
    .then(r => r.json())
    .then(d => console.log(d));
```

## Deployment Checklist

- [ ] Update database credentials
- [ ] Disable DEBUG_MODE
- [ ] Set up HTTPS/SSL
- [ ] Configure email service
- [ ] Set up cron jobs
- [ ] Review security settings
- [ ] Test all features
- [ ] Set up monitoring
- [ ] Document API endpoints
- [ ] Back up database

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make changes
4. Test thoroughly
5. Submit pull request

## Resources

- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [MDN Web Docs](https://developer.mozilla.org/)
- [REST API Best Practices](https://restfulapi.net/)

---

**Happy coding! 🚀**
