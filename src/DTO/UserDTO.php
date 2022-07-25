<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class UserDTO
{
    #[Serializer\Type("string")]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email.')]
    #[Assert\NotBlank(message: 'The username field can\'t be blank.')]
    public string $username;

    #[Serializer\Type("string")]
    #[Assert\NotBlank(message: 'The password field can\'t be blank.')]
    #[Assert\Length(min: 6, minMessage: 'The password must be at least {{ limit }} characters.')]
    public string $password;

    #[Serializer\Type("float")]
    public float $balance;

    #[Serializer\Type("array")]
    public array $roles;

    #[Serializer\Type("string")]
    public string $token;

    #[Serializer\Type("string")]
    public string $refresh_token;
}