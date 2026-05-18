<?php
/**
 * Campus Event Hub - Database Helper Class
 */

class Database {
    private $conn;
    
    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Execute a prepared statement query
     */
    public function query($sql, $types = '', $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        if ($types && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        return $stmt;
    }
    
    /**
     * Get a single row
     */
    public function getRow($sql, $types = '', $params = []) {
        $stmt = $this->query($sql, $types, $params);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Get multiple rows
     */
    public function getRows($sql, $types = '', $params = []) {
        $stmt = $this->query($sql, $types, $params);
        $result = $stmt->get_result();
        $rows = [];
        
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        
        return $rows;
    }
    
    /**
     * Insert data
     */
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $types = '';
        $values = [];
        
        foreach ($data as $value) {
            $values[] = $value;
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        
        $this->query($sql, $types, $values);
        return $this->conn->insert_id;
    }
    
    /**
     * Update data
     */
    public function update($table, $data, $where, $where_types = '', $where_params = []) {
        $set = [];
        $types = '';
        $values = [];
        
        foreach ($data as $column => $value) {
            $set[] = "$column = ?";
            $values[] = $value;
            
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        
        $set_string = implode(',', $set);
        $sql = "UPDATE $table SET $set_string WHERE $where";
        
        $types .= $where_types;
        $values = array_merge($values, $where_params);
        
        $this->query($sql, $types, $values);
        return $this->conn->affected_rows;
    }
    
    /**
     * Delete data
     */
    public function delete($table, $where, $types = '', $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        $this->query($sql, $types, $params);
        return $this->conn->affected_rows;
    }
    
    /**
     * Execute raw query (be careful with this)
     */
    public function rawQuery($sql) {
        return $this->conn->query($sql);
    }
    
    /**
     * Close connection
     */
    public function close() {
        $this->conn->close();
    }
}
?>
