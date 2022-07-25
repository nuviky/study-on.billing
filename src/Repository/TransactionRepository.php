<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function add(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findEndingTransaction($userId): array
    {
        $connect = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT c.character_code, t.validity_period 
            FROM transaction t
            INNER JOIN course c ON c.id = t.course_id
            WHERE t.type = 0 
            AND t.user__id = :user_id 
            AND t.validity_period::date = (now()::date + '1 day'::interval)
            ORDER BY t.date DESC
            ";
        $query = $connect->prepare($sql);
        $query = $query->executeQuery([
            'user_id' => $userId,
        ]);
        return $query->fetchAllAssociative();
    }

    public function findLastMonthTransaction()
    {
        $connect = $this->getEntityManager()->getConnection();
        $sql = "
            SELECT COUNT(t.id), SUM(t.count), c.character_code, c.type 
            FROM transaction t
            INNER JOIN course c ON c.id = t.course_id
            AND t.date::date between (now()::date - '1 month'::interval) AND now()::date
            AND t.type = 0
            GROUP BY c.character_code, c.type
            ";
        $query = $connect->prepare($sql);
        $query = $query->executeQuery();
        return $query->fetchAllAssociative();
    }

//    /**
//     * @return Transaction[] Returns an array of Transaction objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Transaction
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
