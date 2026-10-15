<?php

namespace App\Entity;

use App\Repository\LoansRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Un emprunt de matériel : qui a pris quoi, pour combien de temps.
 * Le statut est calculé (en cours / en retard / retourné), pas stocké.
 */
#[ORM\Entity(repositoryClass: LoansRepository::class)]
#[ORM\Table(name: 'loans')]
#[ORM\Index(name: 'idx_loans_material_id', columns: ['material_id'])]
#[ORM\Index(name: 'idx_loans_returned_at', columns: ['returned_at'])]
class Loans
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_RETURNED = 'returned';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'loans')]
    #[ORM\JoinColumn(name: 'material_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Materials $material = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $borrower_name = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTime $borrowed_at = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTime $due_date = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $returned_at = null;

    #[ORM\ManyToOne(inversedBy: 'loans')]
    #[ORM\JoinColumn(name: 'performed_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Users $performedBy = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    public function __construct()
    {
        $this->borrowed_at = new \DateTime();
        $this->due_date = new \DateTime('+7 days');
    }

    public function __toString(): string
    {
        return 'Emprunt '.($this->material?->getName() ?? '').' - '.($this->borrower_name ?? '');
    }

    public function isReturned(): bool
    {
        return null !== $this->returned_at;
    }

    public function isOverdue(): bool
    {
        return !$this->isReturned()
            && null !== $this->due_date
            && $this->due_date < new \DateTime('today');
    }

    public function getStatusSlug(): string
    {
        if ($this->isReturned()) {
            return self::STATUS_RETURNED;
        }

        return $this->isOverdue() ? self::STATUS_OVERDUE : self::STATUS_ACTIVE;
    }

    public function getStatusLabel(): string
    {
        return match ($this->getStatusSlug()) {
            self::STATUS_RETURNED => 'Retourné',
            self::STATUS_OVERDUE => 'En retard',
            default => 'En cours',
        };
    }

    public function getId(): ?int { return $this->id; }
    public function getMaterial(): ?Materials { return $this->material; }
    public function setMaterial(?Materials $material): static { $this->material = $material; return $this; }
    public function getBorrowerName(): ?string { return $this->borrower_name; }
    public function setBorrowerName(string $borrower_name): static { $this->borrower_name = $borrower_name; return $this; }
    public function getBorrowedAt(): ?\DateTime { return $this->borrowed_at; }
    public function setBorrowedAt(\DateTime $borrowed_at): static { $this->borrowed_at = $borrowed_at; return $this; }
    public function getDueDate(): ?\DateTime { return $this->due_date; }
    public function setDueDate(\DateTime $due_date): static { $this->due_date = $due_date; return $this; }
    public function getReturnedAt(): ?\DateTime { return $this->returned_at; }
    public function setReturnedAt(?\DateTime $returned_at): static { $this->returned_at = $returned_at; return $this; }
    public function getPerformedBy(): ?Users { return $this->performedBy; }
    public function setPerformedBy(?Users $performedBy): static { $this->performedBy = $performedBy; return $this; }
    public function getNote(): ?string { return $this->note; }
    public function setNote(?string $note): static { $this->note = $note; return $this; }
}
