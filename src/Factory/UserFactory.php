<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;

class UserFactory
{
    public function create(string $email, string $plainPassword): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($plainPassword);
        return $user;
    }
}