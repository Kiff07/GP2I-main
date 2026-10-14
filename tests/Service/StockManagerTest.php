<?php

namespace App\Tests\Service;

use App\Entity\InventoryMovements;
use App\Entity\Materials;
use App\Service\StockManager;
use PHPUnit\Framework\TestCase;

/**
 * Test unitaire du StockManager : aucun besoin de base de donnees.
 * Lancer avec : php bin/phpunit tests/Service/StockManagerTest.php
 */
class StockManagerTest extends TestCase
{
    private function material(int $quantity): Materials
    {
        return (new Materials())
            ->setName('Laptop test')
            ->setReference('TEST-001')
            ->setQuantity($quantity)
            ->setStatus(Materials::STATUS_AVAILABLE)
            ->setLocation('Stock A');
    }

    private function movement(Materials $material, string $type, int $quantity): InventoryMovements
    {
        return (new InventoryMovements())
            ->setMaterial($material)
            ->setMovementType($type)
            ->setQuantity($quantity);
    }

    public function testEntryIncreasesStock(): void
    {
        $material = $this->material(10);
        (new StockManager())->apply($this->movement($material, InventoryMovements::TYPE_IN, 5));

        self::assertSame(15, $material->getQuantity());
    }

    public function testExitDecreasesStock(): void
    {
        $material = $this->material(10);
        (new StockManager())->apply($this->movement($material, InventoryMovements::TYPE_OUT, 4));

        self::assertSame(6, $material->getQuantity());
    }

    public function testExitBeyondStockIsRefused(): void
    {
        $material = $this->material(3);

        $this->expectException(\DomainException::class);
        (new StockManager())->apply($this->movement($material, InventoryMovements::TYPE_OUT, 5));
    }

    public function testTransferMovesLocationWithoutTouchingStock(): void
    {
        $material = $this->material(7);
        $movement = $this->movement($material, InventoryMovements::TYPE_TRANSFER, 7)
            ->setToLocation('Bureau B204');

        (new StockManager())->apply($movement);

        self::assertSame(7, $material->getQuantity());
        self::assertSame('Bureau B204', $material->getLocation());
        self::assertSame('Stock A', $movement->getFromLocation());
    }
}
