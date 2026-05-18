<?php
/**
 * Campus Event Hub - Get All Upcoming Events API
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Event.php';

try {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
    $search = isset($_GET['search']) ? $_GET['search'] : null;
    
    $event = new Event();
    
    if ($search) {
        $events = $event->searchEvents($search, $limit, $offset);
    } elseif ($category_id) {
        $events = $event->getEventsByCategory($category_id, $limit, $offset);
    } else {
        $events = $event->getUpcomingEvents($limit, $offset);
    }
    
    // Format dates
    foreach ($events as &$e) {
        $e['event_date'] = date('Y-m-d H:i', strtotime($e['event_date']));
        if ($e['end_date']) {
            $e['end_date'] = date('Y-m-d H:i', strtotime($e['end_date']));
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $events
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
