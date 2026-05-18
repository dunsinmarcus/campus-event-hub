<?php
/**
 * Campus Event Hub - Reminder Management API
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Reminder.php';

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized: Please login first');
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? null;
    
    $reminder = new Reminder();
    $user_id = $_SESSION['user_id'];
    
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'set') {
            if (!isset($data['event_id'])) {
                throw new Exception('Event ID is required');
            }
            
            $event_id = intval($data['event_id']);
            $reminder_type = $data['reminder_type'] ?? 'email';
            $reminder_time = intval($data['reminder_time'] ?? 24);
            $reminder_unit = $data['reminder_unit'] ?? 'hours';
            
            $reminder->setReminder($event_id, $user_id, $reminder_type, $reminder_time, $reminder_unit);
            
            echo json_encode([
                'success' => true,
                'message' => 'Reminder set successfully'
            ]);
            
        } elseif ($action === 'delete') {
            if (!isset($data['event_id'])) {
                throw new Exception('Event ID is required');
            }
            
            $event_id = intval($data['event_id']);
            $reminder_type = $data['reminder_type'] ?? null;
            
            $reminder->deleteReminder($event_id, $user_id, $reminder_type);
            
            echo json_encode([
                'success' => true,
                'message' => 'Reminder deleted successfully'
            ]);
        }
        
    } elseif ($method === 'GET') {
        if ($action === 'my_reminders') {
            $reminders = $reminder->getUserReminders($user_id);
            
            // Format dates
            foreach ($reminders as &$r) {
                $r['event_date'] = date('Y-m-d H:i', strtotime($r['event_date']));
                $r['created_at'] = date('Y-m-d H:i', strtotime($r['created_at']));
                if ($r['sent_at']) {
                    $r['sent_at'] = date('Y-m-d H:i', strtotime($r['sent_at']));
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => $reminders
            ]);
            
        } elseif ($action === 'event_reminders' && isset($_GET['event_id'])) {
            $event_id = intval($_GET['event_id']);
            $reminders = $reminder->getUserEventReminders($event_id, $user_id);
            
            echo json_encode([
                'success' => true,
                'data' => $reminders
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
