<?php
namespace App\DTO\Response;

use App\DTO\CourseDTO;
use App\DTO\Response\AbstractResponse;
use App\Entity\Course;

class CourseResponse extends AbstractResponse
{
    public function transformFromObject($object): CourseDTO
    {
        $dto = new CourseDto();
        $dto->character_code = $object->getCharacterCode();
        $dto->type = $object->getType();
        if ($dto->type != 0){
            $dto->price = $object->getPrice();
        }
        return $dto;
    }
}