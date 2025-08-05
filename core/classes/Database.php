<?php
/**
 * Database Connection Singleton Class
 * Handles database connections for the CSU CMS Platform
 */

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            // Use defined constants if available, otherwise fallback to defaults
            $host = defined('DB_HOST') ? DB_HOST : 'localhost';
            $database = defined('DB_NAME') ? DB_NAME : 'csu_cms_platform';
            $username = defined('DB_USER') ? DB_USER : 'root';
            $password = defined('DB_PASS') ? DB_PASS : '';
            $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
            
            $this->connection = new PDO(
                "mysql:host={$host};dbname={$database};charset={$charset}",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        // Check if connection is still alive
        try {
            $this->connection->query('SELECT 1');
        } catch (PDOException $e) {
            // Reconnect if connection is lost
            $this->__construct();
        }
        return $this->connection;
    }

    /**
     * Check if database connection is active
     */
    public function isConnected() {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            throw $e;
        }
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        try {
            // Check if connection is still active and in transaction
            if ($this->isConnected() && $this->connection->inTransaction()) {
                return $this->connection->rollback();
            }
        } catch (PDOException $e) {
            // Log the error but don't throw it
            error_log('Database rollback failed: ' . $e->getMessage());
            return false;
        }
        return true;
    }
    
    /**
     * Get records filtered by current campus
     */
    public function getCampusRecords($table, $conditions = [], $orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$table} WHERE campus_id = :campus_id";
        $params = ['campus_id' => CAMPUS_ID];
        
        if (!empty($conditions)) {
            foreach ($conditions as $field => $value) {
                $sql .= " AND {$field} = :{$field}";
                $params[$field] = $value;
            }
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->query($sql, $params)->fetchAll();
    }
    
    /**
     * Insert record with campus_id
     */
    public function insertCampusRecord($table, $data) {
        $data['campus_id'] = CAMPUS_ID;
        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        
        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") VALUES ({$placeholders})";
        
        return $this->query($sql, $data);
    }
    
    /**
     * Update record for current campus
     */
    public function updateCampusRecord($table, $data, $id) {
        $setClause = [];
        foreach (array_keys($data) as $field) {
            $setClause[] = "{$field} = :{$field}";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $setClause) . " WHERE id = :id AND campus_id = :campus_id";
        $data['id'] = $id;
        $data['campus_id'] = CAMPUS_ID;
        
        return $this->query($sql, $data);
    }
    
    /**
     * Delete record for current campus
     */
    public function deleteCampusRecord($table, $id) {
        $sql = "DELETE FROM {$table} WHERE id = :id AND campus_id = :campus_id";
        return $this->query($sql, ['id' => $id, 'campus_id' => CAMPUS_ID]);
    }
}
?>
