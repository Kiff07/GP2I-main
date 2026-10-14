<?php

namespace App\Service;

use App\Entity\InventoryMovements;
use App\Entity\Materials;

/**
 * Applique un mouvement de stock au materiel concerne.
 *
 * AVANT : creer un mouvement n'avait AUCUN effet. La quantite, le lieu
 * et le statut du materiel ne bougeaient jamais, donc l'inventaire
 * divergeait de l'historique des mouvements des le premier jour.
 *
 * Regles :
 * - entree ("in")          : quantite += n, lieu = lieu de destination
 * - sortie ("out")         : quantite -= n (refusee si stock insuffisant)
 * - transfert ("transfer") : lieu = lieu de destination, quantite inchangee
 * - maintenance            : statut = "maintenance", quantite inchangee
 */
class StockManager
{
    /**
     * @throws \InvalidArgumentException si le mouvement est incomplet
     * @throws \DomainException          si le stock est insuffisant pour une sortie
     */
    public function apply(InventoryMovements $movement): void
    {
        $material = $movement->getMaterial();
        if (null === $material) {
            throw new \InvalidArgumentException('Le mouvement doit concerner un materiel.');
        }

        $quantity = (int) $movement->getQuantity();
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('La quantite du mouvement doit etre positive.');
        }

        match ($movement->getMovementType()) {
            InventoryMovements::TYPE_IN => $this->applyIn($material, $movement, $quantity),
            InventoryMovements::TYPE_OUT => $this->applyOut($material, $quantity),
            InventoryMovements::TYPE_TRANSFER => $this->applyTransfer($material, $movement),
            InventoryMovements::TYPE_MAINTENANCE => $material->setStatus(Materials::STATUS_MAINTENANCE),
            default => throw new \InvalidArgumentException('Type de mouvement inconnu : '.$movement->getMovementType()),
        };

        $material->setUpdatedAt(new \DateTime());
    }

    private function applyIn(Materials $material, InventoryMovements $movement, int $quantity): void
    {
        $material->setQuantity((int) $material->getQuantity() + $quantity);
        if ($movement->getToLocation()) {
            $material->setLocation($movement->getToLocation());
        }
        if (Materials::STATUS_DISPOSED === $material->getStatus()) {
            $material->setStatus(Materials::STATUS_AVAILABLE);
        }
    }

    private function applyOut(Materials $material, int $quantity): void
    {
        $current = (int) $material->getQuantity();
        if ($quantity > $current) {
            throw new \DomainException(sprintf(
                'Stock insuffisant pour "%s" : %d en stock, %d demandes.',
                $material->getName(),
                $current,
                $quantity
            ));
        }
        $material->setQuantity($current - $quantity);
    }

    private function applyTransfer(Materials $material, InventoryMovements $movement): void
    {
        if (!$movement->getToLocation()) {
            throw new \InvalidArgumentException('Un transfert exige un emplacement de destination.');
        }
        if (!$movement->getFromLocation()) {
            $movement->setFromLocation($material->getLocation());
        }
        $material->setLocation($movement->getToLocation());
    }
}
