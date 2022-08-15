<?php

declare(strict_types=1);

namespace App\Application\Controller;

use App\Application\DAO\UserDAO;
use App\Application\Model\User;
use App\Application\Helper\Util;
use Exception;

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
     * @throws Exception
     */
    public function create(array $data): User|null
    {
        $password = Util::generatePassword();
        $data['password'] = $password;
        $user = $this->userDAO->create($data);
        if ($user) {
            $branchController = new \App\Application\Controller\BranchController();
            $branch = $branchController->getById(intval($data['branch_id']));
            $dataToSendEmail = [
                'email', $user->email,
                'branchName' => $branch->name,
                'branchLocation' => $branch->location,
                'password' => $password,
                'userName' => "$user->name $user->last_name"
            ];
            if (!Util::sendPasswordToNewUser($dataToSendEmail)) {
                throw new Exception('Error to send password to new user.');
            }
        }
        return $user;
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
     * @return User|null
     */
    public function delete(int $id): User|null
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
        if (!Util::validateEmail($email)) {
            throw new Exception('Invalid email');
        }
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