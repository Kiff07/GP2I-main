<?php

namespace App\Repository;

use App\Entity\Materials;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Materials>
 */
class MaterialsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Materials::class);
    }

    /**
     * Recherche et filtre les matériels.
     *
     * @param string|null $q          Terme de recherche (nom, référence, série, localisation)
     * @param string|null $status     Filtre par statut
     * @param int|null    $categoryId Filtre par catégorie
     * @param int|null    $supplierId Filtre par fournisseur
     *
     * @return Materials[]
     */
    public function search(?string $q, ?string $status, ?int $categoryId, ?int $supplierId): array
    {
        $qb = $this->createQueryBuilder('m')
            ->orderBy('m.id', 'DESC');

        if ($q) {
            $qb->andWhere('m.name LIKE :q OR m.reference LIKE :q OR m.serial_number LIKE :q OR m.location LIKE :q')
                ->setParameter('q', '%'.$q.'%');
        }

        if ($status) {
            $qb->andWhere('m.status = :status')
                ->setParameter('status', $status);
        }

        if ($categoryId) {
            $qb->andWhere('m.category = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($supplierId) {
            $qb->andWhere('m.supplier = :supplierId')
                ->setParameter('supplierId', $supplierId);
        }

        return $qb->getQuery()->getResult();
    }
}
