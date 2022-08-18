<?php

declare(strict_types=1);

namespace App\Application\DAO;

use App\Application\Helpers\Connection;
use App\Application\Helpers\Util;
use App\Application\Model\User;

class UserDAO
{
    private const TABLE_NAME = 'user';

    /**
     * @var Connection $connection
     */
    private Connection $connection;

    public function __construct()
    {
        $this->connection = new Connection();
    }

    /**
     * @param array $data
     * @return User|null
     */
    public function create(array $data): User|null
    {
        $data["hash"] = password_hash($data["password"], PASSWORD_DEFAULT);
        unset($data["password"]);
        $query = Util::prepareInsertQuery($data, self::TABLE_NAME);
        return ($this->connection->insert($query)) ? $this->getUserById($this->connection->getLastId()) : null;
    }

    /**
     * @param int $id
     * @return User
     */
    public function getUserById(int $id): User
    {
        $user = $this->connection
            ->select("SELECT * FROM user WHERE id = $id")
            ->fetch_object('App\Application\Model\User');
        unset($user->hash);
        return $user;
    }

    public function getSiguienteId(): int
    {
        $row = $this->connection->select("SELECT AUTO_INCREMENT FROM "
            . "INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'user'")->fetch_array();
        return $row[0];
    }

    /**
     * @param int $id
     * @param array $data
     * @return User|null
     */
    public function edit(int $id, array $data): User|null
    {
        $query = Util::prepareUpdateQuery($id, $data, self::TABLE_NAME);
        return ($this->connection->update($query)) ? $this->getUserById($id) : null;
    }

    /**
     * @param int $id
     * @return User|null
     */
    public function delete(int $id): User|null
    {
        $now = date('Y-m-d H:i:s');
        $data = [
            "enabled" => 0,
            "deleted_at" => $now
        ];
        $query = Util::prepareUpdateQuery($id, $data, self::TABLE_NAME);
        return ($this->connection->update($query)) ? $this->getUserById($id) : null;
    }

    /**
     * @param int $branchId
     * @return User[]
     */
    public function getCashiers(int $branchId): array
    {
        $cashiers = [];
        $query = <<<EOF
            SELECT id
            FROM user
            WHERE branch_id = $branchId
                AND root = 0
                AND enabled = 1
        EOF;

        $result = $this->connection->select($query);
        while ($row = $result->fetch_array()) {
            $cashiers[] = $this->getUserById(intval($row['id']));
        }

        return $cashiers;
    }

    /**
     * @param string $email
     * @param string $password
     * @return array|null
     */
    public function validateSession(string $email, string $password): array|null
    {
        $query = <<<EOF
            SELECT id, branch_id, hash, root
            FROM user
            WHERE email LIKE '$email' 
                AND email = '$email' 
                AND enabled = 1
        EOF;

        $result = $this->connection->select($query);

        if ($result->num_rows == 1) {
            $data = $result->fetch_assoc();
            if (password_verify($password, $data['hash'])) {
                unset($data['hash']);
                return $data;
            }
            return null;
        }
        return null;
    }

    /**
     * @param int $userId
     * @param string $password
     * @return User|null
     */
    public function resetPassword(int $userId, string $password): User|null
    {
        $data = [];
        $data["hash"] = password_hash($password, PASSWORD_DEFAULT);
        return $this->edit($userId, $data);
    }

    /**
     * @param string $email
     * @return bool
     */
    public function existEmail(string $email): bool
    {
        $row = $this->connection->select("SELECT email FROM user WHERE email = '$email'")->fetch_assoc();
        return (isset($row) && Util::validateEmail($row['email']));
    }
}
