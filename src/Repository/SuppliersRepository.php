<?php

namespace App\Repository;

use App\Entity\Suppliers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Suppliers>
 */
class SuppliersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Suppliers::class);
    }

    /**
     * Recherche par nom, contact ou email (parametre GET "q" de la liste).
     *
     * @return Suppliers[]
     */
    public function search(?string $q): array
    {
        $qb = $this->createQueryBuilder('s')->orderBy('s.name', 'ASC');

        $q = null !== $q ? trim($q) : null;
        if (null !== $q && '' !== $q) {
            // NB : en DQL on utilise le nom de la propriete PHP (contact_name),
            // pas le nom du getter (getContactName).
            $qb->andWhere('LOWER(s.name) LIKE LOWER(:q) OR LOWER(s.contact_name) LIKE LOWER(:q) OR LOWER(s.email) LIKE LOWER(:q)')
                ->setParameter('q', '%'.addcslashes($q, '%_\\').'%');
        }

        return $qb->getQuery()->getResult();
    }
}
