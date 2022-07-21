<?php

namespace App\DTO\Response;

interface ResponseInterface
{
    public function transformFromObject($object);
    public function transformFromObjects(iterable $objects): iterable;
}