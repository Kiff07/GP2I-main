<?php

namespace App\EventListener;

use App\Entity\Archives;
use App\Entity\Categories;
use App\Entity\Materials;
use App\Entity\Suppliers;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\RemoveEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Alimente automatiquement la table "archives" à chaque suppression
 * d'un matériel, d'une catégorie ou d'un fournisseur : on garde une
 * trace de quoi a été supprimé, quand, et par qui.
 */
#[AsDoctrineListener(event: Events::onRemove)]
class ArchiveListener
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function onRemove(RemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Materials && !$entity instanceof Categories && !$entity instanceof Suppliers) {
            return;
        }

        $user = $this->security->getUser();

        $archive = (new Archives())
            ->setEntity($entity::class)
            ->setEntityId((int) $entity->getId())
            ->setAction('delete')
            ->setData($this->snapshot($entity))
            ->setCreatedBy($user instanceof Users ? $user : null);

        $em = $args->getObjectManager();
        $em->persist($archive);
        // Nécessaire pour qu'une entité créée pendant un événement
        // de suppression soit bien prise en compte par le flush en cours.
        $em->getUnitOfWork()->computeChangeSet($em->getClassMetadata(Archives::class), $archive);
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
