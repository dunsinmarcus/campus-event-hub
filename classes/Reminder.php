<?php
/**
 * Campus Event Hub - Reminder Model
 */

require_once __DIR__ . '/Database.php';

class Reminder {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Set a reminder for an event
     */
    public function setReminder($event_id, $user_id, $reminder_type = 'email', $reminder_time = 24, $reminder_unit = 'hours') {
        $data = [
            'event_id' => $event_id,
            'user_id' => $user_id,
            'reminder_type' => $reminder_type,
            'reminder_time' => $reminder_time,
            'reminder_unit' => $reminder_unit
        ];
        
        $sql = "INSERT INTO reminders (event_id, user_id, reminder_type, reminder_time, reminder_unit)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE reminder_time = VALUES(reminder_time), reminder_unit = VALUES(reminder_unit)";
        
        $stmt = $this->db->query($sql, 'iisis', [$event_id, $user_id, $reminder_type, $reminder_time, $reminder_unit]);
        return $stmt;
    }
    
    /**
     * Get reminders for a user
     */
    public function getUserReminders($user_id) {
        $sql = "SELECT r.*, e.title, e.event_date, e.location
                FROM reminders r
                JOIN events e ON r.event_id = e.id
                WHERE r.user_id = ?
                ORDER BY e.event_date ASC";
        
        return $this->db->getRows($sql, 'i', [$user_id]);
    }
    
    /**
     * Get reminders for an event
     */
    public function getEventReminders($event_id) {
        $sql = "SELECT r.*, u.name, u.email, e.event_date
                FROM reminders r
                JOIN users u ON r.user_id = u.id
                JOIN events e ON r.event_id = e.id
                WHERE r.event_id = ?
                ORDER BY u.name ASC";
        
        return $this->db->getRows($sql, 'i', [$event_id]);
    }
    
    /**
     * Get pending reminders (reminders that need to be sent)
     */
    public function getPendingReminders() {
        $sql = "SELECT r.*, u.email, u.name, e.title, e.event_date, e.location
                FROM reminders r
                JOIN users u ON r.user_id = u.id
                JOIN events e ON r.event_id = e.id
                WHERE r.is_sent = FALSE
                AND DATE_ADD(e.event_date, INTERVAL -r.reminder_time r.reminder_unit) <= NOW()
                AND e.event_date > NOW()
                ORDER BY e.event_date ASC";
        
        return $this->db->getRows($sql);
    }
    
    /**
     * Send reminder (mark as sent)
     */
    public function markReminderAsSent($reminder_id) {
        return $this->db->update(
            'reminders',
            ['is_sent' => 1, 'sent_at' => date('Y-m-d H:i:s')],
            'id = ?',
            'i',
            [$reminder_id]
        );
    }
    
    /**
     * Delete reminder
     */
    public function deleteReminder($event_id, $user_id, $reminder_type = null) {
        if ($reminder_type) {
            return $this->db->delete(
                'reminders',
                'event_id = ? AND user_id = ? AND reminder_type = ?',
                'iis',
                [$event_id, $user_id, $reminder_type]
            );
        }
        
        return $this->db->delete(
            'reminders',
            'event_id = ? AND user_id = ?',
            'ii',
            [$event_id, $user_id]
        );
    }
    
    /**
     * Get user's reminder preferences for an event
     */
    public function getUserEventReminders($event_id, $user_id) {
        $sql = "SELECT * FROM reminders WHERE event_id = ? AND user_id = ? ORDER BY reminder_type";
        return $this->db->getRows($sql, 'ii', [$event_id, $user_id]);
    }
}
?>
