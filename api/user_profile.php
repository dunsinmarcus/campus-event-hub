<?php
/**
 * Campus Event Hub - User Profile API
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/User.php';

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized: Please login first');
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? null;
    
    $user = new User();
    $user_id = $_SESSION['user_id'];
    
    if ($method === 'GET') {
        if ($action === 'profile') {
            $current_user = $user->getUserById($user_id);
            
            if (!$current_user) {
                throw new Exception('User not found');
            }
            
            echo json_encode([
                'success' => true,
                'user' => $current_user
            ]);
        }
        
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'update_profile') {
            $user->updateProfile($user_id, $data);
            
            $updated_user = $user->getUserById($user_id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $updated_user
            ]);
            
        } elseif ($action === 'change_password') {
            if (!isset($data['old_password']) || !isset($data['new_password'])) {
                throw new Exception('Old and new passwords are required');
            }
            
            $result = $user->changePassword($user_id, $data['old_password'], $data['new_password']);
            
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => $result['message']
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
