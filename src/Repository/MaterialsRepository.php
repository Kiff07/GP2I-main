<?php

namespace App\Repository;

use App\Entity\Materials;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Materials>
 */
class MaterialsRepository extends ServiceEntityRepository
{
    public const LOW_STOCK_THRESHOLD = 2;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Materials::class);
    }

    /**
     * Recherche et filtre les matériels.
     *
     * Corrections par rapport a la version initiale :
     * - les caractères speciaux LIKE (% et _) sont echappes, sinon une
     *   recherche "%" renvoyait tout le catalogue ;
     * - la recherche couvre aussi le nom de la catégorie et du fournisseur
     *   (jointures), pas seulement les colonnes du matériel ;
     * - un statut invalide passe dans l'URL est ignore au lieu de
     *   produire une liste vide incomprehensible ;
     * - recherche insensible a la casse via LOWER().
     *
     * @return Materials[]
     */
    public function search(?string $q, ?string $status, ?int $categoryId, ?int $supplierId): array
    {
        return $this->createSearchQueryBuilder($q, $status, $categoryId, $supplierId)
            ->orderBy('m.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Meme recherche, paginee (la version initiale chargeait TOUTE la table).
     *
     * @return array{items: Materials[], total: int, page: int, perPage: int, pages: int}
     */
    public function searchPaginated(?string $q, ?string $status, ?int $categoryId, ?int $supplierId, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));

        $query = $this->createSearchQueryBuilder($q, $status, $categoryId, $supplierId)
            ->orderBy('m.id', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery();

        $paginator = new Paginator($query);
        $total = \count($paginator);

        return [
            'items' => iterator_to_array($paginator),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /**
     * Nombre de matériels par statut, pour le tableau de bord.
     *
     * @return array<string, int> ex. ['available' => 12, 'borrowed' => 3]
     */
    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('m.status AS status, COUNT(m.id) AS total')
            ->groupBy('m.status')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(string) $row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * Matériels en stock faible (ou en rupture) a afficher en alerte.
     *
     * @return Materials[]
     */
    public function findLowStock(int $threshold = self::LOW_STOCK_THRESHOLD, int $limit = 5): array
    {
        // Le seuil peut etre global (parametre) ou propre a chaque
        // materiel : on compare a la colonne alert_threshold de la ligne.
        return $this->createQueryBuilder('m')
            ->andWhere('m.quantity <= m.alert_threshold')
            ->andWhere('m.status != :disposed')
            ->setParameter('disposed', Materials::STATUS_DISPOSED)
            ->orderBy('m.quantity', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    private function createSearchQueryBuilder(?string $q, ?string $status, ?int $categoryId, ?int $supplierId): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.category', 'c')
            ->leftJoin('m.supplier', 's');

        $q = null !== $q ? trim($q) : null;
        if (null !== $q && '' !== $q) {
            // Echapper % et _ pour qu'ils soient traites comme du texte.
            $term = '%'.addcslashes($q, '%_\\').'%';
            $qb->andWhere('LOWER(m.name) LIKE LOWER(:q) OR LOWER(m.reference) LIKE LOWER(:q) OR LOWER(m.serial_number) LIKE LOWER(:q) OR LOWER(m.location) LIKE LOWER(:q) OR LOWER(c.name) LIKE LOWER(:q) OR LOWER(s.name) LIKE LOWER(:q)')
                ->setParameter('q', $term);
        }

        // Ignorer un statut qui n'existe pas (URL modifiée a la main, etc.)
        if (null !== $status && \in_array($status, Materials::statusChoices(), true)) {
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

        return $qb;
    }
}
