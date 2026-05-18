<?php
/**
 * Campus Event Hub - RSVP Model
 */

require_once __DIR__ . '/Database.php';

class RSVP {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * RSVP to an event
     */
    public function rsvpEvent($event_id, $user_id, $status = 'confirmed') {
        $existing = $this->getRsvp($event_id, $user_id);
        
        if ($existing) {
            // Update existing RSVP
            return $this->updateRsvp($event_id, $user_id, $status);
        }
        
        // Create new RSVP
        $data = [
            'event_id' => $event_id,
            'user_id' => $user_id,
            'status' => $status
        ];
        
        return $this->db->insert('rsvps', $data);
    }
    
    /**
     * Get RSVP record
     */
    public function getRsvp($event_id, $user_id) {
        $sql = "SELECT * FROM rsvps WHERE event_id = ? AND user_id = ?";
        return $this->db->getRow($sql, 'ii', [$event_id, $user_id]);
    }
    
    /**
     * Update RSVP status
     */
    public function updateRsvp($event_id, $user_id, $status) {
        return $this->db->update(
            'rsvps',
            ['status' => $status],
            'event_id = ? AND user_id = ?',
            'ii',
            [$event_id, $user_id]
        );
    }
    
    /**
     * Cancel RSVP
     */
    public function cancelRsvp($event_id, $user_id) {
        return $this->db->delete('rsvps', 'event_id = ? AND user_id = ?', 'ii', [$event_id, $user_id]);
    }
    
    /**
     * Get RSVPs for an event
     */
    public function getEventRsvps($event_id, $status = null) {
        if ($status) {
            $sql = "SELECT r.*, u.name, u.email 
                    FROM rsvps r
                    JOIN users u ON r.user_id = u.id
                    WHERE r.event_id = ? AND r.status = ?
                    ORDER BY r.rsvp_date DESC";
            return $this->db->getRows($sql, 'is', [$event_id, $status]);
        }
        
        $sql = "SELECT r.*, u.name, u.email 
                FROM rsvps r
                JOIN users u ON r.user_id = u.id
                WHERE r.event_id = ?
                ORDER BY r.status DESC, r.rsvp_date DESC";
        return $this->db->getRows($sql, 'i', [$event_id]);
    }
    
    /**
     * Get user's RSVPs
     */
    public function getUserRsvps($user_id, $status = null) {
        if ($status) {
            $sql = "SELECT r.*, e.title, e.event_date, e.location
                    FROM rsvps r
                    JOIN events e ON r.event_id = e.id
                    WHERE r.user_id = ? AND r.status = ?
                    ORDER BY e.event_date DESC";
            return $this->db->getRows($sql, 'is', [$user_id, $status]);
        }
        
        $sql = "SELECT r.*, e.title, e.event_date, e.location
                FROM rsvps r
                JOIN events e ON r.event_id = e.id
                WHERE r.user_id = ?
                ORDER BY e.event_date DESC";
        return $this->db->getRows($sql, 'i', [$user_id]);
    }
    
    /**
     * Get RSVP count for event
     */
    public function getRsvpCount($event_id, $status = 'confirmed') {
        $sql = "SELECT COUNT(*) as count FROM rsvps WHERE event_id = ? AND status = ?";
        $result = $this->db->getRow($sql, 'is', [$event_id, $status]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Check if event is full
     */
    public function isEventFull($event_id) {
        $sql = "SELECT e.capacity, COUNT(r.id) as confirmed_count
                FROM events e
                LEFT JOIN rsvps r ON e.id = r.event_id AND r.status = 'confirmed'
                WHERE e.id = ?
                GROUP BY e.id";
        
        $result = $this->db->getRow($sql, 'i', [$event_id]);
        
        if (!$result) {
            return false;
        }
        
        return $result['confirmed_count'] >= $result['capacity'];
    }
}
?>
