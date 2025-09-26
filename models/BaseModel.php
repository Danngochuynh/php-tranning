<?php
require_once 'configs/database.php';

abstract class BaseModel
{
    // Database connection
    protected static $_connection;

    public function __construct()
    {

        if (!isset(self::$_connection)) {
            self::$_connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
            if (self::$_connection->connect_errno) {
                printf("Connect failed");
                exit();
            }
        }
    }

    /**
     * Query in database
     * @param $sql
     */
    protected function query($sql)
    {

        $result = self::$_connection->query($sql);
        return $result;
    }

    /**
     * Select statement
     * @param $sql
     */
    protected function select($sql)
    {
        $result = $this->query($sql);
        $rows = [];
        if (!empty($result)) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    /**
     * Delete statement
     * @param $sql
     * @return mixed
     */
    protected function delete($sql)
    {
        $result = $this->query($sql);
        return $result;
    }

    /**
     * Update statement
     * @param $sql
     * @return mixed
     */
    protected function update($sql)
    {
        $result = $this->query($sql);
        return $result;
    }

    /**
     * Insert statement
     * @param $sql
     */
    protected function insert($sql)
    {
        $result = $this->query($sql);
        return $result;
    }

    /**
     * SELECT prepared statement (safe)
     * @param string $sql
     * @param string $types
     * @param array $params
     * @return array
     */
    protected function selectPrepared($sql, $types = '', $params = [])
    {
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) return [];
        if ($types && $params) $this->bindParams($stmt, $types, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    /**
     * INSERT prepared statement
     * @param string $sql
     * @param string $types
     * @param array $params
     * @return mixed insert_id or false
     */
    protected function insertPrepared($sql, $types = '', $params = [])
    {
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) return false;
        if ($types && $params) $this->bindParams($stmt, $types, $params);
        $ok = $stmt->execute();
        if (!$ok) {
            $stmt->close();
            return false;
        }
        $insertId = self::$_connection->insert_id;
        $stmt->close();
        return $insertId;
    }

    /**
     * UPDATE/DELETE prepared statement
     * @param string $sql
     * @param string $types
     * @param array $params
     * @return int|false affected_rows
     */
    protected function executePrepared($sql, $types = '', $params = [])
    {
        $stmt = self::$_connection->prepare($sql);
        if ($stmt === false) return false;
        if ($types && $params) $this->bindParams($stmt, $types, $params);
        $ok = $stmt->execute();
        if (!$ok) {
            $stmt->close();
            return false;
        }
        $affected = self::$_connection->affected_rows;
        $stmt->close();
        return $affected;
    }

    /**
     * Bind parameters helper (for prepared statements)
     */
    protected function bindParams(&$stmt, $types, $params)
    {
        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }
}