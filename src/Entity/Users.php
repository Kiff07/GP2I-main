<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'username', columns: ['username'])]
#[ORM\UniqueConstraint(name: 'email', columns: ['email'])]
#[UniqueEntity(fields: ['username'], message: 'Ce nom d utilisateur existe deja.')]
#[UniqueEntity(fields: ['email'], message: 'Cet email existe deja.')]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    private ?string $username = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $full_name = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $phone = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updated_at = null;

    /** @var Collection<int, PurchaseOrders> */
    #[ORM\OneToMany(targetEntity: PurchaseOrders::class, mappedBy: 'orderedBy')]
    private Collection $purchaseOrders;

    /** @var Collection<int, Deliveries> */
    #[ORM\OneToMany(targetEntity: Deliveries::class, mappedBy: 'receivedBy')]
    private Collection $deliveries;

    /** @var Collection<int, InventoryMovements> */
    #[ORM\OneToMany(targetEntity: InventoryMovements::class, mappedBy: 'performedBy')]
    private Collection $inventoryMovements;

    /** @var Collection<int, Archives> */
    #[ORM\OneToMany(targetEntity: Archives::class, mappedBy: 'createdBy')]
    private Collection $archives;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->purchaseOrders = new ArrayCollection();
        $this->deliveries = new ArrayCollection();
        $this->inventoryMovements = new ArrayCollection();
        $this->archives = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->full_name ?: ($this->username ?? 'Utilisateur');
    }

    public function getId(): ?int { return $this->id; }
    public function getUsername(): ?string { return $this->username; }
    public function setUsername(string $username): static { $this->username = $username; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }
    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }
    public function getRoles(): array { return array_values(array_unique([...$this->roles, 'ROLE_USER'])); }
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }
    public function eraseCredentials(): void {}
    public function getUserIdentifier(): string { return (string) $this->email; }
    public function getFullName(): ?string { return $this->full_name; }
    public function setFullName(?string $full_name): static { $this->full_name = $full_name; return $this; }
    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): static { $this->phone = $phone; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->created_at; }
    public function setCreatedAt(\DateTimeImmutable $created_at): static { $this->created_at = $created_at; return $this; }
    public function getUpdatedAt(): ?\DateTime { return $this->updated_at; }
    public function setUpdatedAt(?\DateTime $updated_at): static { $this->updated_at = $updated_at; return $this; }
}
