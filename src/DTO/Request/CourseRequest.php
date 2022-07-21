<?php

namespace App\DTO\Request;

use App\DTO\CourseDTO;
use App\DTO\UserDTO;
use App\Entity\Course;
use App\Entity\User;

class CourseRequest
{
    public function transformToObject(CourseDTO $courseDTO): Course
    {
        $course = new Course();
        $course->setCharacterCode($courseDTO->character_code);
        $course->setType($courseDTO->type);
        $course->setPrice($courseDTO->price);
        return $course;
    }
}