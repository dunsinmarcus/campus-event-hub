<?php
/**
 * Campus Event Hub - Send Reminders Cron Job
 * This script should be run periodically (e.g., every hour) to send pending reminders
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Reminder.php';

$reminder = new Reminder();

// Get pending reminders
$pending = $reminder->getPendingReminders();

$sent_count = 0;
$failed_count = 0;

foreach ($pending as $rem) {
    try {
        // Send email reminder
        if ($rem['reminder_type'] === 'email') {
            sendEmailReminder($rem);
        }
        // SMS reminder (implement based on your SMS provider)
        elseif ($rem['reminder_type'] === 'sms') {
            sendSmsReminder($rem);
        }
        // In-app notification
        elseif ($rem['reminder_type'] === 'in_app') {
            sendInAppReminder($rem);
        }
        
        // Mark reminder as sent
        $reminder->markReminderAsSent($rem['id']);
        $sent_count++;
        
    } catch (Exception $e) {
        error_log("Failed to send reminder {$rem['id']}: " . $e->getMessage());
        $failed_count++;
    }
}

echo "Reminders sent: $sent_count, Failed: $failed_count\n";

/**
 * Send email reminder
 */
function sendEmailReminder($reminder) {
    $to = $reminder['email'];
    $subject = "Reminder: {$reminder['title']} is coming up!";
    
    $message = "
    <html>
    <head>
        <title>{$subject}</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #3498db; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
            .content { background-color: #f9f9f9; padding: 20px; border-radius: 0 0 5px 5px; }
            .event-info { margin: 15px 0; }
            .button { background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Event Reminder</h2>
            </div>
            <div class='content'>
                <p>Hi {$reminder['name']},</p>
                <p>This is a reminder that you have registered for an upcoming event:</p>
                
                <div class='event-info'>
                    <h3>{$reminder['title']}</h3>
                    <p><strong>Date & Time:</strong> " . date('M d, Y - H:i', strtotime($reminder['event_date'])) . "</p>
                    <p><strong>Location:</strong> {$reminder['location']}</p>
                </div>
                
                <p>Make sure not to miss this exciting event!</p>
                
                <p style='margin-top: 20px; color: #666; font-size: 12px;'>
                    This is an automated reminder from Campus Event Hub.
                </p>
            </div>
        </div>
    </body>
    </html>";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@campuseventhub.local\r\n";
    
    // In production, replace with actual mail service
    // For now, this logs the email that would be sent
    error_log("Email would be sent to: $to - Subject: $subject");
    
    // Uncomment to use PHP mail function
    // mail($to, $subject, $message, $headers);
}

/**
 * Send SMS reminder
 */
function sendSmsReminder($reminder) {
    // Implement SMS provider integration (Twilio, etc.)
    error_log("SMS reminder would be sent to: {$reminder['phone']} - Event: {$reminder['title']}");
}

/**
 * Send in-app notification
 */
function sendInAppReminder($reminder) {
    global $conn;
    
    $title = "Upcoming Event: {$reminder['title']}";
    $message = "Your event '{$reminder['title']}' is coming up on " . date('M d, Y', strtotime($reminder['event_date'])) . "!";
    
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, event_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('issi', $reminder['user_id'], $title, $message, $reminder['event_id']);
    $stmt->execute();
}
?>
