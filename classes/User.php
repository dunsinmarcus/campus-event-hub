<?php
/**
 * Campus Event Hub - User Model
 */

require_once __DIR__ . '/Database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Register a new user
     */
    public function register($student_id, $name, $email, $password, $phone = null, $department = null) {
        // Check if user already exists
        if ($this->getUserByEmail($email)) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        if ($this->getUserByStudentId($student_id)) {
            return ['success' => false, 'message' => 'Student ID already registered'];
        }
        
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        $data = [
            'student_id' => $student_id,
            'name' => $name,
            'email' => $email,
            'password' => $hashed_password,
            'phone' => $phone,
            'department' => $department
        ];
        
        $user_id = $this->db->insert('users', $data);
        return ['success' => true, 'user_id' => $user_id, 'message' => 'Registration successful'];
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        $user = $this->getUserByEmail($email);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Remove password from user data
        unset($user['password']);
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($user_id) {
        $sql = "SELECT id, student_id, name, email, phone, department, created_at FROM users WHERE id = ?";
        return $this->db->getRow($sql, 'i', [$user_id]);
    }
    
    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        return $this->db->getRow($sql, 's', [$email]);
    }
    
    /**
     * Get user by student ID
     */
    public function getUserByStudentId($student_id) {
        $sql = "SELECT id, student_id, name, email, phone, department FROM users WHERE student_id = ?";
        return $this->db->getRow($sql, 's', [$student_id]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($user_id, $data) {
        $update_data = array_filter([
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'department' => $data['department'] ?? null
        ], fn($v) => $v !== null);
        
        if (empty($update_data)) {
            return 0;
        }
        
        return $this->db->update('users', $update_data, 'id = ?', 'i', [$user_id]);
    }
    
    /**
     * Change password
     */
    public function changePassword($user_id, $old_password, $new_password) {
        $user = $this->getUserById($user_id);
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        // Get full user record with password
        $sql = "SELECT * FROM users WHERE id = ?";
        $full_user = $this->db->getRow($sql, 'i', [$user_id]);
        
        if (!password_verify($old_password, $full_user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $this->db->update('users', ['password' => $hashed_password], 'id = ?', 'i', [$user_id]);
        
        return ['success' => true, 'message' => 'Password changed successfully'];
    }
    
    /**
     * Get all users (for admin)
     */
    public function getAllUsers($limit = 50, $offset = 0) {
        $sql = "SELECT id, student_id, name, email, phone, department, created_at 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        return $this->db->getRows($sql, 'ii', [$limit, $offset]);
    }
}
?>
