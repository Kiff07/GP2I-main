<?php

namespace App\Service;

use App\Entity\Archives;
use App\Entity\Categories;
use App\Entity\Materials;
use App\Entity\Suppliers;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Journal des suppressions : alimente la table "archives" avec
 * quoi a ete supprime, quand, et par qui.
 *
 * Appele explicitement par les controleurs avant la suppression
 * (un evenement Doctrine onRemove n'existe plus sous cette forme
 * avec Doctrine ORM 3).
 */
class ArchiveService
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function logDeletion(Materials|Categories|Suppliers $entity, EntityManagerInterface $em): void
    {
        $user = $this->security->getUser();

        $archive = (new Archives())
            ->setEntity($entity::class)
            ->setEntityId((int) $entity->getId())
            ->setAction('delete')
            ->setData($this->snapshot($entity))
            ->setCreatedBy($user instanceof Users ? $user : null);

        $em->persist($archive);
    }

    private function snapshot(Materials|Categories|Suppliers $entity): array
    {
        return match (true) {
            $entity instanceof Materials => [
                'name' => $entity->getName(),
                'reference' => $entity->getReference(),
                'status' => $entity->getStatus(),
                'quantity' => $entity->getQuantity(),
                'location' => $entity->getLocation(),
            ],
            $entity instanceof Categories => [
                'name' => $entity->getName(),
                'description' => $entity->getDescription(),
            ],
            $entity instanceof Suppliers => [
                'name' => $entity->getName(),
                'email' => $entity->getEmail(),
            ],
        };
    }
}
