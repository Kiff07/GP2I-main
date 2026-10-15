<?php

namespace App\Repository;

use App\Entity\Loans;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Loans> */
class LoansRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loans::class);
    }

    /**
     * Emprunts en retard (non retournés, date dépassée), les plus anciens d'abord.
     *
     * @return Loans[]
     */
    public function findOverdue(int $limit = 5): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.returned_at IS NULL')
            ->andWhere('l.due_date < :today')
            ->setParameter('today', new \DateTime('today'))
            ->orderBy('l.due_date', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countActive(): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.returned_at IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
