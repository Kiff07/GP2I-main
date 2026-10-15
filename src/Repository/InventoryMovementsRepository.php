<?php

namespace App\Repository;

use App\Entity\InventoryMovements;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<InventoryMovements> */
class InventoryMovementsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryMovements::class);
    }

    /**
     * Recherche (nom du matériel) et filtre par type, paginés.
     *
     * @return array{items: InventoryMovements[], total: int, page: int, pages: int}
     */
    public function searchPaginated(?string $q, ?string $type, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));

        $qb = $this->createQueryBuilder('mv')
            ->leftJoin('mv.material', 'm')
            ->orderBy('mv.id', 'DESC');

        $q = null !== $q ? trim($q) : null;
        if (null !== $q && '' !== $q) {
            $qb->andWhere('LOWER(m.name) LIKE LOWER(:q) OR LOWER(m.reference) LIKE LOWER(:q)')
                ->setParameter('q', '%'.addcslashes($q, '%_\\').'%');
        }

        if (null !== $type && \in_array($type, InventoryMovements::typeChoices(), true)) {
            $qb->andWhere('mv.movement_type = :type')->setParameter('type', $type);
        }

        $query = $qb->setFirstResult(($page - 1) * $perPage)->setMaxResults($perPage)->getQuery();
        $paginator = new Paginator($query);
        $total = \count($paginator);

        return [
            'items' => iterator_to_array($paginator),
            'total' => $total,
            'page' => $page,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /**
     * Nombre de mouvements par mois, pour le graphique du tableau de bord.
     *
     * @return array<int, array{label: string, total: int}> du plus ancien au plus récent
     */
    public function countByMonth(int $months = 6): array
    {
        $since = new \DateTime(sprintf('-%d months', max(1, $months) - 1));
        $since->modify('first day of this month')->setTime(0, 0);

        // Agrégation en PHP : simple et portable (volumes modestes).
        $dates = $this->createQueryBuilder('mv')
            ->select('mv.performed_at')
            ->andWhere('mv.performed_at >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleColumnResult();

        $byKey = [];
        foreach ($dates as $date) {
            $key = $date->format('Y-m');
            $byKey[$key] = ($byKey[$key] ?? 0) + 1;
        }

        $stats = [];
        $cursor = clone $since;
        for ($i = 0; $i < $months; ++$i) {
            $key = $cursor->format('Y-m');
            $stats[] = ['label' => $cursor->format('m/Y'), 'total' => $byKey[$key] ?? 0];
            $cursor->modify('+1 month');
        }

        return $stats;
    }
}
