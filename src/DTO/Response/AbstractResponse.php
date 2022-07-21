<?php
namespace App\DTO\Response;

abstract class AbstractResponse implements ResponseInterface
{
    public function transformFromObjects(iterable $objects): iterable
    {
        $dto = [];

        foreach ($objects as $object) {
            $dto[] = $this->transformFromObject($object);
        }
        return $dto;
    }

}