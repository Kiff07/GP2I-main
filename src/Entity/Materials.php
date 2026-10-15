<?php

namespace App\Entity;

use App\Repository\MaterialsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MaterialsRepository::class)]
#[ORM\Table(name: 'materials')]
#[ORM\UniqueConstraint(name: 'reference', columns: ['reference'])]
#[ORM\Index(name: 'idx_materials_category_id', columns: ['category_id'])]
#[ORM\Index(name: 'idx_materials_status', columns: ['status'])]
#[ORM\Index(name: 'idx_materials_location', columns: ['location'])]
#[UniqueEntity(fields: ['reference'], message: 'Cette reference existe deja.')]
class Materials
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_BORROWED = 'borrowed';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_BROKEN = 'broken';
    public const STATUS_DISPOSED = 'disposed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $reference = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Assert\Length(max: 200)]
    private ?string $serial_number = null;

    #[ORM\ManyToOne(inversedBy: 'materials')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Categories $category = null;

    #[ORM\ManyToOne(inversedBy: 'materials')]
    #[ORM\JoinColumn(name: 'supplier_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Suppliers $supplier = null;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?int $quantity = 0;

    #[ORM\Column(length: 20, options: ['default' => self::STATUS_AVAILABLE])]
    #[Assert\Choice(choices: [
        self::STATUS_AVAILABLE,
        self::STATUS_BORROWED,
        self::STATUS_MAINTENANCE,
        self::STATUS_BROKEN,
        self::STATUS_DISPOSED,
    ])]
    private ?string $status = self::STATUS_AVAILABLE;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $location = null;

    // Seuil en dessous duquel le materiel apparait en "stock faible".
    #[ORM\Column(options: ['default' => 2])]
    #[Assert\PositiveOrZero]
    private ?int $alert_threshold = 2;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $purchase_date = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $purchase_price = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updated_at = null;

    /**
     * @var Collection<int, InventoryMovements>
     */
    #[ORM\OneToMany(targetEntity: InventoryMovements::class, mappedBy: 'material', orphanRemoval: true)]
    private Collection $inventoryMovements;

    /**
     * @var Collection<int, AssetImages>
     */
    #[ORM\OneToMany(targetEntity: AssetImages::class, mappedBy: 'material', orphanRemoval: true)]
    private Collection $assetImages;

    /**
     * @var Collection<int, Loans>
     */
    #[ORM\OneToMany(targetEntity: Loans::class, mappedBy: 'material', orphanRemoval: true)]
    private Collection $loans;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->inventoryMovements = new ArrayCollection();
        $this->assetImages = new ArrayCollection();
        $this->loans = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?? 'Matériel';
    }

    public static function statusChoices(): array
    {
        return [
            'Disponible' => self::STATUS_AVAILABLE,
            'Emprunté' => self::STATUS_BORROWED,
            'Maintenance' => self::STATUS_MAINTENANCE,
            'Cassé' => self::STATUS_BROKEN,
            'Sorti du parc' => self::STATUS_DISPOSED,
        ];
    }

    public function getStatusLabel(): string
    {
        return array_flip(self::statusChoices())[$this->status] ?? (string) $this->status;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serial_number;
    }

    public function setSerialNumber(?string $serial_number): static
    {
        $this->serial_number = $serial_number;

        return $this;
    }

    public function getCategory(): ?Categories
    {
        return $this->category;
    }

    public function setCategory(?Categories $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getSupplier(): ?Suppliers
    {
        return $this->supplier;
    }

    public function setSupplier(?Suppliers $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getPurchaseDate(): ?\DateTime
    {
        return $this->purchase_date;
    }

    public function setPurchaseDate(?\DateTime $purchase_date): static
    {
        $this->purchase_date = $purchase_date;

        return $this;
    }

    public function getPurchasePrice(): ?string
    {
        return $this->purchase_price;
    }

    public function setPurchasePrice(?string $purchase_price): static
    {
        $this->purchase_price = $purchase_price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTime $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, InventoryMovements>
     */
    public function getInventoryMovements(): Collection
    {
        return $this->inventoryMovements;
    }

    /**
     * @return Collection<int, AssetImages>
     */
    public function getAssetImages(): Collection
    {
        return $this->assetImages;
    }

    /**
     * @return Collection<int, Loans>
     */
    public function getLoans(): Collection
    {
        return $this->loans;
    }

    public function getAlertThreshold(): ?int
    {
        return $this->alert_threshold;
    }

    public function setAlertThreshold(int $alert_threshold): static
    {
        $this->alert_threshold = $alert_threshold;

        return $this;
    }

    // Le stock est-il faible pour CE materiel (selon son propre seuil) ?
    public function isLowStock(): bool
    {
        return (int) $this->quantity <= (int) $this->alert_threshold
            && self::STATUS_DISPOSED !== $this->status;
    }
}
