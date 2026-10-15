<?php

namespace App\Service;

use App\Entity\Deliveries;
use App\Entity\InventoryMovements;
use App\Entity\PurchaseOrders;
use App\Entity\Users;
use App\Repository\MaterialsRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Réception d'une livraison : met à jour le stock automatiquement.
 *
 * Pour chaque ligne de la commande liée dont la quantité reçue est
 * inférieure à la quantité commandée :
 * - la ligne passe en "tout reçu" ;
 * - si un matériel porte la même référence, son stock augmente
 *   et un mouvement d'entrée est créé dans l'historique ;
 * - la commande passe au statut "Reçue".
 *
 * Une commande déjà reçue n'est jamais traitée deux fois.
 * Le flush reste à la charge du contrôleur.
 */
class ReceptionManager
{
    public function __construct(
        private readonly MaterialsRepository $materialsRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return string[] messages décrivant ce qui a été fait (à afficher en flash)
     */
    public function receive(Deliveries $delivery, ?Users $performedBy): array
    {
        $order = $delivery->getPurchaseOrder();
        if (null === $order) {
            return [];
        }
        if (PurchaseOrders::STATUS_RECEIVED === $order->getStatus()) {
            return ['Commande déjà réceptionnée : stock non modifié.'];
        }

        $messages = [];
        foreach ($order->getItems() as $item) {
            $remaining = (int) $item->getQuantity() - (int) $item->getReceivedQuantity();
            if ($remaining <= 0) {
                continue;
            }
            $item->setReceivedQuantity((int) $item->getQuantity());

            $material = $item->getMaterialReference()
                ? $this->materialsRepository->findOneBy(['reference' => $item->getMaterialReference()])
                : null;

            if (null === $material) {
                $messages[] = sprintf('"%s" : aucun matériel avec cette référence, stock non modifié.', $item->getMaterialName());
                continue;
            }

            $material->setQuantity((int) $material->getQuantity() + $remaining);
            $material->setUpdatedAt(new \DateTime());

            $movement = (new InventoryMovements())
                ->setMaterial($material)
                ->setMovementType(InventoryMovements::TYPE_IN)
                ->setQuantity($remaining)
                ->setToLocation($material->getLocation())
                ->setPerformedBy($performedBy)
                ->setPerformedAt($delivery->getDeliveryDate() ?? new \DateTime())
                ->setReason('Réception commande '.$order->getOrderNumber());
            $this->entityManager->persist($movement);

            $messages[] = sprintf('+%d × %s (référence %s)', $remaining, $material->getName(), $material->getReference());
        }

        $order->setStatus(PurchaseOrders::STATUS_RECEIVED);

        return $messages;
    }
}
