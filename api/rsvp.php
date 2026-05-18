<?php
/**
 * Campus Event Hub - RSVP Management API
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/RSVP.php';

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized: Please login first');
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? null;
    
    $rsvp = new RSVP();
    $user_id = $_SESSION['user_id'];
    
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'rsvp') {
            if (!isset($data['event_id'])) {
                throw new Exception('Event ID is required');
            }
            
            $status = $data['status'] ?? 'confirmed';
            $event_id = intval($data['event_id']);
            
            // Check if event is full
            if ($status === 'confirmed' && $rsvp->isEventFull($event_id)) {
                throw new Exception('Event is full. You can only express interest.');
            }
            
            $result = $rsvp->rsvpEvent($event_id, $user_id, $status);
            
            echo json_encode([
                'success' => true,
                'message' => 'RSVP saved successfully',
                'data' => ['rsvp_id' => $result]
            ]);
            
        } elseif ($action === 'cancel') {
            if (!isset($data['event_id'])) {
                throw new Exception('Event ID is required');
            }
            
            $event_id = intval($data['event_id']);
            $rsvp->cancelRsvp($event_id, $user_id);
            
            echo json_encode([
                'success' => true,
                'message' => 'RSVP cancelled successfully'
            ]);
        }
        
    } elseif ($method === 'GET') {
        if ($action === 'my_rsvps') {
            $status = $_GET['status'] ?? null;
            $rsvps = $rsvp->getUserRsvps($user_id, $status);
            
            // Format dates
            foreach ($rsvps as &$r) {
                $r['event_date'] = date('Y-m-d H:i', strtotime($r['event_date']));
                $r['rsvp_date'] = date('Y-m-d H:i', strtotime($r['rsvp_date']));
            }
            
            echo json_encode([
                'success' => true,
                'data' => $rsvps
            ]);
            
        } elseif ($action === 'event_rsvps' && isset($_GET['event_id'])) {
            $event_id = intval($_GET['event_id']);
            $status = $_GET['status'] ?? null;
            $rsvps = $rsvp->getEventRsvps($event_id, $status);
            
            echo json_encode([
                'success' => true,
                'data' => $rsvps
            ]);
        }
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
