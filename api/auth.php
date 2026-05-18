<?php
/**
 * Campus Event Hub - Authentication API
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/User.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? null;
    
    $user = new User();
    
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'register') {
            $required = ['student_id', 'name', 'email', 'password'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("$field is required");
                }
            }
            
            $result = $user->register(
                $data['student_id'],
                $data['name'],
                $data['email'],
                $data['password'],
                $data['phone'] ?? null,
                $data['department'] ?? null
            );
            
            if ($result['success']) {
                $_SESSION['user_id'] = $result['user_id'];
                echo json_encode([
                    'success' => true,
                    'message' => 'Registration successful',
                    'user_id' => $result['user_id']
                ]);
            } else {
                throw new Exception($result['message']);
            }
            
        } elseif ($action === 'login') {
            if (!isset($data['email']) || !isset($data['password'])) {
                throw new Exception('Email and password are required');
            }
            
            $result = $user->login($data['email'], $data['password']);
            
            if ($result['success']) {
                $_SESSION['user_id'] = $result['user']['id'];
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => $result['user']
                ]);
            } else {
                throw new Exception($result['message']);
            }
        }
        
    } elseif ($method === 'GET') {
        if ($action === 'logout') {
            session_destroy();
            echo json_encode([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
            
        } elseif ($action === 'check_session') {
            if (isset($_SESSION['user_id'])) {
                $current_user = $user->getUserById($_SESSION['user_id']);
                echo json_encode([
                    'success' => true,
                    'logged_in' => true,
                    'user' => $current_user
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'logged_in' => false
                ]);
            }
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
