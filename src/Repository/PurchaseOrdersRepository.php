<?php

namespace App\Repository;

use App\Entity\PurchaseOrders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PurchaseOrders> */
class PurchaseOrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseOrders::class);
    }

    /**
     * Recherche (numéro, fournisseur) et filtre par statut, paginés.
     *
     * @return array{items: PurchaseOrders[], total: int, page: int, pages: int}
     */
    public function searchPaginated(?string $q, ?string $status, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));

        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.supplier', 's')
            ->orderBy('o.id', 'DESC');

        $q = null !== $q ? trim($q) : null;
        if (null !== $q && '' !== $q) {
            $qb->andWhere('LOWER(o.order_number) LIKE LOWER(:q) OR LOWER(s.name) LIKE LOWER(:q)')
                ->setParameter('q', '%'.addcslashes($q, '%_\\').'%');
        }

        if (null !== $status && \in_array($status, PurchaseOrders::statusChoices(), true)) {
            $qb->andWhere('o.status = :status')->setParameter('status', $status);
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
}
