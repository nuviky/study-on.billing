<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PaymentService
{
    public function deposit(User $user, float $amount, EntityManagerInterface $entityManager) {
        $entityManager->getConnection()->beginTransaction();
        try {
            $transaction = new Transaction();
            $transaction->setDate(new \DateTime());
            $transaction->setType(0);
            $transaction->setCount($amount);
            $transaction->setUser($user);

            $user->setBalance($user->getBalance() + $amount);
            $entityManager->persist($user);
            $entityManager->persist($transaction);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return true;
        } catch (\Exception $exception){
            $entityManager->getConnection()->rollBack();
            return false;
        }
    }

    public function payment(Course $course, User $user, EntityManagerInterface $entityManager) {
        $entityManager->getConnection()->beginTransaction();
        try {
            if ($user->getBalance() >= $course->getPrice()) {
                $transaction = new Transaction();
                $transaction->setDate(new \DateTime());
                $transaction->setCourse($course);
                $transaction->setUser($user);
                $transaction->setType(1);

                if ($course->getType() != 0) {
                    $transaction->setCount($course->getPrice());
                    if ($course->getType() == 2) {
                        $transaction->setValidityPeriod((new \DateTime())->modify('+1 week'));
                    }
                    $entityManager->persist($transaction);
                    $entityManager->flush();

                    if (!is_null($course->getPrice())){
                        $user->setBalance($user->getBalance() - $course->getPrice());
                    }
                    $entityManager->persist($user);
                    $entityManager->flush();
                    $entityManager->getConnection()->commit();
                    return true;
                }
            } else {
                $entityManager->rollback();
                return false;
            }
        } catch (\Exception $exception) {
            $entityManager->rollback();
            return false;
        }
    }
}