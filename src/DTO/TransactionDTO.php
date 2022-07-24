<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;

class TransactionDTO
{
    #[Serializer\Type("int")]
    public int $id;

    #[Serializer\Type("string")]
    public string $code;

    #[Serializer\Type("smallint")]
    public string $type;

    #[Serializer\Type("float")]
    public float $value;

    #[Serializer\Type("string")]
    public string $created_at;

    #[Serializer\Type("string")]
    public string $expires_at;
}