<?php

namespace App\DTO\Response;

use App\DTO\UserDTO;
use App\Entity\User;

class CurrentUserResponse
{
    public function transformFromObject(User $user): UserDTO
    {
        $user_dto = new UserDTO();
        $user_dto->username = $user->getEmail();
        $user_dto->balance = $user->getBalance();
        $user_dto->roles = $user->getRoles();
        return $user_dto;
    }
}