<?php

declare(strict_types=1);

namespace App\Application\Controller;

use App\Application\Model\User;
 
class UserController
{
    public static function create(string $username, string $email, string $password)
    {
        $user = User::create(['username' => $username, 'email' => $email, 'password' => $password]);
        return $user;
    }
}