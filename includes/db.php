<?php
/**
 * Database Connection File
 * ملف الاتصال بقاعدة البيانات
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'online_bookstore');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Check which database extension is available
$use_mysqli = extension_loaded('mysqli');
$use_pdo = extension_loaded('pdo_mysql');

/**
 * Get Database Connection
 * @return mysqli|PDO
 */
function getDBConnection() {
    global $use_mysqli, $use_pdo;
    static $conn = null;
    
    if ($conn === null) {
        if ($use_mysqli) {
            // Use MySQLi
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                die("خطأ في الاتصال بقاعدة البيانات: " . $conn->connect_error);
            }
            
            $conn->set_charset(DB_CHARSET);
        } elseif ($use_pdo) {
            // Use PDO
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
            }
        } else {
            die("خطأ: لم يتم العثور على درايفر MySQL. يرجى تثبيت mysqli أو PDO MySQL.");
        }
    }
    
    return $conn;
}

/**
 * Execute a query and return results
 * @param string $sql
 * @param array $params
 * @return array
 */
function dbQuery($sql, $params = []) {
    global $use_mysqli;
    $conn = getDBConnection();
    
    if ($use_mysqli) {
        // MySQLi implementation
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("خطأ في تحضير الاستعلام: " . $conn->error);
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    } else {
        // PDO implementation
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

/**
 * Execute a query and return single row
 * @param string $sql
 * @param array $params
 * @return array|null
 */
function dbQuerySingle($sql, $params = []) {
    global $use_mysqli;
    $conn = getDBConnection();
    
    if ($use_mysqli) {
        // MySQLi implementation
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("خطأ في تحضير الاستعلام: " . $conn->error);
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    } else {
        // PDO implementation
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
}

/**
 * Execute an insert/update/delete query
 * @param string $sql
 * @param array $params
 * @return int Number of affected rows
 */
function dbExecute($sql, $params = []) {
    global $use_mysqli;
    $conn = getDBConnection();
    
    if ($use_mysqli) {
        // MySQLi implementation
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("خطأ في تحضير الاستعلام: " . $conn->error);
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    } else {
        // PDO implementation
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}

/**
 * Get last inserted ID
 * @return string
 */
function dbLastInsertId() {
    global $use_mysqli;
    $conn = getDBConnection();
    
    if ($use_mysqli) {
        return $conn->insert_id;
    } else {
        return $conn->lastInsertId();
    }
}

/**
 * Call a stored procedure
 * @param string $procedure
 * @param array $params
 * @return array
 */
function callProcedure($procedure, $params = []) {
    $placeholders = implode(',', array_fill(0, count($params), '?'));
    $sql = "CALL {$procedure}({$placeholders})";
    return dbQuery($sql, $params);
}
?>
