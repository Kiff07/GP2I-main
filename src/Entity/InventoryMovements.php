<?php

namespace App\Entity;

use App\Repository\InventoryMovementsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InventoryMovementsRepository::class)]
#[ORM\Table(name: 'inventory_movements')]
#[ORM\Index(name: 'idx_inventory_movements_material_id', columns: ['material_id'])]
#[ORM\Index(name: 'idx_inventory_movements_performed_by', columns: ['performed_by'])]
#[ORM\Index(name: 'idx_inventory_movements_type', columns: ['movement_type'])]
class InventoryMovements
{
    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_MAINTENANCE = 'maintenance';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inventoryMovements')]
    #[ORM\JoinColumn(name: 'material_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Materials $material = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::TYPE_IN, self::TYPE_OUT, self::TYPE_TRANSFER, self::TYPE_MAINTENANCE])]
    private ?string $movement_type = self::TYPE_IN;

    #[ORM\Column]
    #[Assert\Positive]
    private ?int $quantity = 1;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $from_location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $to_location = null;

    #[ORM\ManyToOne(inversedBy: 'inventoryMovements')]
    #[ORM\JoinColumn(name: 'performed_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Users $performedBy = null;

    #[ORM\Column]
    private ?\DateTime $performed_at = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    public function __construct() { $this->performed_at = new \DateTime(); }
    public function __toString(): string { return $this->movement_type.' - '.($this->material?->getName() ?? 'Materiel'); }
    public static function typeChoices(): array { return ['Entree' => self::TYPE_IN, 'Sortie' => self::TYPE_OUT, 'Transfert' => self::TYPE_TRANSFER, 'Maintenance' => self::TYPE_MAINTENANCE]; }
    public function getTypeLabel(): string { return array_flip(self::typeChoices())[$this->movement_type] ?? (string) $this->movement_type; }
    public function getId(): ?int { return $this->id; }
    public function getMaterial(): ?Materials { return $this->material; }
    public function setMaterial(?Materials $material): static { $this->material = $material; return $this; }
    public function getMovementType(): ?string { return $this->movement_type; }
    public function setMovementType(string $movement_type): static { $this->movement_type = $movement_type; return $this; }
    public function getQuantity(): ?int { return $this->quantity; }
    public function setQuantity(int $quantity): static { $this->quantity = $quantity; return $this; }
    public function getFromLocation(): ?string { return $this->from_location; }
    public function setFromLocation(?string $from_location): static { $this->from_location = $from_location; return $this; }
    public function getToLocation(): ?string { return $this->to_location; }
    public function setToLocation(?string $to_location): static { $this->to_location = $to_location; return $this; }
    public function getPerformedBy(): ?Users { return $this->performedBy; }
    public function setPerformedBy(?Users $performedBy): static { $this->performedBy = $performedBy; return $this; }
    public function getPerformedAt(): ?\DateTime { return $this->performed_at; }
    public function setPerformedAt(\DateTime $performed_at): static { $this->performed_at = $performed_at; return $this; }
    public function getReason(): ?string { return $this->reason; }
    public function setReason(?string $reason): static { $this->reason = $reason; return $this; }
}
