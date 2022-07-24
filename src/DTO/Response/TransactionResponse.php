<?php

namespace App\DTO\Response;

use App\DTO\TransactionDTO;
use App\Entity\Transaction;

class TransactionResponse extends AbstractResponse
{
    public function transformFromObject( $object): TransactionDTO
    {
        $dto = new TransactionDTO();
        $dto->id = $object->getId();
        $dto->created_at = $object->getDate()->format('Y-m-d T H:i:s');
        $dto->type = $object->getType();
        $dto->value = $object->getCount();
        $dto->code = $object->getCourse()->getCharacterCode();
        if ($object->getValidityPeriod()) {
            $dto->expires_at = $object->getValidityPeriod()->format('Y-m-d H:i');
        }
        return $dto;
    }
}