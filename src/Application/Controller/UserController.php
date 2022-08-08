<?php

declare(strict_types=1);

namespace App\Application\Controller;

use App\Application\DAO\UserDAO;
use App\Application\Model\User;

class UserController
{
    /**
     * @var UserDAO $userDAO
     */
    private UserDAO $userDAO;

    function __construct() {
        $this->userDAO = new UserDAO();
    }

    /**
     * @param array $data
     * @return User|null
     */
    public function create(array $data): User|null
    {
        return $this->userDAO->create($data);
    }

    /**
     * @param int $id
     * @return User
     */
    public function getUserById(int $id): User
    {
        return $this->userDAO->getUserById($id);
    }

    /**
     * @param int $id
     * @param array $data
     * @return User|null
     */
    public function edit(int $id, array $data): User|null
    {
        return $this->userDAO->edit($id, $data);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->userDAO->delete($id);
    }

    /**
     * @param int $branchId
     * @return User[]
     */
    public function getCashiers(int $branchId): array
    {
        return $this->userDAO->getCashiers($branchId);
    }

    /**
     * @param string $email
     * @param string $password
     * @return array|null
     */
    public function validateSession(string $email, string $password): array|null
    {
        return $this->userDAO->validateSession($email, $password);
    }

    /**
     * @param string $email
     * @return bool
     */
    public function existEmail(string $email): bool
    {
        return $this->userDAO->existEmail($email);
    }

    public function getSiguienteId() {
        return $this->userDAO->getSiguienteId();
    }
}