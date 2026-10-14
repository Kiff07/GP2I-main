<?php

namespace App\Entity;

use App\Repository\PurchaseItemsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PurchaseItemsRepository::class)]
#[ORM\Table(name: 'purchase_items')]
#[ORM\Index(name: 'idx_purchase_items_purchase_order_id', columns: ['purchase_order_id'])]
class PurchaseItems
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'purchase_order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?PurchaseOrders $purchaseOrder = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $material_name = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $material_reference = null;

    #[ORM\Column]
    #[Assert\Positive]
    private ?int $quantity = 1;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $unit_price = null;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private ?int $received_quantity = 0;

    public function __toString(): string { return $this->material_name ?? 'Ligne de commande'; }
    public function getId(): ?int { return $this->id; }
    public function getPurchaseOrder(): ?PurchaseOrders { return $this->purchaseOrder; }
    public function setPurchaseOrder(?PurchaseOrders $purchaseOrder): static { $this->purchaseOrder = $purchaseOrder; return $this; }
    public function getMaterialName(): ?string { return $this->material_name; }
    public function setMaterialName(string $material_name): static { $this->material_name = $material_name; return $this; }
    public function getMaterialReference(): ?string { return $this->material_reference; }
    public function setMaterialReference(?string $material_reference): static { $this->material_reference = $material_reference; return $this; }
    public function getQuantity(): ?int { return $this->quantity; }
    public function setQuantity(int $quantity): static { $this->quantity = $quantity; return $this; }
    public function getUnitPrice(): ?string { return $this->unit_price; }
    public function setUnitPrice(?string $unit_price): static { $this->unit_price = $unit_price; return $this; }
    public function getReceivedQuantity(): ?int { return $this->received_quantity; }
    public function setReceivedQuantity(int $received_quantity): static { $this->received_quantity = $received_quantity; return $this; }
}
