<?php

require_once 'BaseModel.php';

class UserModel extends BaseModel
{

    public function findUserById($id)
    {
        $sql = 'SELECT * FROM users WHERE id = ?';
        $user = $this->selectPrepared($sql, 'i', [$id]);

        return $user;
    }

    public function findUser($keyword)
    {
        $sql = 'SELECT * FROM users WHERE user_name LIKE ? OR user_email LIKE ?';
        $kw = "%{$keyword}%";
        $user = $this->selectPrepared($sql, 'ss', [$kw, $kw]);

        return $user;
    }

    /**
     * Authentication user
     * @param $userName
     * @param $password
     * @return array
     */
    public function auth($userName, $password)
    {
        // Lấy user theo tên (LIMIT 1)
        $sql = 'SELECT * FROM users WHERE name = ? LIMIT 1';
        $rows = $this->selectPrepared($sql, 's', [$userName]);

        if (empty($rows)) {
            return [];
        }

        $user = $rows[0];

        // Nếu password được lưu theo password_hash()
        if (isset($user['password']) && password_verify($password, $user['password'])) {
            return [$user];
        }

        // Nếu DB đang dùng MD5 (legacy) -> migrate on login
        if (isset($user['password']) && strlen($user['password']) === 32 && md5($password) === $user['password']) {
            // Cập nhật lại password thành password_hash
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $this->updateUserPassword($user['id'], $newHash);

            $user['password'] = $newHash;
            return [$user];
        }

        return [];
    }

    /**
     * Delete user by id
     * @param $id
     * @return mixed
     */
    public function deleteUserById($id)
    {
        $sql = 'DELETE FROM users WHERE id = ?';
        return $this->executePrepared($sql, 'i', [$id]);
    }

    /**
     * Update user
     * @param $input
     * @return mixed
     */
    public function updateUser($input)
    {
        $sqlParts = [];
        $types = '';
        $params = [];

        if (isset($input['name'])) {
            $sqlParts[] = 'name = ?';
            $types .= 's';
            $params[] = $input['name'];
        }

        if (!empty($input['password'])) {
            $sqlParts[] = 'password = ?';
            $types .= 's';
            $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
        }

        if (empty($sqlParts)) {
            return false;
        }

        // Thêm id vào cuối params
        $types .= 'i';
        $params[] = $input['id'];

        $sql = 'UPDATE users SET ' . implode(', ', $sqlParts) . ' WHERE id = ?';

        $user = $this->executePrepared($sql, $types, $params);

        return $user;
    }

    /**
     * Insert user
     * @param $input
     * @return mixed
     */
    public function insertUser($input)
    {
        $sql = 'INSERT INTO users (name, password) VALUES (?, ?)';
        $hash = password_hash($input['password'], PASSWORD_DEFAULT);
        $user = $this->insertPrepared($sql, 'ss', [$input['name'], $hash]);

        return $user;
    }

    /**
     * Search users
     * @param array $params
     * @return array
     */
    public function getUsers($params = [])
    {
        //Keyword
        if (!empty($params['keyword'])) {
            $sql = 'SELECT * FROM users WHERE name LIKE ?';
            $kw = "%{$params['keyword']}%";
            $users = $this->selectPrepared($sql, 's', [$kw]);
        } else {
            $sql = 'SELECT * FROM users';
            $users = $this->selectPrepared($sql);
        }

        return $users;
    }

    /**
     * Update user's password directly (helper)
     * @param $id
     * @param $newHash
     * @return mixed
     */
    protected function updateUserPassword($id, $newHash)
    {
        $sql = 'UPDATE users SET password = ? WHERE id = ?';
        return $this->executePrepared($sql, 'si', [$newHash, $id]);
    }
}