<?php

namespace App\Repository;

use App\Entity\Categories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categories>
 */
class CategoriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categories::class);
    }

    /**
     * Recherche par nom ou description (parametre GET "q" de la liste).
     *
     * @return Categories[]
     */
    public function search(?string $q): array
    {
        $qb = $this->createQueryBuilder('c')->orderBy('c.name', 'ASC');

        $q = null !== $q ? trim($q) : null;
        if (null !== $q && '' !== $q) {
            $qb->andWhere('LOWER(c.name) LIKE LOWER(:q) OR LOWER(c.description) LIKE LOWER(:q)')
                ->setParameter('q', '%'.addcslashes($q, '%_\\').'%');
        }

        return $qb->getQuery()->getResult();
    }
}
