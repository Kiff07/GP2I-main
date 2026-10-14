<?php

namespace App\Entity;

use App\Repository\PurchaseOrdersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PurchaseOrdersRepository::class)]
#[ORM\Table(name: 'purchase_orders')]
#[ORM\UniqueConstraint(name: 'order_number', columns: ['order_number'])]
#[ORM\Index(name: 'idx_purchase_orders_supplier_id', columns: ['supplier_id'])]
#[ORM\Index(name: 'idx_purchase_orders_ordered_by', columns: ['ordered_by'])]
#[ORM\Index(name: 'idx_purchase_orders_status', columns: ['status'])]
#[UniqueEntity(fields: ['orderNumber'], message: 'Ce numero de commande existe deja.')]
class PurchaseOrders
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ORDERED = 'ordered';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'purchaseOrders')]
    #[ORM\JoinColumn(name: 'supplier_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Suppliers $supplier = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $order_number = null;

    #[ORM\ManyToOne(inversedBy: 'purchaseOrders')]
    #[ORM\JoinColumn(name: 'ordered_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Users $orderedBy = null;

    #[ORM\Column(length: 20, options: ['default' => self::STATUS_PENDING])]
    #[Assert\Choice(choices: [self::STATUS_PENDING, self::STATUS_ORDERED, self::STATUS_RECEIVED, self::STATUS_CANCELLED])]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTime $order_date = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $expected_date = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $total_amount = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /** @var Collection<int, PurchaseItems> */
    #[ORM\OneToMany(targetEntity: PurchaseItems::class, mappedBy: 'purchaseOrder', cascade: ['persist'], orphanRemoval: true)]
    private Collection $items;

    /** @var Collection<int, Deliveries> */
    #[ORM\OneToMany(targetEntity: Deliveries::class, mappedBy: 'purchaseOrder')]
    private Collection $deliveries;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->order_date = new \DateTime();
        $this->items = new ArrayCollection();
        $this->deliveries = new ArrayCollection();
    }

    public function __toString(): string { return $this->order_number ?? 'Commande'; }

    /**
     * Recalcule le montant total a partir des lignes de commande.
     * AVANT : le total etait tape a la main et pouvait ne correspondre
     * a aucune ligne. Appele apres chaque creation/modification de ligne.
     */
    public function recalculateTotal(): static
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            $total += (int) $item->getQuantity() * (float) ($item->getUnitPrice() ?? 0);
        }
        $this->total_amount = number_format($total, 2, '.', '');

        return $this;
    }
    public static function statusChoices(): array { return ['En attente' => self::STATUS_PENDING, 'Commandee' => self::STATUS_ORDERED, 'Recue' => self::STATUS_RECEIVED, 'Annulee' => self::STATUS_CANCELLED]; }
    public function getStatusLabel(): string { return array_flip(self::statusChoices())[$this->status] ?? (string) $this->status; }
    public function getId(): ?int { return $this->id; }
    public function getSupplier(): ?Suppliers { return $this->supplier; }
    public function setSupplier(?Suppliers $supplier): static { $this->supplier = $supplier; return $this; }
    public function getOrderNumber(): ?string { return $this->order_number; }
    public function setOrderNumber(string $order_number): static { $this->order_number = $order_number; return $this; }
    public function getOrderedBy(): ?Users { return $this->orderedBy; }
    public function setOrderedBy(?Users $orderedBy): static { $this->orderedBy = $orderedBy; return $this; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getOrderDate(): ?\DateTime { return $this->order_date; }
    public function setOrderDate(\DateTime $order_date): static { $this->order_date = $order_date; return $this; }
    public function getExpectedDate(): ?\DateTime { return $this->expected_date; }
    public function setExpectedDate(?\DateTime $expected_date): static { $this->expected_date = $expected_date; return $this; }
    public function getTotalAmount(): ?string { return $this->total_amount; }
    public function setTotalAmount(?string $total_amount): static { $this->total_amount = $total_amount; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->created_at; }
    public function setCreatedAt(\DateTimeImmutable $created_at): static { $this->created_at = $created_at; return $this; }
    /** @return Collection<int, PurchaseItems> */
    public function getItems(): Collection { return $this->items; }
    public function addItem(PurchaseItems $item): static { if (!$this->items->contains($item)) { $this->items->add($item); $item->setPurchaseOrder($this); } return $this; }
    public function removeItem(PurchaseItems $item): static { if ($this->items->removeElement($item) && $item->getPurchaseOrder() === $this) { $item->setPurchaseOrder(null); } return $this; }
    /** @return Collection<int, Deliveries> */
    public function getDeliveries(): Collection { return $this->deliveries; }
}
