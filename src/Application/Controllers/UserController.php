<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Application\Controllers\BranchController;
use App\Application\Helpers\EmailTemplate;
use App\Application\DAO\UserDAO;
use App\Application\Model\User;
use App\Application\Helpers\Util;
use Exception;

class UserController
{
    /**
     * @var UserDAO $userDAO
     */
    private UserDAO $userDAO;

    public function __construct()
    {
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
            $branchController = new BranchController();
            $branch = $branchController->getById(intval($data['branch_id']));
            $dataToSendEmail = [
                'subject' => "Bienvenido a $branch->name",
                'email' => $user->email,
                'branch_name' => $branch->name,
                'branch_location' => $branch->location,
                'password' => $password,
                'username' => "$user->name $user->last_name"
            ];
            if (!Util::sendMail($dataToSendEmail, EmailTemplate::PASSWORD_TO_NEW_USER)) {
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
     * @param int $userId
     * @return bool
     * @throws Exception
     */
    public function resetPassword(int $userId): bool
    {
        $password = Util::generatePassword();
        $user = $this->userDAO->resetPassword($userId, $password);
        if ($user) {
            $branchController = new BranchController();
            $branch = $branchController->getById(intval($user->branch_id));
            $dataToSendEmail = [
                'subject' => "Restablecer contraseña - $branch->name",
                'email' => $user->email,
                'branch_name' => $branch->name,
                'password' => $password,
                'user_name' => "$user->name"
            ];
            if (!Util::sendMail($dataToSendEmail, EmailTemplate::RESET_PASSWORD)) {
                throw new Exception('Error to send password to new user.');
            }
            return true;
        }
        return false;
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