<?php
/**
 * Campus Event Hub - Get Event Details API
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Event.php';
require_once __DIR__ . '/../classes/RSVP.php';

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Event ID is required');
    }
    
    $event_id = intval($_GET['id']);
    $event_model = new Event();
    $rsvp_model = new RSVP();
    
    $event = $event_model->getEventById($event_id);
    
    if (!$event) {
        throw new Exception('Event not found');
    }
    
    // Get attendees count
    $event['rsvp_count'] = $rsvp_model->getRsvpCount($event_id, 'confirmed');
    $event['interested_count'] = $rsvp_model->getRsvpCount($event_id, 'interested');
    $event['declined_count'] = $rsvp_model->getRsvpCount($event_id, 'declined');
    
    // Get user RSVP status if logged in
    if (isset($_SESSION['user_id'])) {
        $user_rsvp = $event_model->getUserRsvpStatus($event_id, $_SESSION['user_id']);
        $event['user_rsvp_status'] = $user_rsvp['status'] ?? null;
    }
    
    // Format dates
    $event['event_date'] = date('Y-m-d H:i', strtotime($event['event_date']));
    if ($event['end_date']) {
        $event['end_date'] = date('Y-m-d H:i', strtotime($event['end_date']));
    }
    
    echo json_encode([
        'success' => true,
        'data' => $event
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
