<?php

namespace App\Entity;

use App\Repository\DeliveriesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DeliveriesRepository::class)]
#[ORM\Table(name: 'deliveries')]
#[ORM\Index(name: 'idx_deliveries_purchase_order_id', columns: ['purchase_order_id'])]
#[ORM\Index(name: 'idx_deliveries_received_by', columns: ['received_by'])]
class Deliveries
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(name: 'purchase_order_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?PurchaseOrders $purchaseOrder = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTime $delivery_date = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(name: 'received_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Users $receivedBy = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function __construct() { $this->delivery_date = new \DateTime(); $this->created_at = new \DateTimeImmutable(); }
    public function __toString(): string { return 'Livraison #'.($this->id ?? 'nouvelle'); }
    public function getId(): ?int { return $this->id; }
    public function getPurchaseOrder(): ?PurchaseOrders { return $this->purchaseOrder; }
    public function setPurchaseOrder(?PurchaseOrders $purchaseOrder): static { $this->purchaseOrder = $purchaseOrder; return $this; }
    public function getDeliveryDate(): ?\DateTime { return $this->delivery_date; }
    public function setDeliveryDate(\DateTime $delivery_date): static { $this->delivery_date = $delivery_date; return $this; }
    public function getReceivedBy(): ?Users { return $this->receivedBy; }
    public function setReceivedBy(?Users $receivedBy): static { $this->receivedBy = $receivedBy; return $this; }
    public function getNote(): ?string { return $this->note; }
    public function setNote(?string $note): static { $this->note = $note; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->created_at; }
    public function setCreatedAt(\DateTimeImmutable $created_at): static { $this->created_at = $created_at; return $this; }
}
