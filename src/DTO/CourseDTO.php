<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class CourseDTO
{
    #[Serializer\Type("string")]
    public string $character_code;

    #[Serializer\Type("smallint")]
    public string $type;

    #[Serializer\Type("float")]
    public string $price;
}