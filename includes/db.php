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

/**
 * Get PDO Database Connection
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

/**
 * Execute a query and return results
 * @param string $sql
 * @param array $params
 * @return array
 */
function dbQuery($sql, $params = []) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Execute a query and return single row
 * @param string $sql
 * @param array $params
 * @return array|null
 */
function dbQuerySingle($sql, $params = []) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

/**
 * Execute an insert/update/delete query
 * @param string $sql
 * @param array $params
 * @return int Number of affected rows
 */
function dbExecute($sql, $params = []) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Get last inserted ID
 * @return string
 */
function dbLastInsertId() {
    return getDBConnection()->lastInsertId();
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
