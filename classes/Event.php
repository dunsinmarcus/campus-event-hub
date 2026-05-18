<?php
/**
 * Campus Event Hub - Event Model
 */

require_once __DIR__ . '/Database.php';

class Event {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get all upcoming events
     */
    public function getUpcomingEvents($limit = 20, $offset = 0) {
        $sql = "SELECT e.*, 
                ec.name as category_name, 
                ec.color as category_color,
                u.name as organizer_name,
                COUNT(r.id) as rsvp_count
                FROM events e
                LEFT JOIN event_categories ec ON e.category_id = ec.id
                LEFT JOIN users u ON e.organizer_id = u.id
                LEFT JOIN rsvps r ON e.id = r.event_id AND r.status = 'confirmed'
                WHERE e.event_date > NOW() AND e.status != 'cancelled'
                GROUP BY e.id
                ORDER BY e.event_date ASC
                LIMIT ? OFFSET ?";
        
        return $this->db->getRows($sql, 'ii', [$limit, $offset]);
    }
    
    /**
     * Get events by category
     */
    public function getEventsByCategory($category_id, $limit = 20, $offset = 0) {
        $sql = "SELECT e.*, 
                ec.name as category_name,
                ec.color as category_color,
                u.name as organizer_name,
                COUNT(r.id) as rsvp_count
                FROM events e
                LEFT JOIN event_categories ec ON e.category_id = ec.id
                LEFT JOIN users u ON e.organizer_id = u.id
                LEFT JOIN rsvps r ON e.id = r.event_id AND r.status = 'confirmed'
                WHERE e.category_id = ? AND e.event_date > NOW() AND e.status != 'cancelled'
                GROUP BY e.id
                ORDER BY e.event_date ASC
                LIMIT ? OFFSET ?";
        
        return $this->db->getRows($sql, 'iii', [$category_id, $limit, $offset]);
    }
    
    /**
     * Search events by title or description
     */
    public function searchEvents($keyword, $limit = 20, $offset = 0) {
        $sql = "SELECT e.*, 
                ec.name as category_name,
                ec.color as category_color,
                u.name as organizer_name,
                COUNT(r.id) as rsvp_count
                FROM events e
                LEFT JOIN event_categories ec ON e.category_id = ec.id
                LEFT JOIN users u ON e.organizer_id = u.id
                LEFT JOIN rsvps r ON e.id = r.event_id AND r.status = 'confirmed'
                WHERE (e.title LIKE ? OR e.description LIKE ?) 
                AND e.event_date > NOW() 
                AND e.status != 'cancelled'
                GROUP BY e.id
                ORDER BY e.event_date ASC
                LIMIT ? OFFSET ?";
        
        $keyword_param = '%' . $keyword . '%';
        return $this->db->getRows($sql, 'ssii', [$keyword_param, $keyword_param, $limit, $offset]);
    }
    
    /**
     * Get event by ID with details
     */
    public function getEventById($event_id) {
        $sql = "SELECT e.*, 
                ec.name as category_name,
                ec.color as category_color,
                u.name as organizer_name,
                u.email as organizer_email,
                COUNT(r.id) as rsvp_count
                FROM events e
                LEFT JOIN event_categories ec ON e.category_id = ec.id
                LEFT JOIN users u ON e.organizer_id = u.id
                LEFT JOIN rsvps r ON e.id = r.event_id AND r.status = 'confirmed'
                WHERE e.id = ?
                GROUP BY e.id";
        
        return $this->db->getRow($sql, 'i', [$event_id]);
    }
    
    /**
     * Get user's RSVP status for an event
     */
    public function getUserRsvpStatus($event_id, $user_id) {
        $sql = "SELECT * FROM rsvps WHERE event_id = ? AND user_id = ?";
        return $this->db->getRow($sql, 'ii', [$event_id, $user_id]);
    }
    
    /**
     * Get all attendees for an event
     */
    public function getEventAttendees($event_id) {
        $sql = "SELECT u.id, u.name, u.email, u.student_id, r.status, r.rsvp_date
                FROM rsvps r
                JOIN users u ON r.user_id = u.id
                WHERE r.event_id = ? AND r.status = 'confirmed'
                ORDER BY r.rsvp_date ASC";
        
        return $this->db->getRows($sql, 'i', [$event_id]);
    }
    
    /**
     * Get user's registered events
     */
    public function getUserEvents($user_id, $status = 'confirmed') {
        $sql = "SELECT e.*, 
                ec.name as category_name,
                ec.color as category_color,
                r.status as rsvp_status,
                r.rsvp_date
                FROM events e
                LEFT JOIN event_categories ec ON e.category_id = ec.id
                JOIN rsvps r ON e.id = r.event_id
                WHERE r.user_id = ? AND r.status = ? AND e.event_date > NOW()
                ORDER BY e.event_date ASC";
        
        return $this->db->getRows($sql, 'is', [$user_id, $status]);
    }
    
    /**
     * Create a new event
     */
    public function createEvent($data) {
        $insert_data = [
            'title' => $data['title'],
            'description' => $data['description'],
            'category_id' => $data['category_id'],
            'location' => $data['location'],
            'event_date' => $data['event_date'],
            'end_date' => $data['end_date'] ?? $data['event_date'],
            'capacity' => $data['capacity'] ?? 100,
            'organizer_id' => $data['organizer_id'],
            'image_url' => $data['image_url'] ?? null
        ];
        
        return $this->db->insert('events', $insert_data);
    }
    
    /**
     * Update event
     */
    public function updateEvent($event_id, $data) {
        $update_data = array_filter([
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'location' => $data['location'] ?? null,
            'event_date' => $data['event_date'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'status' => $data['status'] ?? null
        ], fn($v) => $v !== null);
        
        if (empty($update_data)) {
            return 0;
        }
        
        return $this->db->update('events', $update_data, 'id = ?', 'i', [$event_id]);
    }
    
    /**
     * Cancel event
     */
    public function cancelEvent($event_id) {
        return $this->db->update('events', ['status' => 'cancelled'], 'id = ?', 'i', [$event_id]);
    }
    
    /**
     * Get all categories
     */
    public function getCategories() {
        $sql = "SELECT * FROM event_categories ORDER BY name ASC";
        return $this->db->getRows($sql);
    }
}
?>
