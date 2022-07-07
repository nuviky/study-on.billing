<?php

namespace App\DTO\Request;

use App\DTO\UserDTO;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegisterRequest
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function transformToObject(UserDTO $userDto): User
    {
        $user = new User();
        $user->setEmail($userDto->username);

        $plainPassword = $userDto->password;
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plainPassword
        );
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);
        $user->setBalance(0);

        return $user;
    }
}