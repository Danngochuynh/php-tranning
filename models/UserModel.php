<?php

require_once(__DIR__ . '/BaseModel.php');

class UserModel extends BaseModel {

    /**
     * Find user by id (safe prepared statement)
     * @param int $id
     * @return array|null
     */
    public function findUserById($id) {
        $id = (int)$id;
        $sql = 'SELECT * FROM users WHERE id = ? LIMIT 1';
        $stmt = self::$_connection->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $row;
    }

    /**
     * Find user by keyword (search in name or email) - safe
     * @param string $keyword
     * @return array
     */
    public function findUser($keyword) {
        $kw = '%' . $keyword . '%';
        $sql = 'SELECT * FROM users WHERE name LIKE ? OR email LIKE ?';
        $stmt = self::$_connection->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('ss', $kw, $kw);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Authentication user (NOTE: consider using password_hash in production)
     * @param string $userName
     * @param string $password
     * @return array
     */
    public function auth($userName, $password) {
        $md5Password = md5($password);
        $sql = 'SELECT * FROM users WHERE name = ? AND password = ? LIMIT 1';
        $stmt = self::$_connection->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('ss', $userName, $md5Password);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Delete user by id (safe)
     * @param int $id
     * @return bool
     */
    public function deleteUserById($id) {
        $id = (int)$id;
        $sql = 'DELETE FROM users WHERE id = ? LIMIT 1';
        $stmt = self::$_connection->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Update user (safe) - updates name, password (if provided) and deltail
     * @param array $input
     * @return bool
     */
    public function updateUser($input) {
        // Normalize inputs
        $id = isset($input['id']) ? (int)$input['id'] : 0;
        $name = $input['name'] ?? '';
        $deltail = $input['deltail'] ?? '';
        // If password provided and non-empty, update it; otherwise keep existing
        $password = $input['password'] ?? '';

        if ($id <= 0) return false;

        if ($password !== '') {
            $md5 = md5($password);
            $sql = 'UPDATE users SET name = ?, password = ?, deltail = ? WHERE id = ?';
            $stmt = self::$_connection->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param('sssi', $name, $md5, $deltail, $id);
        } else {
            $sql = 'UPDATE users SET name = ?, deltail = ? WHERE id = ?';
            $stmt = self::$_connection->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param('ssi', $name, $deltail, $id);
        }

        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Insert user (safe) - includes deltail
     * @param array $input
     * @return bool|int (insert id or false)
     */
    public function insertUser($input) {
        $name = $input['name'] ?? '';
        $password = $input['password'] ?? '';
        $deltail = $input['deltail'] ?? '';
        // default values for optional columns if not provided
        $fullname = $input['fullname'] ?? null;
        $email = $input['email'] ?? null;
        $type = $input['type'] ?? 'user';

        $md5 = md5($password);

        $sql = 'INSERT INTO users (name, fullname, email, type, password, deltail) VALUES (?,?,?,?,?,?)';
        $stmt = self::$_connection->prepare($sql);
        if (!$stmt) return false;
        // use nulls if needed: bind_param doesn't accept null types easily; pass empty strings or NULL via variables
        // We'll coerce null to empty string for fullname/email to avoid errors if columns are NOT NULL
        $fullname_param = $fullname ?? '';
        $email_param = $email ?? '';
        $type_param = $type ?? 'user';

        $stmt->bind_param('ssssss', $name, $fullname_param, $email_param, $type_param, $md5, $deltail);

        $ok = $stmt->execute();
        if (!$ok) {
            $stmt->close();
            return false;
        }
        $insert_id = self::$_connection->insert_id;
        $stmt->close();
        return $insert_id;
    }

    /**
     * Search users
     * @param array $params
     * @return array
     */
    public function getUsers($params = []) {
        if (!empty($params['keyword'])) {
            $kw = '%' . $params['keyword'] . '%';
            $sql = 'SELECT * FROM users WHERE name LIKE ?';
            $stmt = self::$_connection->prepare($sql);
            if (!$stmt) return [];
            $stmt->bind_param('s', $kw);
            $stmt->execute();
            $res = $stmt->get_result();
            $rows = $res->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $rows;
        } else {
            // fallback to safe select using BaseModel->select if available
            $sql = 'SELECT * FROM users';
            return $this->select($sql);
        }
    }
}
